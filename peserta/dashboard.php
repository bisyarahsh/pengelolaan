<?php
session_start();
include("../php/koneksi.php");

// --- 1. CEK AKSES & ID USER ---
if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$current_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';

if ($current_role != "peserta") {
    header("location:../login/login.php?error=noaccess");
    exit;
}

$q_user_info = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id_user = '$id_user'");
$d_user_info = mysqli_fetch_assoc($q_user_info);
$nama_lengkap = $d_user_info['nama_lengkap'];

$show_password_alert = false;
if (!isset($_SESSION['password_alert_shown']) || $_SESSION['password_alert_shown'] !== true) {
    $q_user_data = mysqli_query($koneksi, "SELECT nim, password FROM users WHERE id_user = '$id_user'");
    $user_data = mysqli_fetch_assoc($q_user_data);

    if ($user_data) {
        $nim = $user_data['nim'];
        $hashed_password = $user_data['password'];
        
        // Cek apakah password cocok dengan NIM (Default)
        if (password_verify($nim, $hashed_password)) {
            $show_password_alert = true;
            // Set session agar tidak muncul lagi sampai login ulang
            $_SESSION['password_alert_shown'] = true; 
        }
    }
}
// --- END LOGIKA PASSWORD ---

// --- 2. DATA FETCHING (PERSONAL) ---

// A. Agenda Mendatang (Saya)
$sql_agenda = "SELECT COUNT(*) as total 
               FROM agenda_rapat ar
               JOIN peserta_rapat pr ON ar.id_rapat = pr.id_rapat
               WHERE pr.id_user = '$id_user' 
               AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) > NOW()
               AND ar.status = 'aktif'";
$total_agenda = mysqli_fetch_assoc(mysqli_query($koneksi, $sql_agenda))['total'];

// B. Riwayat Rapat (Saya)
$sql_riwayat = "SELECT COUNT(*) as total 
                FROM agenda_rapat ar
                JOIN peserta_rapat pr ON ar.id_rapat = pr.id_rapat
                WHERE pr.id_user = '$id_user' 
                AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) <= NOW() AND ar.status = 'aktif'"; // Menghitung yang sudah lewat (aktif/batal)
$total_riwayat = mysqli_fetch_assoc(mysqli_query($koneksi, $sql_riwayat))['total'];

// C. Rapat Bulan Ini
$current_month = date('m');
$current_year = date('Y');
$sql_month_now = "SELECT COUNT(*) as total 
                  FROM agenda_rapat ar
                  JOIN peserta_rapat pr ON ar.id_rapat = pr.id_rapat
                  WHERE pr.id_user = '$id_user' 
                  AND MONTH(ar.tanggal_rapat) = '$current_month' 
                  AND YEAR(ar.tanggal_rapat) = '$current_year'";
$total_month = mysqli_fetch_assoc(mysqli_query($koneksi, $sql_month_now))['total'];

// --- 3. DATA GRAFIK ---
$monthly_data = [];
for ($m = 1; $m <= 12; $m++) {
    $sql_chart = "SELECT COUNT(*) as total 
                  FROM agenda_rapat ar
                  JOIN peserta_rapat pr ON ar.id_rapat = pr.id_rapat
                  WHERE pr.id_user = '$id_user' 
                  AND MONTH(ar.tanggal_rapat) = '$m' 
                  AND YEAR(ar.tanggal_rapat) = '$current_year'";
    $res_chart = mysqli_fetch_assoc(mysqli_query($koneksi, $sql_chart));
    $monthly_data[] = $res_chart['total'];
}

