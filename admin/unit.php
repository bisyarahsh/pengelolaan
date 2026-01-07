<?php
include '../php/koneksi.php';
// Memulai sesi
session_start();
// Cek apakah user sudah login
if ($_SESSION['status'] != "login") {
    // Jika belum login, arahkan ke halaman login
    header("location:../login/login.php");
    exit;
}
// Cek apakah role user bukan 'Admin'
if ($_SESSION['role'] != "Admin") {
    // Jika bukan Admin, tolak akses dan arahkan kembali
    header("location:../login/login.php");
    exit;
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

// MODIFIKASI: INTEGRITAS DATA
// Ambil data unit, sekaligus cek apakah unit sudah terpakai di tabel 'user' atau 'agenda_rapat'
$sql = "
    SELECT 
        u.id_unit, 
        u.nama_unit,
        -- Cek apakah unit terpakai di tabel user
        (SELECT COUNT(id_user) FROM users WHERE unit_id = u.id_unit) AS user_count,
        -- Cek apakah unit terpakai di tabel agenda_rapat (unit_id)
        (SELECT COUNT(id_rapat) FROM agenda_rapat WHERE id_unit = u.id_unit) AS rapat_count
    FROM 
        unit u
    ORDER BY 
        u.nama_unit ASC";
$result = $koneksi->query($sql);

$unit = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Tambahkan flag 'is_used' untuk mempermudah pengecekan di HTML
        $row['is_used'] = ($row['user_count'] > 0 || $row['rapat_count'] > 0);
        $unit[] = $row;
    }
}
$koneksi->close(); // Menggunakan $koneksi

// Ambil flash message dari session jika ada
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); // Hapus setelah diambil
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.4/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/admin.css">
    <title>Unit | Admin - Rapatin</title>
	<link rel="shortcut icon" href="../assets/logo/logo.png">
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
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="unit.php" class="active"><i class="fa-solid fa-users icon"></i>Unit</a></li>
            <li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Anggota</a></li>
            <li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Pengaturan</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
        </ul>
    </section>
	<!-- End Sidebar -->
	
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

                        <div class="img-profile-initials">
                            <?php echo $initials; ?>
                        </div>
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

        <main id="unit">
            <div data-aos="fade-down" class="rapat bg-light">
                <div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
                    <h2 class="text-primary fw-bold m-0 fs-3">Unit</h2>
				    <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="fa-solid fa-plus me-2"></i>Tambah Unit
				    </button>
				</div>
                <!-- Table -->
                <table id="tabel-rapat" class="table table-striped table-hover nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Unit</th>
                            <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($unit as $org): 
                            $disable_delete = $org['is_used'] ? 'disabled' : ''; 
                            $tooltip_delete = $org['is_used'] ? 'title="Unit ini sudah terpakai di Pengguna atau Rapat, tidak bisa dihapus."' : 'title="Hapus Unit"';
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($org['nama_unit']) ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary aksi edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $org['id_unit'] ?>" data-nama="<?= htmlspecialchars($org['nama_unit']) ?>" title="Edit Unit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-danger aksi delete-btn" data-bs-toggle="modal" data-bs-target="#deletemodal" data-id="<?= $org['id_unit'] ?>" data-nama="<?= htmlspecialchars($org['nama_unit']) ?>" <?= $disable_delete ?> <?= $tooltip_delete ?>>
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
					<!-- End Table -->
					
					<!-- Modal Tambah -->
					<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header header-primary">
									<h5 class="modal-title" id="tambahModalLabel">Tambah Unit</h5>
                                </div>
								<form class="needs-validation" novalidate method="POST" action="../php/add_unit.php">
									<div class="modal-body">
										<div class="mb-3">
											<label class="mb-2" for="nama_unit">Nama Unit</label>
											<input class="form-control" type="text" name="nama_unit" id="nama_unit" placeholder="Masukkan Nama Unit..." required>
                                            <div class="invalid-feedback">Nama unit wajib diisi.</div>
                                        </div>
                                    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="tambah_unit" class="btn btn-primary">Tambah</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
					<!-- End Modal Tambah -->
					
					<!-- Modal Edit -->
					<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header header-primary">
									<h5 class="modal-title" id="editModalLabel">Edit Unit</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
								<form method="POST" action="../php/edit_unit.php">
									<div class="modal-body">
										<input type="hidden" name="edit_id_unit" id="edit_id_unit">
										<div class="mb-3">
											<label class="mb-2" for="edit_nama_unit">Nama Unit</label>
                                        	<input class="form-control" type="text" name="edit_nama_unit" id="edit_nama_unit"placeholder="Masukkan Nama Unit..." required>
                                        </div>
                                    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
										<button type="submit" name="edit_unit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
					<!-- End Modal Edit -->
					
                    <!-- Modal Delete -->
			        <div class="modal fade" id="deletemodal" tabindex="-1" aria-hidden="true">
			            	<div class="modal-dialog modal-dialog-centered modal-sm"> <div class="modal-content text-center">
			                    <div class="modal-body pt-5 pb-4">
			                        <form method="POST" action="../php/delete_unit.php">
			                            <input type="hidden" name="hapus_id_unit"id="hapus_id_unit"> 

			                            <div class="modal-icon-wrapper">
			                                <i class="fa-solid fa-triangle-exclamation"></i>
			                            </div>

			                            <h4 class="fw-bold mb-2">Hapus Unit?</h4>
			                            <p class="text-muted mb-4 text-small">Tindakan ini tidak dapat dibatalkan. Data unit akan hilang permanen.</p>

			                            <div class="d-grid gap-2">
			                                <button type="submit" name="hapus_unit" class="btn btn-danger btn-lg shadow-sm">Ya, Hapus Sekarang</button> 
			                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
			                            </div>
			                        </form>
			                    </div>
			                </div>
			            </div>
			        </div>
			        <!-- End Modal Delete -->
                </div>
            </main>
        </section>
    
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.4/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/2.3.4/js/responsive.bootstrap5.js"></script>
<script src="../assets/admin.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    AOS.init();
    // Validasi Form
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#tabel-rapat').DataTable({
        responsive: false, 
        scrollX: true,
        scrollCollapse: true,
    
        columnDefs: [
            { className: "text-center", targets: [0, 2] }, // No & Aksi tengah
            { className: "align-middle", targets: "_all" }, // Vertikal tengah
            
            { width: "50px", targets: 0 },   // No
            { width: "150px", targets: 1 },  // Nama Unit
            { width: "150px", targets: 2 } // Aksi
        ],
        "language": {
            "emptyTable": "Tidak Ada Unit",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ unit",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 unit",
            "infoFiltered": "(difilter dari total _MAX_ unit)",
            "lengthMenu": "Tampilkan _MENU_ unit",
            "search": "Cari:",
            "zeroRecords": "Unit Tidak Ditemukan",
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    });
});

// Alert
const alertData = <?= json_encode($alert); ?>;
if (alertData && alertData.message) {
    Swal.fire({
        title: alertData.icon === 'success' ? "Selamat!" : "Perhatian!",
        text: alertData.message,
        icon: alertData.icon,
        timer: 3000,
        showConfirmButton: false
    });
}

$(document).ready(function () {
    // Modal Edit
    $('.edit-btn').on('click', function () {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        $('#edit_id_unit').val(id);
        $('#edit_nama_unit').val(nama);
    });
    // Modal Delete
    $('.delete-btn').on('click', function () {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        $('#hapus_id_unit').val(id);
        $('#hapus_nama').text(nama);
    });
});
</script>
</body>
</html>