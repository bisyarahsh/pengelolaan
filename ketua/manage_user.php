<?php
include 'php/koneksi.php';
// Memulai sesi
session_start();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

// Cek Sesi dan Akses
if ($_SESSION['status'] != "login") {
    header("location:../login/login.php");
    exit;
}
if ($_SESSION['role'] != "Ketua") {
    header("location:../login/login.php");
    exit;
}

// Ambil User ID & Unit ID
$current_user_id = $_SESSION['id_user'] ?? 0;
$unit_id_ketua = 0;

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

$unit_sql = "SELECT unit_id FROM users WHERE id_user = " . intval($current_user_id);
$unit_result = $koneksi->query($unit_sql);

if ($unit_result && $unit_result->num_rows > 0) {
    $unit_row = $unit_result->fetch_assoc();
    $unit_id_ketua = intval($unit_row['unit_id']);
}

// Tampilkan peserta dari unit Ketua
$sql = "SELECT u.id_user, u.nim, u.nama_lengkap, u.email, u.role, u.unit_id, o.nama_unit 
        FROM users u
        LEFT JOIN unit o ON u.unit_id = o.id_unit
        WHERE u.role = 'peserta' AND u.unit_id = {$unit_id_ketua}
        ORDER BY u.nim ASC";

$result = $koneksi->query($sql);
$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$org_sql = "SELECT id_unit, nama_unit FROM unit ORDER BY nama_unit ASC";
$org_result = $koneksi->query($org_sql);
$unit_list = [];
if ($org_result->num_rows > 0) {
    while($row = $org_result->fetch_assoc()) {
        $unit_list[] = $row;
    }
}

$koneksi->close(); 
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
    <title>Pengguna | Ketua - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
</head>
<body>
    
    <!-- Sidebar -->
    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="manage_user.php" class="active"><i class="fa-solid fa-user icon"></i> Anggota</a></li>
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
                <!-- Header dan Button Tambah -->
                <div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
				    <h2 class="text-primary fw-bold m-0 fs-3">Anggota</h2>
				    <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#tambahModal">
				        <i class="fa-solid fa-plus me-2"></i>Tambah Anggota
				    </button>
				</div>
                <!-- Tabel -->
                <table id="tabel-rapat" class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-start">NIK</th>
                            <th>Nama</th>
                            <th>Email</th>
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
                <!-- End Tabel -->
                
                <!-- Modal Tambah -->
                <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
                    <div class="modal-dialog ">
                        <div class="modal-content">
                            <div class="modal-header header-primary">
                                <h5 class="modal-title" id="tambahModalLabel">Tambah Anggota</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form class="needs-validation" novalidate method="POST" action="php/ketua_add_user.php">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="mb-2" for="nim">NIK</label>
                                        <input class="form-control" type="number" name="nim" id="nim" placeholder="Masukkan NIK..." required>
                                        <div class="invalid-feedback">NIK wajib diisi.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-2" for="nama_lengkap">Nama</label>
                                        <input class="form-control" type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Masukkan Nama..." required>
                                        <div class="invalid-feedback">Nama wajib diisi.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="mb-2" for="password">Kata Sandi</label>
                                        <input class="form-control" type="password" name="password" id="password" placeholder="Kata sandi : NIK..." >
                                        <small class="text-muted" style="font-size: 0.75rem;">*Kosongkan untuk menjadikan NIK sebagai kata sandi.</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="mb-2" for="email">Email</label>
                                        <input class="form-control" type="email" name="email" id="email" placeholder="contoh@gmail.com" required>
                                        <div class="invalid-feedback">Email tidak valid.</div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="tambah_pengguna" class="btn btn-primary">Tambah</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- End Modal Tambah -->
                
                <!-- Modal Edit -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog ">
                        <div class="modal-content">
                            <div class="modal-header header-primary">
                                <h5 class="modal-title" id="editModalLabel">Edit Anggota</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="php/ketua_edit_user.php">
                            <div class="modal-body">
                                <input type="hidden" name="edit_id_user" id="edit_id_user"> 
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_nim">NIK</label>
                                    <input class="form-control" type="number" name="edit_nim" id="edit_nim" placeholder="Masukkan NIK..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_nama_lengkap">Nama</label>
                                    <input class="form-control" type="text" name="edit_nama_lengkap" id="edit_nama_lengkap" placeholder="Masukkan Nama..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_password">Kata Sandi (Kosongkan jika tidak ingin diubah)</label>
                                    <input class="form-control" type="password" name="edit_password" id="edit_password" placeholder="Masukkan Kata Sandi Baru...">
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_email">Email</label>
                                    <input class="form-control" type="email" name="edit_email" id="edit_email" placeholder="contoh@gmail.com" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="edit_pengguna" class="btn btn-primary">Simpan Perubahan</button>
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
			                <form method="POST" action="php/ketua_delete_user.php">
			                    <input type="hidden" name="hapus_id_user" id="hapus_id_user"> 
			                    <div class="modal-icon-wrapper">
			                        <i class="fa-solid fa-triangle-exclamation"></i>
			                    </div>
			                    <h4 class="fw-bold mb-2">Hapus Anggota?</h4>
			                    <p class="text-muted mb-4 text-small">Tindakan ini tidak dapat dibatalkan. Data Pengguna akan hilang permanen.</p>
			                    <div class="d-grid gap-2">
			                        <button type="submit" name="hapus_pengguna" class="btn btn-danger btn-lg shadow-sm">Ya, Hapus Sekarang</button> 
			                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batalkan</button>
			                    </div>
			                </form>
			            </div>
			        </div>
			    </div>
			    <!-- End Modal Delete -->
            </div>
        </main>
        <!-- End Main -->
    </section>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
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

    // Modal Edit dan Hapus
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
            $('#edit_role').val(role);
            $('#edit_unit_id').val(unit_id);

            $('#edit_password').val('');
        });

        // Mengisi data modal Hapus
        $('.delete-btn').on('click', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');

            $('#hapus_id_user').val(id); 
            $('#hapus_nama_pengguna').text(nama);
        });

        // Logika untuk default password = NIK
        $('#tambahModal form').on('submit', function(e) {
            const nim = $('#nim').val();
            const passwordField = $('#password');

            if (passwordField.val() === '') {
                if (nim !== '') {
                    passwordField.val(nim);
                }
            }
        });

        // Data Tabel
        $('#tabel-rapat').DataTable({
            responsive: false, 
            scrollX: true,
            scrollCollapse: true,

            columnDefs: [
                { className: "text-center", targets: [0, 5] }, 
                { className: "align-middle", targets: "_all" },

                { width: "50px", targets: 0 },   // No
                { width: "50px", targets: 1 },  // NIM
                { width: "150px", targets: 2 },  // Nama
                { width: "200px", targets: 3 },  // Email
                { width: "150px", targets: 4 },   // Unit
                { width: "150px", targets: 5 }   // Aksi
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