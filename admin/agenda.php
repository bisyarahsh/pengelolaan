<?php
// Memulai sesi
session_start();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

include("../php/koneksi.php");

// --- Set Zona Waktu ke WIB ---
date_default_timezone_set('Asia/Jakarta'); 
// --- End Set Zona Waktu ---
function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);
 
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

// --- Cek Sesi dan Akses ---
if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

// Gunakan strtolower untuk mengatasi inkonsistensi role dari database
$current_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';

if ($current_role != "admin") {
    header("location:../login/login.php?error=noaccess");
    exit;
}

// --- Tambahkan Fungsi Ambil Data Rapat berdasarkan ID (AJAX Helper) ---
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
        // Ambil Peserta
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

// --- 4. DATA USER, FOTO & INISIAL ---
$id_user_login = $_SESSION['id_user'];

// PERHATIAN: Pastikan 'foto' sesuai dengan nama kolom di database Anda
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

// Logika Membuat Inisial (Tetap dibuat untuk jaga-jaga jika foto dihapus fisik)
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

// Tambahkan logika untuk request AJAX detail rapat
if (isset($_GET['action']) && $_GET['action'] == 'get_rapat_detail' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id_rapat = $_GET['id'];
    $data = get_rapat_detail($koneksi, $id_rapat);
    
    // Tambahkan data peserta lengkap (nama, nim) untuk ditampilkan di modal View
    if($data && !empty($data['peserta_id'])) {
        $peserta_details = [];
        $ids = implode("','", $data['peserta_id']);
        $q_detail = mysqli_query($koneksi, "SELECT nim, nama_lengkap FROM users WHERE id_user IN ('$ids')");
        while ($r_detail = mysqli_fetch_assoc($q_detail)) {
            $peserta_details[] = $r_detail['nim'] . ' - ' . $r_detail['nama_lengkap'];
        }
        $data['peserta_details'] = $peserta_details;
    }

    echo json_encode($data);
    exit;
}

$id_pembuat_rapat = $_SESSION['id_user'];
$list_rapat = [];

$tgl_awal  = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : null;
$tgl_akhir = isset($_POST['tgl_akhir']) ? $_POST['tgl_akhir'] : null;

$sql_read = "SELECT 
                r.*, 
                o.nama_unit,
                u.nama_lengkap as nama_pembuat
             FROM agenda_rapat r
             JOIN unit o ON r.id_unit = o.id_unit
             JOIN users u ON r.id_pembuat = u.id_user
             WHERE r.status = 'aktif'";

$sql_read .= " AND CONCAT(r.tanggal_rapat, ' ', r.jam_rapat) > NOW()";

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql_read .= " AND r.tanggal_rapat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$sql_read .= " ORDER BY r.tanggal_rapat ASC, r.jam_rapat ASC";

$q_read = mysqli_query($koneksi, $sql_read);
while ($r_read = mysqli_fetch_assoc($q_read)) {
    $list_rapat[] = $r_read;
}

// Ambil list unit dan Peserta untuk dropdown modal
$list_unit = [];
$q_unit = mysqli_query($koneksi, "SELECT * FROM unit ORDER BY nama_unit");
while ($r_org = mysqli_fetch_assoc($q_unit)) {
    $list_unit[] = $r_org;
}

// Ambil list semua Peserta
$list_peserta = [];
$sql_peserta_admin = "SELECT u.id_user, u.nim, u.nama_lengkap, u.role, n.nama_unit 
                      FROM users u
                      LEFT JOIN unit n ON u.unit_id = n.id_unit
                      WHERE u.role IN ('Peserta', 'Ketua') 
                      ORDER BY u.nama_lengkap ASC";

$q_peserta = mysqli_query($koneksi, $sql_peserta_admin);
while ($r_peserta = mysqli_fetch_assoc($q_peserta)) {
    $list_peserta[] = $r_peserta;
}

