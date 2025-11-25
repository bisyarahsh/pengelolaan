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
// Cek apakah role user bukan 'ketua'
if ($_SESSION['role'] != "Ketua") {
    // Jika bukan ketua, tolak akses dan arahkan kembali
    header("location:../login/login.php");
    exit;
}

// Ambil semua data pengguna dan gabungkan dengan nama organisasi
$sql = "SELECT u.id_user, u.nim, u.nama_lengkap, u.email, u.role, u.organisasi_id, o.nama_organisasi 
        FROM users u
        LEFT JOIN organisasi o ON u.organisasi_id = o.id_organisasi
        ORDER BY u.nim ASC";
$result = $koneksi->query($sql);
$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Ambil semua data organisasi untuk dropdown di modal
$org_sql = "SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi ASC";
$org_result = $koneksi->query($org_sql);
$organisasi_list = [];
if ($org_result->num_rows > 0) {
    while($row = $org_result->fetch_assoc()) {
        $organisasi_list[] = $row;
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
	<title>Pengguna | Ketua - Rapatin</title>
	<link rel="shortcut icon" href="../assets/logo/logo.png">
</head>
<body>
	
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
		<a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
		<ul class="side-menu" data-aos="fade-right">
			<li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
			<li><a href="organisasi.php"><i class="fa-solid fa-users icon"></i> Organisasi</a></li>
			<li><a href="manage_user.php" class="active"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
			<li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
			<li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Logout</a></li>
		</ul>
	</section>
	<!-- SIDEBAR -->

	<!-- Content -->
	<section id="content">
		<!-- Toggle Sidebar -->
		<nav class="atas">
			<i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar' ></i>
		</nav>
		<!-- End Toggle Sidebar -->

		<!-- Main -->
		<main>
			<div data-aos="fade-down" class="rapat bg-light">
				<div class="tableheader">
					<h2>Pengguna</h2>
					<button type="button" class="btn btn-primary tambah" data-bs-toggle="modal" data-bs-target="#tambahModal">Tambah</button>
				</div>
				<table id="example" class="table table-striped">
        			<thead>
        			    <tr>
        			        <th>No</th>
        			        <th>NIM</th>
        			        <th>Nama</th>
        			        <th>Email</th>
        			        <th>Peran</th>
        			        <th>Organisasi</th>
        			        <th>Aksi</th>
        			    </tr>
        			</thead>
        			<tbody>
        			    <?php $no = 1; foreach ($users as $user): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($user['nim']) ?></td>
                            <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['nama_organisasi'] ?? '-') ?></td>
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
                                    data-organisasi="<?= $user['organisasi_id'] ?>">
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
			
				<!-- Modal Tambah Pengguna -->
				<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="tambahModalLabel">Tambah Pengguna</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<form method="POST" action="../php/add_user.php">
								<div class="modal-body">
                                	<div class="mb-3">
                                	    <label class="mb-2" for="nim">NIM</label>
                                	    <input class="form-control" type="number" name="nim" id="nim" placeholder="Masukkan NIM..." required>
                                	</div>
                                	<div class="mb-3">
                                	    <label class="mb-2" for="nama_lengkap">Nama</label>
                                	    <input class="form-control" type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Masukkan Nama..." required>
                                	</div>
                                	<div class="mb-3">
                                	    <label class="mb-2" for="password">Password</label>
                                	    <input class="form-control" type="password" name="password" id="password" placeholder="Masukkan Password..." required>
                                	</div>
                                	<div class="mb-3">
                                	    <label class="mb-2" for="email">Email</label>
                                	    <input class="form-control" type="email" name="email" id="email" placeholder="contoh@gmail.com" required>
                                	</div>
                                	<div class="mb-3">
                                	    <label class="mb-2" for="role">Peran</label>
                                	    <select class="form-select" name="role" id="role" required>
                                	        <option class="disabled" value="">Pilih Peran...</option>
                                	        <option value="Ketua">Ketua</option>
                                	        <option value="Peserta">Peserta</option>
                                	    </select>
                                	</div>
                                	<div class="mb-3">
                                	    <label class="mb-2" for="organisasi_id">Organisasi</label>
                                	    <select class="form-select" name="organisasi_id" id="organisasi_id" required>
                                	        <option class="disabled" disabled value="">Pilih Organisasi...</option>
                                	        <?php foreach ($organisasi_list as $org): ?>
                                	            <option value="<?= $org['id_organisasi'] ?>"><?= htmlspecialchars($org['nama_organisasi']) ?></option>
                                	        <?php endforeach; ?>
                                	    </select>
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

				<!-- Modal Edit Pengguna -->
				<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="editModalLabel">Edit Pengguna</h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							</div>
							<form method="POST" action="../php/edit_user.php">
                            <div class="modal-body">
                                <input type="hidden" name="edit_id_user" id="edit_id_user"> 
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_nim">NIM</label>
                                    <input class="form-control" type="number" name="edit_nim" id="edit_nim" placeholder="Masukkan NIM..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_nama_lengkap">Nama</label>
                                    <input class="form-control" type="text" name="edit_nama_lengkap" id="edit_nama_lengkap" placeholder="Masukkan Nama..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_password">Password (Kosongkan jika tidak ingin diubah)</label>
                                    <input class="form-control" type="password" name="edit_password" id="edit_password" placeholder="Masukkan Password Baru...">
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_email">Email</label>
                                    <input class="form-control" type="email" name="edit_email" id="edit_email" placeholder="contoh@gmail.com" required>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_role">Peran</label>
                                    <select class="form-select" name="edit_role" id="edit_role" required>
                                        <option class="disabled" value="">Pilih Peran...</option>
                                        <option value="Ketua">Ketua</option>
                                        <option value="Peserta">Peserta</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_organisasi_id">Organisasi</label>
                                    <select class="form-select" name="edit_organisasi_id" id="edit_organisasi_id" required>
										<option class="disabled" disabled value="">Pilih Organisasi...</option>
                                	    <?php foreach ($organisasi_list as $org): ?>
                                	        <option value="<?= $org['id_organisasi'] ?>"><?= htmlspecialchars($org['nama_organisasi']) ?></option>
                                	    <?php endforeach; ?>
                                    </select>
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
				<!-- End Modal Edit Pengguna -->

				<!-- Modal Delete Pengguna -->
				<div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="deletemodalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
            	            <form method="POST" action="../php/delete_user.php">
            	                <div class="modal-header">
            	                    <h5 class="modal-title" id="deletemodalLabel">Hapus Pengguna</h5>
            	                </div>
            	                <div class="modal-body">
            	                    <input type="hidden" name="hapus_id_user" id="hapus_id_user"> 
            	                    <p class="h5">Apakah anda yakin ingin menghapus pengguna <span id="hapus_nama_pengguna"></span>?</p>
            	                </div>
            	                <div class="modal-footer">
            	                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            	                    <button type="submit" name="hapus_pengguna" class="btn btn-danger">Hapus</button>
            	                </div>
            	            </form>
						</div>
					</div>
				</div>
				<!-- End Modal Delete Pengguna -->
			</div>
		</main>
		<!-- End Main -->
	</section>
	<!-- End Content -->

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/admin.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init();
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
        const organisasi_id = $(this).data('organisasi');
        
        $('#edit_id_user').val(id); 
        $('#edit_nim').val(nim);
        $('#edit_nama_lengkap').val(nama);
        $('#edit_email').val(email);
        $('#edit_role').val(role); // Pilih opsi Peran
        $('#edit_organisasi_id').val(organisasi_id); // Pilih opsi Organisasi
        
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

    // Inisialisasi DataTables
    $('#example').DataTable();
});
</script>
</body>
</html>