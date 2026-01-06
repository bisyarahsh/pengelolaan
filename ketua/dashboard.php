<?php
session_start();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

include("../php/koneksi.php");

// --- Set Zona Waktu ---
date_default_timezone_set('Asia/Jakarta'); 

// --- Cek Sesi dan Akses ---
if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

$current_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';
if ($current_role != "ketua") {
    header("location:../login/login.php?error=noaccess");
    exit;
}

// --- AMBIL UNIT ID & USER ID ---
$current_user_id = $_SESSION['id_user'];
$id_unit_ketua   = $_SESSION['unit_id'] ?? null; 

$show_password_alert = false;
if (!isset($_SESSION['password_alert_shown']) || $_SESSION['password_alert_shown'] !== true) {
    $q_user_data = mysqli_query($koneksi, "SELECT nim, password FROM users WHERE id_user = '$current_user_id'");
    $user_data = mysqli_fetch_assoc($q_user_data);

    if ($user_data) {
        $nim = $user_data['nim'];
        $hashed_password = $user_data['password'];
        
        // Cek apakah password cocok dengan NIK (Default)
        if (password_verify($nim, $hashed_password)) {
            $show_password_alert = true;
            $_SESSION['password_alert_shown'] = true; 
        }
    }
}

// --- DATA USER, FOTO & INISIAL ---
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

// Logika Membuat Inisial (Tetap dibuat untuk jaga-jaga jika foto dihapus fisik)
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
    $q_unit = mysqli_query($koneksi, "SELECT unit_id FROM users WHERE id_user = '$current_user_id'");
    $r_unit = mysqli_fetch_assoc($q_unit);
    $id_unit_ketua = $r_unit['unit_id'] ?? null;
    if ($id_unit_ketua) {
        $_SESSION['unit_id'] = $id_unit_ketua;
    }
}

// Total Agenda Rapat (Akan Datang)
$sql_agenda = "SELECT COUNT(DISTINCT r.id_rapat) as total 
               FROM agenda_rapat r
               WHERE r.status = 'aktif'
               AND CONCAT(r.tanggal_rapat, ' ', r.jam_rapat) > NOW()
               AND (
                   r.id_unit = '$id_unit_ketua' 
                   OR 
                   r.id_rapat IN (SELECT id_rapat FROM peserta_rapat WHERE id_user = '$current_user_id')
               )";
$q_agenda = mysqli_query($koneksi, $sql_agenda);
$total_agenda = mysqli_fetch_assoc($q_agenda)['total'];

// Total Riwayat Rapat (Sudah Lewat)
$sql_riwayat = "SELECT COUNT(DISTINCT r.id_rapat) as total 
                FROM agenda_rapat r
                WHERE r.status = 'aktif'
                AND CONCAT(r.tanggal_rapat, ' ', r.jam_rapat) <= NOW()
                AND (
                    r.id_unit = '$id_unit_ketua' 
                    OR 
                    r.id_rapat IN (SELECT id_rapat FROM peserta_rapat WHERE id_user = '$current_user_id')
                )";
$q_riwayat = mysqli_query($koneksi, $sql_riwayat);
$total_riwayat = mysqli_fetch_assoc($q_riwayat)['total'];

// Total Anggota Unit
$sql_anggota = "SELECT COUNT(*) as total FROM users WHERE unit_id = '$id_unit_ketua' AND id_user != '$current_user_id'";
$q_anggota = mysqli_query($koneksi, $sql_anggota);
$total_anggota = mysqli_fetch_assoc($q_anggota)['total'];

// Ambil Nama Unit
$q_unit_name = mysqli_query($koneksi, "SELECT nama_unit FROM unit WHERE id_unit = '$id_unit_ketua'");
$nama_unit_ketua = mysqli_fetch_assoc($q_unit_name)['nama_unit'] ?? 'Unit';

// --- DATA GRAFIK  ---
$current_year = date('Y');
$monthly_data = [];
for ($m = 1; $m <= 12; $m++) {
    $sql_month = "SELECT COUNT(*) as total FROM agenda_rapat 
                  WHERE id_unit = '$id_unit_ketua' 
                  AND MONTH(tanggal_rapat) = '$m' 
                  AND YEAR(tanggal_rapat) = '$current_year'";
    $res_month = mysqli_fetch_assoc(mysqli_query($koneksi, $sql_month));
    $monthly_data[] = $res_month['total'];
}

// --- TABEL RINGKAS  ---
$sql_next = "SELECT * FROM agenda_rapat 
            WHERE (
                id_unit = '$id_unit_ketua' 
                OR 
                id_rapat IN (SELECT id_rapat FROM peserta_rapat WHERE id_user = '$current_user_id')
            )
             AND CONCAT(tanggal_rapat, ' ', jam_rapat) > NOW() AND status = 'aktif' 
             ORDER BY tanggal_rapat ASC, jam_rapat ASC LIMIT 3";
$q_next = mysqli_query($koneksi, $sql_next);