// Query untuk mengambil daftar unit
$q_unit = mysqli_query($koneksi, "SELECT id_unit, nama_unit FROM unit ORDER BY nama_unit ASC");
$units = [];
while($r_unit = mysqli_fetch_assoc($q_unit)) {
    $units[] = $r_unit;
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
	<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.4/css/responsive.bootstrap5.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
	<!-- Or for RTL support -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
	<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
	<link rel="stylesheet" href="../assets/admin.css">
	<title>Agenda | Admin - Rapatin</title>
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
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
		<a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
		<ul class="side-menu" data-aos="fade-right">
			<li><a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
			<li><a href="agenda.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
			<li><a href="unit.php"><i class="fa-solid fa-users icon"></i> Unit</a></li>
			<li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Anggota</a></li>
			<li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Pengaturan</a></li>
			<li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
		</ul>
	</section>
	<!-- SIDEBAR -->

	<!-- Content -->
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
				<div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
				    <h2 class="text-primary fw-bold m-0 fs-3">Agenda Rapat</h2>
				    <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#exampleModal">
				        <i class="fa-solid fa-plus me-2"></i>Tambah Agenda
				    </button>
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
    				    <?php $no = 1; foreach ($list_rapat as $rapat) : ?>
    				    <tr>
    				        <td class="text-center"><?php echo $no++; ?></td>
    				        <td><?php echo htmlspecialchars(tgl_indo($rapat['tanggal_rapat'])); ?></td>
    				        <td><?php echo htmlspecialchars(date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB'); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['nama_unit']); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
    				        <td class="text-center">
    				            <button type="button" class="btn btn-warning aksi view-rapat-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-eye"></i></button>
    				            <button type="button" class="btn btn-primary aksi" 
								data-bs-toggle="modal" 
								data-bs-target="#editModal" 
								data-id="<?php echo $rapat['id_rapat']; ?>"
								data-tanggal="<?php echo $rapat['tanggal_rapat']; ?>"
    							data-jam="<?php echo $rapat['jam_rapat']; ?>"
								data-judul="<?php echo htmlspecialchars($rapat['judul_rapat']); ?>"
								data-ruangan="<?php echo htmlspecialchars($rapat['ruang_rapat']); ?>"
								data-keterangan="<?php echo htmlspecialchars($rapat['keterangan']); ?>"
								data-unitid="<?php echo $rapat['id_unit']; ?>"
								data-notulen="<?php echo htmlspecialchars($rapat['notulen_file']); ?>"
								<?php
    							    $peserta_arr_ids = [];
    							    $q_p = mysqli_query($koneksi, "SELECT id_user FROM peserta_rapat WHERE id_rapat = '{$rapat['id_rapat']}'");
    							    while ($r_p = mysqli_fetch_assoc($q_p)) {
    							        $peserta_arr_ids[] = $r_p['id_user'];
    							    }
    							    $peserta_json = json_encode($peserta_arr_ids); 
    							?>
    							data-peserta='<?php echo htmlspecialchars($peserta_json, ENT_QUOTES, 'UTF-8'); ?>'
								>
								<i class="fa-solid fa-pen-to-square"></i></button>
    				            <button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-trash"></i></i></button>
    				            <button type="button" class="btn btn-success aksi" data-bs-toggle="modal" data-bs-target="#notifmodal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-bell"></i></i></button>
    				        </td>
    				    </tr>
    				    <?php endforeach; ?>
    				</tbody>
    			</table>
			
				<!-- Modal Tambah Rapat -->
				<div class="modal fade modal-compact" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				    <div class="modal-dialog modal-xl modal-dialog-centered"> <div class="modal-content">
				            <div class="modal-header">
				                <h5 class="modal-title" id="exampleModalLabel"><i class="fa-solid fa-calendar-plus me-2"></i>Tambah Agenda Baru</h5>
				                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				            </div>
				            <form class="needs-validation" novalidate action="../php/add_rapat.php" method="POST" enctype="multipart/form-data">
				                <div class="modal-body">
				                    <div class="row g-3"> <div class="col-lg-5 border-end">
				                            <h6 class="text-primary fw-bold mb-3 small text-uppercase">Informasi Dasar</h6>

				                            <div class="row g-2 mb-2">
				                                <div class="col-6">
												    <label>Tanggal</label>
												    <input class="form-control form-control-sm input-tanggal" type="text" name="date" id="date" placeholder="Pilih Tanggal..." required>
													<div class="invalid-feedback">Tanggal wajib diisi.</div>
												</div>
												<div class="col-6">
												    <label>Jam</label>
												    <input class="form-control form-control-sm input-jam" type="text" name="time" id="time" placeholder="Pilih Jam..." required>
													<div class="invalid-feedback">Jam wajib diisi.</div>
												</div>
				                            </div>

				                            <div class="mb-2">
				                                <label>Judul Rapat</label>
				                                <input class="form-control form-control-sm" type="text" name="judul" id="judul" placeholder="Contoh: Rapat Evaluasi Bulanan" required>
												<div class="invalid-feedback">Judul rapat tidak boleh kosong.</div>
											</div>

				                            <div class="row g-2 mb-2">
				                                <div class="col-6">
				                                    <label>Ruangan</label>
				                                    <div class="input-group input-group-sm">
				                                        <span class="input-group-text"><i class="fa-solid fa-door-open"></i></span>
				                                        <input class="form-control form-control-sm" type="text" name="ruangan" id="ruangan" placeholder="Nama Ruang" required>
														<div class="invalid-feedback">Ruangan wajib diisi.</div>
													</div>
				                                </div>
				                                <div class="col-6">
				                                    <label>Unit Penyelenggara</label>
				                                    <select class="form-select form-select-sm" name="unit" id="unit" required>
				                                        <option value="" disabled selected>Pilih Unit</option>
				                                        <?php foreach ($list_unit as $org) : ?>
				                                            <option value="<?php echo $org['id_unit']; ?>"><?php echo htmlspecialchars($org['nama_unit']); ?></option>
				                                        <?php endforeach; ?>
				                                    </select>
													<div class="invalid-feedback">Pilih unit penyelenggara.</div>
				                                </div>
				                            </div>
														
				                            <div class="mb-2">
				                                <label>Upload Notulen (Opsional)</label>
				                                <input class="form-control form-control-sm" type="file" id="myFile" name="filename" accept=".pdf,.doc,.docx">
				                            </div>
				                        </div>
														
				                        <div class="col-lg-7">
				                            <div class="participant-area">
				                                <h6 class="text-primary fw-bold mb-2 small text-uppercase">Peserta & Detail</h6>
														
				                                <div class="d-flex justify-content-between align-items-center mb-2">
				                                    <label class="mb-0">Pilih Peserta</label>
				                                    <div class="btn-group btn-group-sm">
				                                        <button type="button" class="btn btn-secondary" id="select_all_peserta" title="Pilih Semua">Pilih Semua Peserta</button>
				                                        <button class="btn btn-primary" type="button" id="btn_select_all_unit" title="Pilih per Unit">Pilih Berdasarkan Unit</button>
				                                    </div>
				                                </div>
														
				                                <div class="input-group input-group-sm mb-2">
				                                    <select class="form-select form-select-sm" id="select_unit_peserta">
				                                        <option value="" selected disabled>Filter Peserta per Unit...</option>
				                                        <?php foreach ($units as $unit): ?>
				                                            <option value="<?= htmlspecialchars($unit['id_unit']) ?>"><?= htmlspecialchars($unit['nama_unit']) ?></option>
				                                        <?php endforeach; ?>
				                                    </select>
				                                </div>
														
				                                <div class="mb-3">
				                                    <select class="form-select" id="multiple-select-field" name="peserta_rapat[]" multiple required style="width: 100%;">
				                                        <?php foreach ($list_peserta as $peserta) : ?>
				                                            <option value="<?php echo $peserta['id_user']; ?>"><?php echo htmlspecialchars($peserta['nim'] . ' - ' . $peserta['nama_lengkap']); ?></option>
				                                        <?php endforeach; ?>
				                                    </select>
													<div class="invalid-feedback">Pilih minimal satu peserta rapat.</div>
				                                </div>
														
				                                <div class="mb-0">
				                                    <label>Keterangan / Catatan</label>
				                                    <textarea class="form-control form-control-sm" name="keterangan" id="keterangan" rows="3" placeholder="Tambahkan catatan singkat agenda rapat..." required></textarea>
													<div class="invalid-feedback">Keterangan / Catatan Wajib diisi.</div>
												</div>
				                            </div>
				                        </div>
														
				                    </div> </div>
				                <div class="modal-footer py-1 bg-light">
				                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
				                    <button type="submit" name="tambah_rapat" class="btn btn-sm btn-primary px-4"><i class="fa-solid fa-save me-1"></i> Simpan Agenda</button> 
				                </div>
				            </form>
				        </div>
				    </div>
				</div>
				<!-- End Modal Tambah Rapat -->
												
				<!-- Modal View Detail Agenda Rapat -->
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
				<!-- End Modal View Detail Agenda Rapat -->
												
				<!-- Modal Edit Agenda Rapat -->
				<div class="modal fade modal-compact" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
				    <div class="modal-dialog modal-xl modal-dialog-centered">
				        <div class="modal-content">
				            <div class="modal-header header-warning"> <h5 class="modal-title" id="editModalLabel"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Agenda</h5>
				                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				            </div>
				            <form action="../php/edit_rapat.php" method="POST" enctype="multipart/form-data" id="form-edit-rapat">
				                <input type="hidden" name="edit_id_rapat" id="edit_rapat_id_unik">
				                <input type="hidden" name="notulen_file_lama" id="notulen_file_lama">

				                <div class="modal-body">
				                    <div class="row g-3">

				                        <div class="col-lg-5 border-end">
				                            <h6 class="text-primary fw-bold mb-3 small text-uppercase">Informasi Dasar</h6>
				                            <div class="row g-2 mb-2">
												<div class="col-6">
												    <label>Tanggal</label>
												    <input class="form-control form-control-sm input-tanggal" type="text" name="edit_date" id="edit_date" placeholder="Pilih Tanggal..." required>
												</div>
												<div class="col-6">
												    <label>Jam</label>
												    <input class="form-control form-control-sm input-jam" type="text" name="edit_time" id="edit_time" placeholder="Pilih Jam..." required>
												</div>
				                            </div>

				                            <div class="mb-2">
				                                <label>Judul Rapat</label>
				                                <input class="form-control form-control-sm" type="text" name="edit_judul" id="edit_judul" required>
				                            </div>

				                            <div class="row g-2 mb-2">
				                                <div class="col-6">
													<label>Ruangan</label>
													<div class="input-group input-group-sm">
														<span class="input-group-text"><i class="fa-solid fa-door-open"></i></span>
				                                    	<input class="form-control form-control-sm" type="text" name="edit_ruangan" id="edit_ruangan">
													</div>
												</div>
				                                <div class="col-6">
				                                    <label>Unit</label>
				                                    <select class="form-select form-select-sm" name="edit_unit" id="edit_unit" required>
				                                        <option value="" disabled selected>Pilih Unit</option>
				                                        <?php foreach ($list_unit as $org) : ?>
				                                            <option value="<?php echo $org['id_unit']; ?>"><?php echo htmlspecialchars($org['nama_unit']); ?></option>
				                                        <?php endforeach; ?>
				                                    </select>
				                                </div>
				                            </div>
														
				                            <div class="mb-2 p-2 bg-light border rounded">
				                                <label class="text-muted small">File Saat Ini:</label>
				                                <div id="current_file_info" class="text-truncate fw-bold text-dark small mb-1"></div>
				                                <label class="mt-1">Ganti File (Opsional)</label>
				                                <input class="form-control form-control-sm" type="file" id="edit_file" name="edit_filename" accept=".pdf,.doc,.docx">
				                            </div>
				                        </div>
														
				                        <div class="col-lg-7">
				                            <div class="participant-area">
				                                <h6 class="text-primary fw-bold mb-2 small text-uppercase">Peserta & Detail</h6>
														
				                                <div class="d-flex justify-content-between align-items-center mb-2">
				                                    <label class="mb-0">Peserta Rapat</label>
				                                    <div class="btn-group btn-group-sm">
				                                        <button type="button" class="btn btn-secondary" id="select_edit_all_peserta">Pilih Semua Peserta</button>
				                                        <button class="btn btn-primary" type="button" id="edit_btn_select_all_unit">Pilih Berdasarkan Unit</button>
				                                    </div>
				                                </div>
														
				                                <div class="input-group input-group-sm mb-2">
				                                     <select class="form-select form-select-sm" id="edit_select_unit_peserta">
				                                        <option value="" selected disabled>Filter Peserta per Unit...</option>
				                                        <?php foreach ($units as $unit): ?>
				                                            <option value="<?= htmlspecialchars($unit['id_unit']) ?>"><?= htmlspecialchars($unit['nama_unit']) ?></option>
				                                        <?php endforeach; ?>
				                                    </select>
				                                </div>
														
				                                <div class="mb-3">
				                                    <select class="form-select select2-edit" id="edit-multiple-select-field" name="edit_peserta_rapat[]" multiple required style="width: 100%;">
				                                        <?php foreach ($list_peserta as $peserta) : ?>
				                                            <option value="<?php echo $peserta['id_user']; ?>"><?php echo htmlspecialchars($peserta['nim'] . ' - ' . $peserta['nama_lengkap']); ?></option>
				                                        <?php endforeach; ?>
				                                    </select>
				                                </div>
														
				                                <div class="mb-0">
				                                    <label>Keterangan</label>
				                                    <textarea class="form-control form-control-sm" name="edit_keterangan" id="edit_keterangan" rows="3"></textarea>
				                                </div>
				                            </div>
				                        </div>
				                    </div>
				                </div>
				                <div class="modal-footer py-1 bg-light">
				                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
				                    <button type="submit" name="edit_rapat" class="btn btn-sm btn-primary px-4"><i class="fa-solid fa-check me-1"></i> Simpan Perubahan</button>
				                </div>
				            </form>
				        </div>
				    </div>
				</div>
				<!-- End Modal Edit Agenda Rapat -->
												
				<!-- Modal Delete Agenda Rapat -->
				<div class="modal fade" id="deletemodal" tabindex="-1" aria-hidden="true">
				    	<div class="modal-dialog modal-dialog-centered modal-sm"> <div class="modal-content text-center">
				            <div class="modal-body pt-5 pb-4">
				                <form method="POST" action="../php/delete_rapat.php">
				                    <input type="hidden" name="hapus_id_rapat" id="hapus_id_rapat_modal"> 
												
				                    <div class="modal-icon-wrapper">
				                        <i class="fa-solid fa-triangle-exclamation"></i>
				                    </div>
												
				                    <h4 class="fw-bold mb-2">Hapus Agenda?</h4>
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
												
				<!-- Modal Notifikasi -->
				<div class="modal fade" id="notifmodal" tabindex="-1" aria-hidden="true">
				    <div class="modal-dialog modal-dialog-centered">
				        <div class="modal-content">
				            <div class="modal-header header-primary">
				                <h5 class="modal-title"><i class="fa-solid fa-paper-plane me-2"></i> Broadcast Notifikasi</h5>
				                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				            </div>
				            <form method="POST" action="../php/send_notification.php"> 
				                <div class="modal-body text-center py-4">
				                    <input type="hidden" name="notif_id_rapat" id="notif_id_rapat"> 
				                    <img src="../assets/illustrations/email_send.svg" alt="Email Icon" style="width: 120px; margin-bottom: 20px;" onerror="this.style.display='none'">
				                    <div class="mb-3"><i class="fa-solid fa-envelope-open-text fa-3x text-primary"></i></div>
												
				                    <h5 class="fw-bold">Kirim Undangan via Email?</h5>
				                    <p class="text-muted">Sistem akan mengirimkan detail rapat ini ke email semua peserta yang terdaftar.</p>
				                </div>
				                <div class="modal-footer justify-content-center">
				                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
				                    <button type="submit" name="send_notification" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-paper-plane me-2"></i> Kirim</button> 
				                </div>
				            </form>
				        </div>
				    </div>
				</div>
				 <!-- End Modal Notifikasi -->
			</div>
		</main>
		<!-- End Main -->
	</section>
	<!-- End Content -->
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
  AOS.init({
      once: true,
      duration: 800,
      easing: 'ease-out-cubic'
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/agenda_handlers.js"></script>
<script>
(function () {
  'use strict'

  var forms = document.querySelectorAll('.needs-validation')

  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
<script type="text/javascript">

$(document).ready(function() {
    $('#tabel-rapat').DataTable({
        responsive: false, 
        scrollX: true,
        scrollCollapse: true,

        columnDefs: [
            { className: "text-center", targets: [0, 5] }, // No & Aksi tengah
            { className: "align-middle", targets: "_all" }, // Vertikal tengah
            
            // Atur lebar minimum agar tabel 'terpaksa' melebar dan scroll muncul
            { width: "50px", targets: 0 },   // No
            { width: "150px", targets: 1 },  // Tanggal (biar gak turun baris)
            { width: "100px", targets: 2 },  // Jam
            { width: "200px", targets: 3 },  // Unit
            { width: "200px", targets: 4 },  // Judul Rapat (paling lebar)
            { width: "150px", targets: 5 }   // Aksi
        ],
        "language": {
            "emptyTable": "Tidak ada agenda rapat",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ agenda",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 agenda",
            "infoFiltered": "(difilter dari total _MAX_ agenda)",
            "lengthMenu": "Tampilkan _MENU_ agenda",
            "search": "Cari:",
            "zeroRecords": "Tidak ditemukan agenda rapat yang cocok",
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

// Handler SweetAlert dari PHP Session harus tetap di sini
<?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_tambah') : ?>
    Swal.fire({
        title: "Selamat!",
        text: "Rapat Berhasil ditambahkan!",
        icon: "success"
    });
<?php elseif (isset($alert) && $alert['type'] == 'success') : ?>
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
</script>
</body>
</html>