<?php
include '../php/koneksi.php';
session_start();

// Set Zona Waktu dan Ambil ID User
date_default_timezone_set('Asia/Jakarta'); 

if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']); 

// Cek Role
if (strtolower($_SESSION['role']) != "peserta") { 
    header("location:../login/login.php?error=noaccess");
    exit;
}

$now_datetime = date('Y-m-d H:i:s'); 

// Fungsi Bulan Format Indonesia
function tgl_indo($tanggal){
    $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

// DATA USER, FOTO & INISIAL
$id_user_login = $_SESSION['id_user'];

$sql_user_info = "SELECT nama_lengkap, profile_pic FROM users WHERE id_user = '$id_user_login'"; 
$q_user_info = mysqli_query($koneksi, $sql_user_info);
$d_user_info = mysqli_fetch_assoc($q_user_info);

$nama_user = $d_user_info['nama_lengkap'] ?? "Dosen/Labor";
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
    $initials = "DL";
}

// Filter dan Kueri
$tgl_awal  = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : null;
$tgl_akhir = isset($_POST['tgl_akhir']) ? $_POST['tgl_akhir'] : null;

$sql_agenda = "
    SELECT
        ar.tanggal_rapat,
        ar.jam_rapat,
        ar.judul_rapat,
        ar.keterangan,
        ar.ruang_rapat,
        ar.status, 
        o.nama_unit
    FROM
        agenda_rapat ar
    JOIN
        peserta_rapat pr ON ar.id_rapat = pr.id_rapat
    LEFT JOIN 
        unit o ON ar.id_unit = o.id_unit
    WHERE
        pr.id_user = '$id_user'";

$sql_agenda .= " AND (
                    (ar.status = 'dibatalkan' AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) >= '$now_datetime') 
                    OR 
                    (ar.status = 'aktif' AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) >= '$now_datetime')
                 )";

// Filter Tanggal
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql_agenda .= " AND ar.tanggal_rapat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$sql_agenda .= " ORDER BY ar.tanggal_rapat ASC, ar.jam_rapat ASC";

$query_agenda = mysqli_query($koneksi, $sql_agenda);

// Fungsi untuk melihat status rapat
function get_status_display($db_status, $tanggal, $jam) {
    if ($db_status == 'dibatalkan') {
        return ['text' => 'Dibatalkan', 'class' => 'bg-danger'];
    }

    $datetime_rapat = date('Y-m-d H:i:s', strtotime("$tanggal $jam"));
    $now = date('Y-m-d H:i:s');
    
    if ($datetime_rapat >= $now) {
        return ['text' => 'Menunggu', 'class' => 'bg-warning text-dark'];
    } 
    return ['text' => 'Selesai', 'class' => 'bg-success']; 
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.4/css/responsive.bootstrap5.min.css"/>
    <link rel="stylesheet" href="../assets/peserta.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <title>Agenda | Peserta - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
        .form-control.flatpickr-input[readonly] { 
            background-color: #fff; 
        }
        .row-cancelled { 
            background-color: #ffecec !important; 
        }
        .row-cancelled td { 
            color: #888; 
            text-decoration: line-through; 
        }
        .row-cancelled td.status-cell { 
            text-decoration: none; 
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
            <li><a href="agenda.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="history.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
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
                                        <a href="agenda.php" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                            <i class="fa-solid fa-arrows-rotate"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Filter Tanggal -->
                                
                <div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
                    <h2 class="text-primary fw-bold m-0 fs-3">Agenda Rapat</h2>
                </div>
                <!-- Tabel -->
                <table id="tabel-rapat" class="table table-hover nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tanggal Rapat</th>
                            <th>Jam Rapat</th>
                            <th>Unit</th>
                            <th>Judul Rapat</th>
                            <th>Ruangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_agenda) > 0) {
                            while ($rapat = mysqli_fetch_assoc($query_agenda)) {
                                // Cek status dari database, default 'aktif' jika null
                                $db_status = isset($rapat['status']) ? $rapat['status'] : 'aktif'; 
                                // Panggil fungsi status display baru
                                $status_display = get_status_display($db_status, $rapat['tanggal_rapat'], $rapat['jam_rapat']);
                                $jam_wib = date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB';
                                // Efek visual baris (opsional)
                                $row_class = ($db_status == 'dibatalkan') ? 'row-cancelled' : '';
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars(tgl_indo($rapat['tanggal_rapat'])); ?></td>
                            <td><?php echo $jam_wib; ?></td>
                            <td><?php echo htmlspecialchars($rapat['nama_unit']); ?></td>
                            <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
                            <td><?php echo htmlspecialchars($rapat['ruang_rapat']); ?></td>
                            <td class="text-center status-cell">
                                <span class="badge <?php echo $status_display['class']; ?> rounded-pill px-3 shadow-sm">
                                    <?php echo $status_display['text']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <!-- End Tabel -->
            </div>
        </main>
    </section>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.4/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.4/js/responsive.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/peserta.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
  AOS.init();
  
  // Data Tabel
  $(document).ready(function () {
    $('#tabel-rapat').DataTable({
        responsive: false, 
        scrollX: true,
        scrollCollapse: true,
        columnDefs: [
            { className: "text-center", targets: [0, 6] },
            { className: "align-middle", targets: "_all" },
            { width: "50px", targets: 0 },   
            { width: "150px", targets: 1 },  
            { width: "100px", targets: 2 },  
            { width: "150px", targets: 3 },  
            { width: "150px", targets: 4 },  
            { width: "150px", targets: 5 },
            { width: "100px", targets: 6 }
        ],
        "language": {
            "emptyTable": "Tidak ada agenda rapat",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ agenda",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 agenda",
            "infoFiltered": "(difilter dari total _MAX_ agenda)",
            "lengthMenu": "Tampilkan _MENU_ agenda",
            "search": "Cari:",
            "zeroRecords": "Tidak ditemukan agenda rapat yang cocok"
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
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>