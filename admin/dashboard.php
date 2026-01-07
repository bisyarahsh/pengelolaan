<?php
session_start();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

include("../php/koneksi.php");

// Cek Sesi dan Akses
if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

$current_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';

if ($current_role != "admin") {
    header("location:../login/login.php?error=noaccess");
    exit;
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

// Penghapusan Otomatis Untuk Rapat yang Tanggal sudah terlewat dengan status dibatalkan

$sql_cleanup = "DELETE FROM agenda_rapat 
                WHERE status = 'dibatalkan' 
                AND CONCAT(tanggal_rapat, ' ', jam_rapat) <= NOW()";
mysqli_query($koneksi, $sql_cleanup);

// DATA FETCHING UNTUK CARD DASHBOARD

// Total Agenda Rapat
$sql_agenda = "SELECT COUNT(*) as total FROM agenda_rapat 
               WHERE CONCAT(tanggal_rapat, ' ', jam_rapat) > NOW() AND status = 'aktif'";
$q_agenda = mysqli_query($koneksi, $sql_agenda);
$total_agenda = mysqli_fetch_assoc($q_agenda)['total'];

// Total Riwayat Rapat
$sql_riwayat = "SELECT COUNT(*) as total FROM agenda_rapat 
                WHERE status = 'aktif' AND CONCAT(tanggal_rapat, ' ', jam_rapat) <= NOW()";
$q_riwayat = mysqli_query($koneksi, $sql_riwayat);
$total_riwayat = mysqli_fetch_assoc($q_riwayat)['total'];

// Total Unit
$sql_unit = "SELECT COUNT(*) as total FROM unit";
$q_unit = mysqli_query($koneksi, $sql_unit);
$total_unit = mysqli_fetch_assoc($q_unit)['total'];


// DATA FETCHING UNTUK UNIT STATISTIK
$sql_chart = "SELECT u.nama_unit, COUNT(r.id_rapat) as jumlah_rapat
              FROM unit u
              LEFT JOIN agenda_rapat r ON u.id_unit = r.id_unit
              GROUP BY u.id_unit
              ORDER BY jumlah_rapat DESC";
$q_chart = mysqli_query($koneksi, $sql_chart);

$labels_unit = [];
$data_rapat = [];

while ($row = mysqli_fetch_assoc($q_chart)) {
    $labels_unit[] = $row['nama_unit'];
    $data_rapat[] = $row['jumlah_rapat'];
}

// DATA FETCHING UNTUK RASIO RAPAT
$labels_status = ['Agenda (Aktif)', 'Riwayat (Selesai)'];
$data_status = [$total_agenda, $total_riwayat];

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
    <title>Dasbor | Admin - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
</head>
<body>
    
    <!-- Sidebar -->
    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
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

        <main>
            <div class="container-fluid p-0">
                <div class="d-sm-flex align-items-center justify-content-between mb-4" data-aos="fade-down" data-aos-duration="800">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Tinjauan Dasbor</h1>
                </div>

				<!-- Card -->
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
                                    <div class="col-auto"><i class="fas fa-calendar fa-2x text-gray-300 stat-icon text-primary"></i></div>
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
                                    <div class="col-auto"><i class="fas fa-history fa-2x text-gray-300 stat-icon text-success"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="card card-stat border-left-warning h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-label text-warning mb-1">Total Unit</div>
                                        <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_unit; ?></div>
                                        <a href="unit.php" class="text-decoration-none small text-muted mt-2 d-inline-block">Lihat Detail &rarr;</a>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300 stat-icon text-warning"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Card -->
                
                <!-- Chart -->
                <div class="row g-4">
                    <div class="col-xl-8 col-lg-7" data-aos="zoom-in-right" data-aos-delay="400" data-aos-duration="1000">
                        <div class="card card-chart mb-4">
                            <div class="card-header card-header-chart d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary text-uppercase" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-bar me-2"></i>Frekuensi Rapat per Unit
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="myBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-5" data-aos="zoom-in-left" data-aos-delay="600" data-aos-duration="1000">
                        <div class="card card-chart mb-4">
                            <div class="card-header card-header-chart d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-success text-uppercase" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-pie me-2"></i>Rasio Status Rapat
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="doughnut-chart-container">
                                    <canvas id="myPieChart"></canvas>
                                </div>
                                <div class="mt-4 text-center small">
                                    <span class="mr-2 mx-2">
                                        <i class="fas fa-circle text-primary"></i> Agenda
                                    </span>
                                    <span class="mr-2 mx-2">
                                        <i class="fas fa-circle text-success"></i> Riwayat
                                    </span>
                                </div>
                                <div class="text-center mt-2 text-muted small">
                                    <i>Total seluruh rapat: <b><?php echo $total_agenda + $total_riwayat; ?></b></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Chart -->
            </div>
        </main>
    </section>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../assets/admin.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init({ once: true, duration: 800, easing: 'ease-out-cubic' });
    </script>

    <script>
        // CHART SETTINGS 
        Chart.defaults.font.family = "'Open Sans', sans-serif";
        Chart.defaults.color = '#858796';

        // Statistik Unit
        const ctxBar = document.getElementById('myBarChart').getContext('2d');

        let gradientBar = ctxBar.createLinearGradient(0, 0, 0, 400);
        gradientBar.addColorStop(0, 'rgba(78, 115, 223, 0.9)'); 
        gradientBar.addColorStop(1, 'rgba(78, 115, 223, 0.2)');

        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_unit); ?>,
                datasets: [{
                    label: 'Total Rapat',
                    data: <?php echo json_encode($data_rapat); ?>,
                    backgroundColor: gradientBar,
                    borderColor: '#4e73df',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6,
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "#fff",
                        bodyColor: "#858796",
                        titleColor: '#6e707e',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, padding: 10 },
                        grid: { color: "#eaecf4", drawBorder: false }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 45, minRotation: 0 }
                    }
                }
            }
        });

        // Rasio Rapat
        const ctxPie = document.getElementById('myPieChart').getContext('2d');
        
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($labels_status); ?>,
                datasets: [{
                    data: <?php echo json_encode($data_status); ?>,
                    backgroundColor: ['#4e73df', '#1cc88a'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                    borderWidth: 4,
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: "#fff",
                        bodyColor: "#858796",
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' Rapat';
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    </script>
</body>
</html>