function tgl_indo_short($tanggal){
    $bulan = array (1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="../assets/admin.css">
    <title>Dasbor | Ketua - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
        .stat-icon {
            font-size: 2.5rem; opacity: 0.3; transform: rotate(15deg); transition: all 0.3s;
        }
        .card-stat:hover .stat-icon { opacity: 0.6; transform: rotate(0deg) scale(1.1); }
        .stat-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

        /* Style Border Left Warna-Warni */
        .border-left-primary { border-left: 5px solid #4e73df !important; }
        .border-left-success { border-left: 5px solid #1cc88a !important; }
        .border-left-info { border-left: 5px solid #36b9cc !important; } /* Cyan untuk Anggota */

        /* Style List Agenda */
        .date-badge {
            background: #f8f9fc; border-radius: 8px; padding: 5px 10px;
            text-align: center; min-width: 60px;
        }
        .date-day { font-size: 1.2rem; font-weight: bold; line-height: 1; }
        .date-month { font-size: 0.7rem; text-transform: uppercase; color: #888; }
        
        .card-chart {
             border: none;
             border-radius: 20px;
             box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
             height: 100%;
        }
    </style>
</head>
<body>

    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Anggota</a></li>
            <li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Pengaturan</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
        </ul>
    </section>

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

        <main>
            <div class="container-fluid p-0">
                
                <div class="d-sm-flex align-items-center justify-content-between mb-4" data-aos="fade-down" data-aos-duration="800">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Dasbor <?php echo $nama_unit_ketua; ?></h1>
                </div>

                <div class="row g-4 mb-5">
                    
                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="card card-stat border-left-primary h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-label text-primary mb-1">Agenda Rapat</div>
                                        <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_agenda; ?></div>
                                        <a href="agenda.php" class="text-decoration-none small text-muted mt-2 d-inline-block">Lihat Detail &rarr;</a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300 stat-icon text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="card card-stat border-left-success h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-label text-success mb-1">Riwayat Rapat</div>
                                        <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_riwayat; ?></div>
                                        <a href="riwayat.php" class="text-decoration-none small text-muted mt-2 d-inline-block">Lihat Detail &rarr;</a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300 stat-icon text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="card card-stat border-left-info h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-label text-info mb-1">Anggota Unit</div>
                                        <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_anggota; ?></div>
                                        <a href="manage_user.php" class="text-decoration-none small text-muted mt-2 d-inline-block">Lihat Detail &rarr;</a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300 stat-icon text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row g-4">
                    
                    <div class="col-lg-8" data-aos="zoom-in-right" data-aos-delay="500">
                        <div class="card card-chart mb-4 h-100">
                            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary text-uppercase" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-line me-2"></i>Aktivitas Rapat Unit <?php echo $nama_unit_ketua; ?> Tahun <?php echo $current_year; ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                                    <canvas id="myAreaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4" data-aos="zoom-in-left" data-aos-delay="600">
                        <div class="card card-chart mb-4 h-100">
                            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-success text-uppercase" style="letter-spacing: 1px;">
                                    <i class="fas fa-clock me-2"></i>Segera Datang
                                </h6>
                                <a href="agenda.php" class="text-decoration-none small">Lihat Semua</a>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php if (mysqli_num_rows($q_next) > 0): ?>
                                    <?php while($next = mysqli_fetch_assoc($q_next)): ?>
                                        <div class="list-group-item d-flex align-items-center px-4 py-3">
                                            <div class="date-badge me-3 shadow-sm">
                                                <div class="date-day text-primary"><?php echo date('d', strtotime($next['tanggal_rapat'])); ?></div>
                                                <div class="date-month"><?php echo tgl_indo_short($next['tanggal_rapat']); ?></div>
                                            </div>
                                            <div class="flex-grow-1" style="overflow: hidden;">
                                                <h6 class="mb-1 text-truncate fw-bold text-dark" title="<?php echo $next['judul_rapat']; ?>">
                                                    <?php echo $next['judul_rapat']; ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1 text-warning"></i> <?php echo date('H:i', strtotime($next['jam_rapat'])); ?> WIB
                                                    <br>
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo $next['ruang_rapat']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-calendar-check fa-3x mb-3 text-gray-300"></i>
                                        <p class="small">Tidak ada agenda rapat dalam waktu dekat.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </section>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/admin.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>AOS.init({ once: true, duration: 800, easing: 'ease-out-cubic' });</script>

    <script>
        // Chart.js Configuration
        Chart.defaults.font.family = "'Open Sans', sans-serif";
        Chart.defaults.color = '#858796';

        const ctx = document.getElementById('myAreaChart').getContext('2d');
        
        // Gradient Fill
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(78, 115, 223, 0.4)');
        gradient.addColorStop(1, 'rgba(78, 115, 223, 0.05)');

        const myChart = new Chart(ctx, {
            type: 'line', // Gunakan Line chart untuk melihat tren waktu
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                datasets: [{
                    label: "Frekuensi Rapat",
                    data: <?php echo json_encode($monthly_data); ?>,
                    backgroundColor: gradient,
                    borderColor: "#4e73df",
                    pointBackgroundColor: "#4e73df",
                    pointBorderColor: "#fff",
                    pointHoverBackgroundColor: "#fff",
                    pointHoverBorderColor: "#4e73df",
                    fill: true,
                    tension: 0.4 // Garis melengkung halus
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#fff',
                        titleColor: '#6e707e',
                        bodyColor: '#858796',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.raw + ' Rapat';
                            }
                        }
                    }
                },
                scales: {
                    x: { 
                        grid: { display: false },
                        ticks: { maxRotation: 0 } 
                    },
                    y: { 
                        beginAtZero: true,
                        ticks: { stepSize: 1, padding: 10 },
                        grid: { 
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            borderDash: [2], 
                            drawBorder: false 
                        }
                    }
                }
            }
        });

        <?php if ($show_password_alert): ?>
        Swal.fire({
            title: 'Peringatan Keamanan!',
            html: "Kata sandi Anda masih menggunakan sandi bawaan (NIK Anda).<br><br>Untuk keamanan akun, sangat disarankan untuk mengganti kata sandi Anda sekarang.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ganti Kata Sandi Sekarang',
            cancelButtonText: 'Nanti Saja',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'pengaturan.php';
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>