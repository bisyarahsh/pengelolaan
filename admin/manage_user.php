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

// Ambil semua data pengguna (kecuali Admin) dan gabungkan dengan nama unit
$sql = "SELECT u.id_user, u.nim, u.nama_lengkap, u.email, u.role, u.unit_id, o.nama_unit 
        FROM users u
        LEFT JOIN unit o ON u.unit_id = o.id_unit
        WHERE u.role != 'Admin' 
        ORDER BY u.nim ASC";
$result = $koneksi->query($sql);
$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Ambil semua data unit untuk dropdown di modal
$org_sql = "SELECT id_unit, nama_unit FROM unit ORDER BY nama_unit ASC";
$org_result = $koneksi->query($org_sql);
$unit_list = [];
if ($org_result->num_rows > 0) {
    while($row = $org_result->fetch_assoc()) {
        $unit_list[] = $row;
    }
}

$koneksi->close(); 

// Ambil flash message dari session jika ada
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 
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
    <title>Anggota | Admin - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
    /* Pemisah kolom pada modal landscape */
    @media (min-width: 768px) {
        .border-end {
            border-right: 1px solid #dee2e6 !important;
        }
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .modal-header {
        border-radius: 12px 12px 0 0;
    }

    /* Memperbaiki tampilan input focus */
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
</style>
</head>
<body>
    <div id="loader-wrapper">
        <div class="loader-spinner"></div>
    </div>
    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="unit.php"><i class="fa-solid fa-users icon"></i> Unit</a></li>
            <li><a href="manage_user.php" class="active"><i class="fa-solid fa-user icon"></i> Anggota</a></li>
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
            <div data-aos="fade-down" class="rapat bg-light">
                <div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
				    <h2 class="text-primary fw-bold m-0 fs-3">Anggota</h2>
				    <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#tambahModal">
				        <i class="fa-solid fa-plus me-2"></i>Tambah Anggota
				    </button>
				</div>
                <table id="tabel-rapat" class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-start">NIK</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Unit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($users as $user): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="text-start"><?= htmlspecialchars($user['nim']) ?></td>
                            <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php 
                                if ($user['role'] == 'Peserta') {
                                    echo 'Dosen/Labor';
                                } elseif ($user['role'] == 'Ketua') {
                                    echo 'Kaprodi';
                                } else {
                                    echo htmlspecialchars($user['role']);
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($user['nama_unit'] ?? '-') ?></td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-primary aksi edit-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal"
                                    data-id="<?= $user['id_user'] ?>"
                                    data-nim="<?= $user['nim'] ?>"
                                    data-nama="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                    data-role="<?= $user['role'] ?>"
                                    data-unit="<?= $user['unit_id'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-danger aksi delete-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deletemodal"
                                    data-id="<?= $user['id_user'] ?>"
                                    data-nama="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Modal Tambah Anggota -->
                <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered"> 
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="tambahModalLabel"><i class="fa-solid fa-user-plus me-2"></i>Tambah Anggota Baru</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="needs-validation" novalidate method="POST" action="../php/add_user.php">
                                <div class="modal-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6 border-end">
                                            <h6 class="text-primary fw-bold mb-3 text-uppercase small">Informasi Anggota</h6>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">NIK</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-id-card"></i></span>
                                                    <input class="form-control" type="number" name="nim" id="nim" placeholder="Masukkan NIK..." required>
                                                    <div class="invalid-feedback">NIK wajib diisi.</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                                                    <input class="form-control" type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Nama lengkap sesuai identitas..." required>
                                                    <div class="invalid-feedback">Nama wajib diisi.</div>
                                                </div>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label fw-semibold">Kata Sandi</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                                                    <input class="form-control" type="password" name="password" id="password" placeholder="Kata Sandi Bawaan: NIK (jika kosong)">
                                                </div>
                                                <small class="text-muted" style="font-size: 0.75rem;">*Kosongkan untuk menjadikan NIK sebagai kata sandi.</small>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="text-primary fw-bold mb-3 text-uppercase small">Detail Anggota</h6>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-envelope"></i></span>
                                                    <input class="form-control" type="email" name="email" id="email" placeholder="contoh@gmail.com" required>
                                                    <div class="invalid-feedback">Email tidak valid.</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Peran</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-user-tag"></i></span>
                                                    <select class="form-select" name="role" id="role" required>
                                                        <option value="" disabled selected>Pilih Peran...</option>
                                                        <option value="Ketua">Ketua Prodi</option>
                                                        <option value="Peserta">Dosen/Labor</option>
                                                    </select>
                                                    <div class="invalid-feedback">Pilih peran.</div>
                                                </div>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label fw-semibold">Unit</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-building"></i></span>
                                                    <select class="form-select" name="unit_id" id="unit_id" required>
                                                        <option value="" disabled selected>Pilih unit...</option>
                                                        <?php foreach ($unit_list as $org): ?>
                                                            <option value="<?= $org['id_unit'] ?>"><?= htmlspecialchars($org['nama_unit']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="invalid-feedback">Pilih unit.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-0">
                                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="tambah_pengguna" class="btn btn-primary px-4 shadow-sm"><i class="fa-solid fa-save me-2"></i>Simpan Anggota</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End Modal Tambah Anggota -->

                <!-- Modal Edit Anggota -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="editModalLabel"><i class="fa-solid fa-user-pen me-2"></i>Edit Informasi Anggota</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="needs-validation" novalidate method="POST" action="../php/edit_user.php">
                                <div class="modal-body p-4">
                                    <input type="hidden" name="edit_id_user" id="edit_id_user"> 
                                    <div class="row g-4">
                                        <div class="col-md-6 border-end">
                                            <h6 class="text-primary fw-bold mb-3 text-uppercase small">Informasi Anggota</h6>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">NIK</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-id-card"></i></span>
                                                    <input class="form-control" type="number" name="edit_nim" id="edit_nim" required>
                                                    <div class="invalid-feedback">NIK wajib diisi.</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                                                    <input class="form-control" type="text" name="edit_nama_lengkap" id="edit_nama_lengkap" required>
                                                    <div class="invalid-feedback">Nama wajib diisi.</div>
                                                </div>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label fw-semibold">Kata Sandi</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                                                     <input class="form-control" type="password" name="edit_password" id="edit_password" placeholder="Biarkan kosong jika tetap...">
                                                </div>
                                                <small class="text-muted" style="font-size: 0.75rem;">*Kosongkan untuk menjadikan NIK sebagai kata sandi.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary fw-bold mb-3 text-uppercase small">Detail Anggota</h6>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-envelope"></i></span>
                                                    <input class="form-control" type="email" name="edit_email" id="edit_email" required>
                                                    <div class="invalid-feedback">Email tidak valid.</div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Peran</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-user-tag"></i></span>
                                                    <select class="form-select" name="edit_role" id="edit_role" required>
                                                        <option value="" disabled selected>Pilih Peran...</option>
                                                        <option value="Ketua">Ketua Prodi</option>
                                                        <option value="Peserta">Dosen/Labor</option>
                                                    </select>
                                                    <div class="invalid-feedback">Pilih peran.</div>
                                                </div>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label fw-semibold">Unit</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="fa-solid fa-building"></i></span>
                                                    <select class="form-select" name="edit_unit_id" id="edit_unit_id" required>
                                                        <option value="" disabled selected>Pilih unit...</option>
                                                        <?php foreach ($unit_list as $org): ?>
                                                            <option value="<?= $org['id_unit'] ?>"><?= htmlspecialchars($org['nama_unit']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="invalid-feedback">Pilih unit.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-0">
                                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="edit_pengguna" class="btn btn-primary px-4 shadow-sm">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End Modal Edit Anggota -->

                <!-- Modal Hapus Anggota -->
			    <div class="modal fade" id="deletemodal" tabindex="-1" aria-hidden="true">
			        	<div class="modal-dialog modal-dialog-centered modal-sm"> <div class="modal-content text-center">
			                <div class="modal-body pt-5 pb-4">
			                    <form method="POST" action="../php/delete_user.php">
			                        <input type="hidden" name="hapus_id_user" id="hapus_id_user"> 
			                        <div class="modal-icon-wrapper">
			                            <i class="fa-solid fa-triangle-exclamation"></i>
			                        </div>
			                        <h4 class="fw-bold mb-2">Hapus Anggota?</h4>
			                        <p class="text-muted mb-4 text-small">Tindakan ini tidak dapat dibatalkan. Data Anggota akan hilang permanen.</p>
			                        <div class="d-grid gap-2">
			                            <button type="submit" name="hapus_pengguna" class="btn btn-danger btn-lg shadow-sm">Ya, Hapus Sekarang</button> 
			                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
			                        </div>
			                    </form>
			                </div>
			            </div>
			        </div>
			    </div>
			    <!-- End Modal Hapus Anggota -->
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

const alertData = <?= json_encode($alert); ?>;
if (alertData && alertData.message) {
    Swal.fire({
        // Gunakan 'Perhatian!' untuk icon warning (duplikasi) atau 'Selamat!' untuk success
        title: alertData.icon === 'success' ? "Selamat!" : "Perhatian!", 
        text: alertData.message,
        icon: alertData.icon, // Membaca 'success' atau 'warning' dari PHP
        timer: 3000,
        showConfirmButton: false
    });
}

// 2. Logika Pengisian Modal Edit & Hapus (JQuery)
$(document).ready(function() {
    // Mengisi data modal Edit
    $('.edit-btn').on('click', function() {
        const id = $(this).data('id');
        const nim = $(this).data('nim');
        const nama = $(this).data('nama');
        const email = $(this).data('email');
        const role = $(this).data('role');
        const unit_id = $(this).data('unit');
        
        $('#edit_id_user').val(id); 
        $('#edit_nim').val(nim);
        $('#edit_nama_lengkap').val(nama);
        $('#edit_email').val(email);
        $('#edit_role').val(role); // Pilih opsi Peran
        $('#edit_unit_id').val(unit_id); // Pilih opsi unit
        
        // Kosongkan field password untuk keamanan
        $('#edit_password').val('');
    });

    // Mengisi data modal Hapus
    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        
        $('#hapus_id_user').val(id); 
        $('#hapus_nama_pengguna').text(nama);
    });

    // MODIFIKASI: Logika untuk default password = NIM pada modal Tambah Pengguna
    $('#tambahModal form').on('submit', function(e) {
        const nim = $('#nim').val();
        const passwordField = $('#password');
        
        // Cek jika field password kosong
        if (passwordField.val() === '') {
            // Pastikan NIM terisi
            if (nim !== '') {
                // Jika kosong, set value password menjadi NIM sebelum dikirim
                passwordField.val(nim);
            }
        }
    });

    $('#tabel-rapat').DataTable({
        responsive: false, 
        scrollX: true,
        scrollCollapse: true,

        columnDefs: [
            { className: "text-center", targets: [0, 6] }, // No & Aksi tengah
            { className: "align-middle", targets: "_all" }, // Vertikal tengah
            
            // Atur lebar minimum agar tabel 'terpaksa' melebar dan scroll muncul
            { width: "50px", targets: 0 },   // No
            { width: "50px", targets: 1 },  // NIM
            { width: "150px", targets: 2 },  // Nama
            { width: "200px", targets: 3 },  // Email
            { width: "50px", targets: 4 },  // Peran
            { width: "150px", targets: 5 },  // Unit
            { width: "150px", targets: 6 }   // Aksi
        ],
        "language": {
            "emptyTable": "Tidak Ada Anggota",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ anggota",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 anggota",
            "infoFiltered": "(difilter dari total _MAX_ anggota)",
            "lengthMenu": "Tampilkan _MENU_ anggota",
            "search": "Cari:",
            "zeroRecords": "Anggota Tidak Ditemukan",
            "paginate": {
                "previous": "<",
                "next": ">"
            }
        }
    });
});
</script>
</body>
</html>