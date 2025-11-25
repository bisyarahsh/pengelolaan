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
// Cek apakah role user bukan 'Ketua'
if ($_SESSION['role'] != "Ketua") {
    // Jika bukan ketua, tolak akses dan arahkan kembali
    header("location:../login/login.php");
    exit;
}

// Ambil data organisasi
$sql = "SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi ASC";
$result = $koneksi->query($sql);
$organisasi = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $organisasi[] = $row;
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
    <title>Rapatin</title>
</head>
<body>
    
	<!-- Sidebar -->
    <section id="sidebar">
        <a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
			<li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="organisasi.php" class="active"><i class="fa-solid fa-users icon"></i>Organisasi</a></li>
            <li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
            <li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Logout</a></li>
        </ul>
    </section>
	<!-- End Sidebar -->
	
	<!-- Content -->
    <section id="content">
		<!-- Toggle Sidebar -->
        <nav class="atas">
            <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar'></i>
        </nav>
		<!-- End Toggle Sidebar -->

		<!-- Table -->
        <main id="organisasi">
            <div data-aos="fade-down" class="rapat bg-light">
                <div class="tableheader">
					<h2>Organisasi</h2>
                    <button type="button" class="btn btn-primary tambah" data-bs-toggle="modal" data-bs-target="#tambahModal">Tambah</button>
                </div>
                <table id="example" class="table table-striped ">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Organisasi</th>
                            <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($organisasi as $org): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= htmlspecialchars($org['nama_organisasi']) ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary aksi edit-btn" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?= $org['id_organisasi'] ?>" data-nama="<?= htmlspecialchars($org['nama_organisasi']) ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger aksi delete-btn" data-bs-toggle="modal" data-bs-target="#deletemodal" data-id="<?= $org['id_organisasi'] ?>" data-nama="<?= htmlspecialchars($org['nama_organisasi']) ?>">
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
								<div class="modal-header">
									<h5 class="modal-title" id="tambahModalLabel">TambahOrganisasi</h5>
                                </div>
								<form method="POST" action="../php/add_organisasi.php">
									<div class="modal-body">
										<div class="mb-3">
											<label class="mb-2" for="nama_organisasi">Nama Organisasi</label>
											<input class="form-control" type="text" name="nama_organisasi" id="nama_organisasi" placeholder="Masukkan Nama Organisasi..." required>
                                        </div>
                                    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="tambah_organisasi" class="btn btn-primary">Tambah</button>
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
								<div class="modal-header">
									<h5 class="modal-title" id="editModalLabel">Edit Organisasi</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
								<form method="POST" action="../php/edit_organisasi.php">
									<div class="modal-body">
										<input type="hidden" name="edit_id_organisasi" id="edit_id_organisasi">
										<div class="mb-3">
											<label class="mb-2" for="edit_nama_organisasi">Nama Organisasi</label>
                                        	<input class="form-control" type="text" name="edit_nama_organisasi" id="edit_nama_organisasi"placeholder="Masukkan Nama Organisasi..." required>
                                        </div>
                                    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
										<button type="submit" name="edit_organisasi" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
					<!-- End Modal Edit -->
					
					<!-- Modal Delete -->
					<div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="deletemodalLabel" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="deletemodalLabel">Hapus Organisasi</h5>
                                </div>
								<form method="POST" action="../php/delete_organisasi.php">
									<div class="modal-body">
										<input type="hidden" name="hapus_id_organisasi"id="hapus_id_organisasi">
                                    <p class="h5">Apakah anda yakin ingin menghapus Organisasi <span id="hapus_nama"></span>?</p>
                                    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    	<button type="submit" name="hapus_organisasi" class="btn btn-danger">Hapus</button>
                                    </div>
                                </form>
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
    <script src="../assets/admin.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">

        // Tampilkan SweetAlert berdasarkan session alert dari PHP
        const alertData = <?= json_encode($alert); ?>;
		if (alertData && alertData.message) {
            Swal.fire({
                title: alertData.icon === 'success' ? "Selamat!" : "Perhatian!", // Menggunakan "Perhatian!" untuk warning
                text: alertData.message,
                icon: alertData.icon, // Membaca 'success' atau 'warning' dari PHP
                timer: 3000,
                showConfirmButton: false
            });
        }
        
        $(document).ready(function () {
            // Logika untuk mengisi data modal Edit saat tombol Edit diklik
            $('.edit-btn').on('click', function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');

                $('#edit_id_organisasi').val(id);
                $('#edit_nama_organisasi').val(nama);
            });

            // Logika untuk mengisi data modal Hapus saat tombol Hapus diklik
            $('.delete-btn').on('click', function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');

                $('#hapus_id_organisasi').val(id);
                $('#hapus_nama').text(nama);
            });
        });
    </script>
</body>

</html>