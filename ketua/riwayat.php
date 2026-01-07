<?php
include '../php/koneksi.php';
// Memulai sesi
session_start();
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

// --- Set Zona Waktu ke WIB ---
date_default_timezone_set('Asia/Jakarta'); 
// --- End Set Zona Waktu ---

// Cek Sesi
if ($_SESSION['status'] != "login") {
    header("location:../login/login.php");
    exit;
}
if ($_SESSION['role'] != "Ketua") {
    header("location:../login/login.php");
    exit;
}

// Ambil User ID & Unit ID
$id_unit_ketua = $_SESSION['unit_id'] ?? null; 
$current_user_id = $_SESSION['id_user'];

// DATA USER, FOTO & INISIAL
$id_user_login = $_SESSION['id_user'];

$sql_user_info = "SELECT nama_lengkap, profile_pic FROM users WHERE id_user = '$id_user_login'"; 
$q_user_info = mysqli_query($koneksi, $sql_user_info);
$d_user_info = mysqli_fetch_assoc($q_user_info);

$nama_user = $d_user_info['nama_lengkap'] ?? "Ketua Prodi";
$foto_db   = $d_user_info['profile_pic'] ?? null;

$path_foto_target = "../assets/img/profile/" . $foto_db;
$tampilkan_foto = false;

if (!empty($foto_db) && file_exists($path_foto_target)) {
    $tampilkan_foto = true;
}

// Membuat Profil Inisial
$words = explode(" ", $nama_user);
$initials = "";
if (count($words) >= 1) {
    $initials .= strtoupper(substr($words[0], 0, 1));
    if (count($words) > 1) {
        $initials .= strtoupper(substr(end($words), 0, 1));
    }
} else {
    $initials = "KP";
}

if (empty($id_unit_ketua)) {
    $q_unit = mysqli_query($koneksi, "SELECT unit_id FROM users WHERE id_user = '{$_SESSION['id_user']}'");
    $r_unit = mysqli_fetch_assoc($q_unit);
    $id_unit_ketua = $r_unit['unit_id'] ?? null;
    if ($id_unit_ketua) {
        $_SESSION['unit_id'] = $id_unit_ketua;
    }
}

