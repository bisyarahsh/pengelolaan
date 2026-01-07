<?php
include '../php/koneksi.php';
// Memulai sesi
session_start();

// Set Zona Waktu ke WIB
date_default_timezone_set('Asia/Jakarta'); 
// End Set Zona Waktu

// Cek Sesi
if ($_SESSION['status'] != "login") {
    header("location:../login/login.php");
    exit;
}
if ($_SESSION['role'] != "Admin") {
    header("location:../login/login.php");
    exit;
}

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

// Fungsi Format Bulan Indonesia dan Mengambil Data Rapat
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

// DATA USER, FOTO & INISIAL
$id_user_login = $_SESSION['id_user'];

$sql_user_info = "SELECT nama_lengkap, profile_pic FROM users WHERE id_user = '$id_user_login'"; 
$q_user_info = mysqli_query($koneksi, $sql_user_info);
$d_user_info = mysqli_fetch_assoc($q_user_info);

$nama_user = $d_user_info['nama_lengkap'] ?? "Admin";
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
    $initials = "AD";
}

// Menampilkan Riwayat
$list_riwayat = [];

$tgl_awal  = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : null;
$tgl_akhir = isset($_POST['tgl_akhir']) ? $_POST['tgl_akhir'] : null;

$sql_read = "SELECT 
                r.*, 
                o.nama_unit,
                u.nama_lengkap as nama_pembuat
             FROM agenda_rapat r
             JOIN unit o ON r.id_unit = o.id_unit
             LEFT JOIN users u ON r.id_pembuat = u.id_user 
             WHERE r.status = 'aktif'";

$sql_read .= " AND CONCAT(r.tanggal_rapat, ' ', r.jam_rapat) <= NOW()";

// Filter Tanggal
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql_read .= " AND r.tanggal_rapat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$sql_read .= " ORDER BY r.tanggal_rapat DESC, r.jam_rapat DESC";

$q_read = mysqli_query($koneksi, $sql_read);
while ($r_read = mysqli_fetch_assoc($q_read)) {
    $list_riwayat[] = $r_read;
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
    <title>Riwayat | Admin - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
    .form-control.flatpickr-input[readonly] {
        background-color: #fff; 
    }
    </style>
</head>
<body>
    <div id="loader-wrapper">
        <div class="loader-spinner"></div>
    </div>
    <!-- Sidebar -->
    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php" class="active"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="unit.php"><i class="fa-solid fa-users icon"></i> Unit</a></li>
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

                <div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
                    <h2 class="text-primary fw-bold m-0 fs-3">Riwayat Rapat</h2>
                </div>
                <!-- Tabel -->
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
                        <?php $no = 1; foreach ($list_riwayat as $rapat) : ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars(tgl_indo($rapat['tanggal_rapat'])); ?></td>
                            <td><?php echo htmlspecialchars(date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB'); ?></td>
                            <td><?php echo htmlspecialchars($rapat['nama_unit']); ?></td>
                            <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning aksi view-rapat-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-eye"></i></button>
                                <button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-trash"></i></i></button>
                                <button type="button" class="btn btn-success aksi print-rapat-detail-btn" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-arrow-down"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- End Tabel -->
                
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
    
$(document).ready(function() {
    $('#tabel-rapat').DataTable({
        responsive: false, 
        scrollX: true,
        scrollCollapse: true,

        columnDefs: [
            { className: "text-center", targets: [0, 5] }, 
            { className: "align-middle", targets: "_all" }, 
            
            { width: "50px", targets: 0 },   
            { width: "150px", targets: 1 },  
            { width: "100px", targets: 2 },  
            { width: "200px", targets: 3 },  
            { width: "200px", targets: 4 },  
            { width: "150px", targets: 5 }   
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
    });

    flatpickr(".input-tanggal", {
        locale: "id",
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        allowInput: true
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

// Buttin Download Detail
$(document).on('click', '.print-rapat-detail-btn', function (e) {
    e.preventDefault();
    var id_rapat = $(this).data('id');
    
    Swal.fire({
        title: "Memproses Unduh",
        text: "Mohon tunggu sebentar, berkas PDF sedang disiapkan...",
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