// --- 4. AGENDA TERDEKAT ---
$sql_next = "SELECT ar.*, o.nama_unit 
             FROM agenda_rapat ar
             JOIN peserta_rapat pr ON ar.id_rapat = pr.id_rapat
             LEFT JOIN unit o ON ar.id_unit = o.id_unit
             WHERE pr.id_user = '$id_user' 
             AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) > NOW()
             AND ar.status = 'aktif'
             ORDER BY ar.tanggal_rapat ASC, ar.jam_rapat ASC LIMIT 3";
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
    <link rel="stylesheet" href="../assets/peserta.css">
    <title>Dashboard | Peserta - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
        body { background-color: #f8f9fc; }
        
        .card-stat {
            border: none; border-radius: 20px; background: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden;
        }
        .card-stat:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .border-left-primary { border-left: 5px solid #4e73df !important; }
        .border-left-success { border-left: 5px solid #1cc88a !important; }
        .border-left-warning { border-left: 5px solid #f6c23e !important; }

        .stat-icon {
            font-size: 2.5rem; opacity: 0.3; transform: rotate(15deg); transition: all 0.3s;
        }
        .card-stat:hover .stat-icon { opacity: 0.6; transform: rotate(0deg) scale(1.1); }
        .stat-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

        .card-chart {
             border: none; border-radius: 20px;
             box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
             height: 100%;
        }
        .chart-container { position: relative; height: 300px; width: 100%; }
        
        .date-badge {
            background: #f8f9fc; border-radius: 8px; padding: 5px 10px;
            text-align: center; min-width: 60px;
        }
        .date-day { font-size: 1.2rem; font-weight: bold; line-height: 1; }
        .date-month { font-size: 0.7rem; text-transform: uppercase; color: #888; }
        .list-group-item { border: none; border-bottom: 1px solid #f1f1f1; }
        .list-group-item:last-child { border-bottom: none; }
    </style>
</head>
<body>

    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="history.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="ganti_password.php"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
        </ul>
    </section>

    <section id="content">
        <nav class="atas">
            <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar' ></i>
        </nav>

        <main>
            <div class="container-fluid p-0">
                
                <div class="d-sm-flex align-items-center justify-content-between mb-4" data-aos="fade-down">
                    <h1 class="h3 mb-0 text-gray-800 fw-bold">Selamat Datang, <?php echo htmlspecialchars($nama_lengkap); ?>!</h1>
                </div>

                <div class="row g-4 mb-5">
                    
                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="card card-stat border-left-primary h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-label text-primary mb-1">Agenda Saya</div>
                                        <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_agenda; ?></div>
                                        <a href="agenda.php" class="text-decoration-none small text-muted mt-2 d-inline-block">Lihat Jadwal &rarr;</a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-check fa-2x text-gray-300 stat-icon text-primary"></i>
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
                                        <a href="history.php" class="text-decoration-none small text-muted mt-2 d-inline-block">Lihat History &rarr;</a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock-rotate-left fa-2x text-gray-300 stat-icon text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="card card-stat border-left-warning h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-label text-warning mb-1">Rapat Bulan Ini</div>
                                        <div class="h3 mb-0 fw-bold text-gray-800"><?php echo $total_month; ?></div>
                                        <small class="text-muted">Aktivitas bulan <?php echo date('F'); ?></small>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-2x text-gray-300 stat-icon text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row g-4">
                    
                    <div class="col-lg-8" data-aos="zoom-in-right" data-aos-delay="400">
                        <div class="card card-chart mb-4 h-100">
                            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary text-uppercase" style="letter-spacing: 1px;">
                                    <i class="fas fa-chart-area me-2"></i>Keaktifan Saya Tahun <?php echo $current_year; ?>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="myAreaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4" data-aos="zoom-in-left" data-aos-delay="500">
                        <div class="card card-chart mb-4 h-100">
                            <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-success text-uppercase" style="letter-spacing: 1px;">
                                    <i class="fas fa-bell me-2"></i>Segera Datang
                                </h6>
                                <a href="agenda.php" class="text-decoration-none small">Semua</a>
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
                                                <small class="text-muted d-block text-truncate">
                                                    <i class="fas fa-clock me-1 text-warning"></i> <?php echo date('H:i', strtotime($next['jam_rapat'])); ?> WIB
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?php echo $next['ruang_rapat']; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-mug-hot fa-3x mb-3 text-gray-300"></i>
                                        <p class="small">Belum ada jadwal rapat untuk Anda.</p>
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
    <script src="../assets/peserta.js"></script> 
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        AOS.init({ once: true, duration: 800, easing: 'ease-out-cubic' });

        // --- CHART JS ---
        Chart.defaults.font.family = "'Open Sans', sans-serif";
        Chart.defaults.color = '#858796';
        const ctx = document.getElementById('myAreaChart').getContext('2d');
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(78, 115, 223, 0.4)');
        gradient.addColorStop(1, 'rgba(78, 115, 223, 0.05)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
                datasets: [{
                    label: "Jumlah Rapat",
                    data: <?php echo json_encode($monthly_data); ?>,
                    backgroundColor: gradient,
                    borderColor: "#4e73df",
                    pointBackgroundColor: "#4e73df",
                    pointBorderColor: "#fff",
                    fill: true,
                    tension: 0.4 
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // --- [BARU] SWEETALERT LOGIC ---
        <?php if ($show_password_alert): ?>
        Swal.fire({
            title: 'Peringatan Keamanan!',
            html: "Kata sandi Anda masih menggunakan sandi bawaan (NIM Anda).<br><br>Untuk keamanan akun, sangat disarankan untuk mengganti kata sandi Anda sekarang.",
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
                window.location.href = 'ganti_password.php';
            }
        });
        <?php endif; ?>

    </script>
</body>
</html>