// Fungsi Bulan Format Indonesia dan Mengambil Data Rapat
function tgl_indo($tanggal){
    $bulan = array (
        1 =>   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

function get_rapat_detail($koneksi, $id_rapat) {
    $id_rapat = mysqli_real_escape_string($koneksi, $id_rapat);
    $sql = "SELECT 
                r.*, 
                o.nama_unit 
            FROM agenda_rapat r
            JOIN unit o ON r.id_unit = o.id_unit
            WHERE r.id_rapat = '$id_rapat'";
    $q = mysqli_query($koneksi, $sql);
    $rapat_data = mysqli_fetch_assoc($q);

    if ($rapat_data) {
        $peserta_arr = [];
        $sql_peserta = "SELECT id_user FROM peserta_rapat WHERE id_rapat = '$id_rapat'";
        $q_peserta = mysqli_query($koneksi, $sql_peserta);
        while ($r_peserta = mysqli_fetch_assoc($q_peserta)) {
            $peserta_arr[] = $r_peserta['id_user'];
        }
        $rapat_data['peserta_id'] = $peserta_arr;
    }
    return $rapat_data;
}

// Menampilkan Riwayat Unit dan Undangan
$list_riwayat_unit = [];
$list_riwayat_undangan = [];

$tgl_awal  = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : null;
$tgl_akhir = isset($_POST['tgl_akhir']) ? $_POST['tgl_akhir'] : null;

$base_sql = "SELECT DISTINCT
                r.*, 
                o.nama_unit,
                u.nama_lengkap as nama_pembuat
             FROM agenda_rapat r
             JOIN unit o ON r.id_unit = o.id_unit
             LEFT JOIN users u ON r.id_pembuat = u.id_user 
             WHERE 1=1 "; 

// Filter Tanggal
$base_sql .= " AND CONCAT(r.tanggal_rapat, ' ', r.jam_rapat) <= NOW()";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $base_sql .= " AND r.tanggal_rapat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

// Riwayat Unit
$sql_unit = $base_sql . " AND (r.id_unit = '$id_unit_ketua' OR r.id_pembuat = '$current_user_id')
                          AND (
                              r.id_pembuat = '$current_user_id' 
                              OR 
                              r.id_rapat NOT IN (SELECT id_rapat FROM peserta_rapat WHERE id_user = '$current_user_id')
                          )
                          ORDER BY r.tanggal_rapat DESC, r.jam_rapat DESC";

$q_unit = mysqli_query($koneksi, $sql_unit);
while ($row = mysqli_fetch_assoc($q_unit)) {
    $list_riwayat_unit[] = $row;
}

// Riwayat Undangan
$sql_invite = $base_sql . " AND r.id_rapat IN (SELECT id_rapat FROM peserta_rapat WHERE id_user = '$current_user_id')
                            AND r.id_pembuat != '$current_user_id'
                            ORDER BY r.tanggal_rapat DESC, r.jam_rapat DESC";

$q_invite = mysqli_query($koneksi, $sql_invite);
while ($row = mysqli_fetch_assoc($q_invite)) {
    $list_riwayat_undangan[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="../assets/admin.css">
    <title>Riwayat | Ketua - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
    .form-control.flatpickr-input[readonly] {
        background-color: #fff; 
    }
    </style>
</head>
<body>
    
    <!-- Sidebar -->
    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php" class="active"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Anggota</a></li>
            <li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Pengaturan</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
        </ul>
    </section>
    <!-- End Sidebar -->

    <section id="content">
        <!-- Navbar -->
		<nav class="atas mb-4 shadow">
            <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar'></i>

            <div class="d-flex align-items-center" data-aos="fade-left">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle hide-arrow" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-lg-inline text-gray-600 small fw-bold">
                            <?php echo $nama_user; ?>
                        </span>

                        <?php if ($tampilkan_foto): ?>
                            <img src="../assets/img/profile/<?= $foto_db; ?>" 
                                 alt="Profile" 
                                 class="rounded-circle object-fit-cover shadow-sm" 
                                 style="width: 40px; height: 40px;">
                        <?php else: ?>
                            <div class="img-profile-initials">
                                <?php echo $initials; ?>
                            </div>
                        <?php endif; ?>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="profileDropdown">
                        <li>
                            <a class="dropdown-item" href="pengaturan.php">
                                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400 me-2"></i>
                                Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="logout.php" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400 me-2"></i>
                                Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
		<!-- End Navbar -->
        
        <!-- Main -->
        <main>
            <div data-aos="fade-down" class="rapat bg-light">
                <!-- Filter Tanggal -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <h5 class="text-primary">Saring berdasarkan tanggal</h5>
                        <form action="" method="POST">
                            <div class="row align-items-end g-2">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-calendar-days me-1"></i> Dari Tanggal</label>
                                    <input type="text" name="tgl_awal" class="form-control form-control-sm input-tanggal" value="<?php echo $tgl_awal; ?>" placeholder="Pilih Tanggal..." autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-calendar-check me-1"></i> Sampai Tanggal</label>
                                    <input type="text" name="tgl_akhir" class="form-control form-control-sm input-tanggal" value="<?php echo $tgl_akhir; ?>" placeholder="Pilih Tanggal..." autocomplete="off">
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                        <i class="fa-solid fa-filter me-1"></i> Terapkan
                                    </button>
                                    <?php if(!empty($tgl_awal)): ?>
                                        <a href="riwayat.php" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                            <i class="fa-solid fa-arrows-rotate"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End Filter Tanggal -->
                
                <!-- Tab Switch -->
                <div class="d-flex justify-content-center justify-content-lg-start mb-4">
                    <ul class="nav nav-pills nav-pills-custom" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-unit-tab" data-bs-toggle="pill" data-bs-target="#pills-unit" type="button" role="tab" aria-controls="pills-unit" aria-selected="true">
                                <i class="fa-solid fa-calendar-days me-2"></i>Riwayat Unit
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-invite-tab" data-bs-toggle="pill" data-bs-target="#pills-invite" type="button" role="tab" aria-controls="pills-invite" aria-selected="false">
                                <i class="fa-solid fa-envelope-open-text me-2"></i>Riwayat Undangan
                            </button>
                        </li>
                    </ul>
                </div>
                <!-- End Tab Switch -->
                
				<!-- Tabel Riwayat Unit dan Undangan -->
                <div class="tab-content" id="pills-tabContent">
					<!-- Tabel Unit -->
                    <div class="tab-pane fade show active" id="pills-unit" role="tabpanel" aria-labelledby="pills-unit-tab">
						<div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
						    <h2 class="text-primary fw-bold m-0 fs-3">Riwayat Unit</h2>
						</div>
                        <table id="tabel-rapat" class="table table-striped table-hover nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal Rapat</th>
                                    <th>Jam Rapat</th>
                                    <th>Unit</th>
                                    <th>Judul Rapat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($list_riwayat_unit as $rapat) : ?>
                                <?php
					                $is_creator = ($rapat['id_pembuat'] == $_SESSION['id_user']);
					            ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars(tgl_indo($rapat['tanggal_rapat'])); ?></td>
                                    <td><?php echo htmlspecialchars(date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB'); ?></td>
                                    <td><?php echo htmlspecialchars($rapat['nama_unit']); ?></td>
                                    <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-warning aksi view-rapat-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-eye"></i></button>
                                        <button type="button"<?php
					                            $peserta_arr_ids = [];
					                            $q_p = mysqli_query($koneksi, "SELECT id_user FROM peserta_rapat WHERE id_rapat = '{$rapat['id_rapat']}'");
					                            while ($r_p = mysqli_fetch_assoc($q_p)) {
					                                $peserta_arr_ids[] = $r_p['id_user'];
					                            }
					                            $peserta_json = json_encode($peserta_arr_ids); 
					                        ?> class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-trash"></i></i></button>
                                        <button type="button" class="btn btn-success aksi print-rapat-detail-btn" title="Download Detail Rapat PDF" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-arrow-down"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
					<!-- End Tabel Unit -->

					<!-- Tabel Undangan -->
                    <div class="tab-pane fade" id="pills-invite" role="tabpanel" aria-labelledby="pills-invite-tab">
						<div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
						    <h2 class="text-primary fw-bold m-0 fs-3">Rapat Undangan</h2>
						</div>
                        <table id="tabel-undangan" class="table table-striped table-hover nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Tanggal Rapat</th>
                                    <th>Jam Rapat</th>
                                    <th>Unit</th>
                                    <th>Judul Rapat</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($list_riwayat_undangan as $rapat) : ?>
                                <tr>
                                    <td class="text-center"><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars(tgl_indo($rapat['tanggal_rapat'])); ?></td>
                                    <td><?php echo htmlspecialchars(date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB'); ?></td>
                                    <td><?php echo htmlspecialchars($rapat['nama_unit']); ?></span></td>
                                    <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
                                     <td class="text-center status-cell">
                    				    <button type="button" class="btn btn-warning aksi view-rapat-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-eye"></i></button>
                                        <button type="button" class="btn btn-success aksi print-rapat-detail-btn" title="Download Detail Rapat PDF" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-arrow-down"></i></button>
                    				</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
					<!-- End Tabel Undangan -->
                </div>
				<!-- End Tabel Riwayat Unit dan Undangan -->
            
                <!-- Modal Detail Riwayat Rapat -->
                <div class="modal fade modal-compact" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered"> <div class="modal-content">
                            <div class="modal-header header-primary text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                                <h5 class="modal-title" id="viewModalLabel"><i class="fa-solid fa-eye me-2"></i>Detail Agenda</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div class="row g-4">

                                    <div class="col-md-7 border-end">

                                        <div class="d-flex align-items-center mb-4">
                                            <div class="view-date-box me-3" style="min-width: 80px;">
                                                <div class="day" id="view_tanggal_day">--</div> 
                                                <div class="month-year" id="view_tanggal_month">--</div>
                                            </div>
                                            <div>
                                                <div class="detail-label">Waktu Pelaksanaan</div>
                                                <div class="h5 fw-bold text-dark mb-0"><i class="fa-regular fa-clock me-2 text-warning"></i><span id="view_jam"></span></div>
                                                <small class="text-muted" id="view_tanggal_full"></small> </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="detail-label">Judul Rapat</div>
                                            <div class="h5 fw-bold text-primary" id="view_judul"></div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <div class="detail-label">Ruangan</div>
                                                <div class="detail-value"><i class="fa-solid fa-location-dot me-1 text-danger"></i> <span id="view_ruangan"></span></div>
                                            </div>
                                            <div class="col-6">
                                                <div class="detail-label">Unit Penyelenggara</div>
                                                <div class="detail-value"><i class="fa-solid fa-users-gear me-1 text-info"></i> <span id="view_unit"></span></div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="detail-label">Keterangan</div>
                                            <div class="description-box" id="view_keterangan">
                                                </div>
                                        </div>

                                        <div>
                                            <div class="detail-label mb-2">Dokumen Notulen</div>
                                            <div id="view_notulen_container">
                                                <span id="view_notulen_file"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-users me-2"></i>Daftar Peserta</h6>
                                            <span class="badge bg-secondary rounded-pill" id="view_peserta_count">0 Orang</span>
                                        </div>

                                        <div class="participant-list-container" id="view_peserta_list_box">
                                            <div class="text-center text-muted mt-5">
                                                <i class="fa-solid fa-spinner fa-spin"></i> Memuat...
                                             </div>
                                        </div>
                                        <span id="view_peserta" style="display:none;"></span> 
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer bg-light py-2">
                                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Modal Detail Riwayat Rapat -->
                
                <!-- Modal Delete Riwayat Rapat -->
                <div class="modal fade" id="deletemodal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm"> 
                        <div class="modal-content text-center">
                            <div class="modal-body pt-5 pb-4">
                                <form method="POST" action="../php/delete_rapatv.1.php">
                                    <input type="hidden" name="hapus_id_rapat" id="hapus_id_rapat_modal"> 

                                    <div class="modal-icon-wrapper">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>

                                    <h4 class="fw-bold mb-2">Hapus Riwayat?</h4>
                                    <p class="text-muted mb-4 text-small">Tindakan ini tidak dapat dibatalkan. Data rapat akan hilang permanen.</p>

                                    <div class="d-grid gap-2">
                                        <button type="submit" name="hapus_rapat" class="btn btn-danger btn-lg shadow-sm">Ya, Hapus Sekarang</button> 
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Modal Delete Riwayat Rapat -->
            </div>
        </main>
        <!-- End Main -->
    </section>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.4/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.4/js/responsive.bootstrap5.js"></script>
<script src="../assets/admin.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
  AOS.init();
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/agenda_handlers.js"></script>
<script type="text/javascript">
    
// Setting Data Tabel
$(document).ready(function() {
   var tableOptions = {
        responsive: false, 
        scrollX: true,
        scrollCollapse: true,

        columnDefs: [
            { className: "text-center", targets: [0, 5] },
            { className: "align-middle", targets: "_all" },
            
            { width: "50px", targets: 0 },   // No
            { width: "150px", targets: 1 },  // Tanggal
            { width: "100px", targets: 2 },  // Jam
            { width: "200px", targets: 3 },  // Unit
            { width: "200px", targets: 4 },  // Judul Rapat
            { width: "150px", targets: 5 }   // Aksi
        ],
        "language": {
            "emptyTable": "Tidak ada riwayat rapat",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ riwayat",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 riwayat",
            "infoFiltered": "(difilter dari total _MAX_ riwayat)",
            "lengthMenu": "Tampilkan _MENU_ riwayat",
            "search": "Cari:",
            "zeroRecords": "Tidak ditemukan riwayat rapat yang cocok",
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    };

	var tableUnit = $('#tabel-rapat').DataTable(tableOptions);

    var tableInvite = $('#tabel-undangan').DataTable(tableOptions);
    
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        tableUnit.columns.adjust();
        tableInvite.columns.adjust();
    });

	flatpickr(".input-tanggal", {
        locale: "id",
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        allowInput: true,
        disableMobile: "true"
    });

    flatpickr(".input-jam", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        locale: "id",
        allowInput: true,
        disableMobile: "true"
    });
});

// Alert
<?php if (isset($alert) && $alert['type'] == 'success') : ?>
    Swal.fire({
        title: "Selamat!",
        text: "<?php echo $alert['message']; ?>",
        icon: "success"
    });
<?php elseif (isset($alert) && $alert['type'] == 'error') : ?>
    Swal.fire({
        title: "Oops!",
        text: "<?php echo $alert['message']; ?>",
        icon: "error"
    });
<?php endif; ?>

// Delete Modal
$(document).on('click', 'button[data-bs-target="#deletemodal"]', function (event) {
    var id_rapat = $(this).data('id'); 
    $('#hapus_id_rapat_modal').val(id_rapat); 
});

// Tombol Download Detail Rapat
$(document).on('click', '.print-rapat-detail-btn', function (e) {
    e.preventDefault();
    var id_rapat = $(this).data('id');
    
    Swal.fire({
        title: "Memproses Download",
        text: "Mohon tunggu sebentar, file PDF sedang disiapkan...",
        icon: "info",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
            window.location.href = '../php/download_rapat_pdf.php?id=' + id_rapat;
        }
    });

    setTimeout(() => {
        Swal.close();
    }, 5000); 
});
</script>
</body>
</html>