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
    
	<!-- Sidebar -->
    <section id="sidebar">
        <a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
			<li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="unit.php" class="active"><i class="fa-solid fa-users icon"></i>Unit</a></li>
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
        <main id="unit">
            <div data-aos="fade-down" class="rapat bg-light">
                <div class="tableheader">
					<h2>Unit</h2>
                    <button type="button" class="btn btn-primary tambah" data-bs-toggle="modal" data-bs-target="#tambahModal">Tambah</button>
                </div>
                <table id="tabel-rapat" class="table table-striped ">
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
                            // Tentukan apakah tombol delete harus di-disable
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
								<div class="modal-header">
									<h5 class="modal-title" id="tambahModalLabel">Tambah Unit</h5>
                                </div>
								<form method="POST" action="../php/add_unit.php">
									<div class="modal-body">
										<div class="mb-3">
											<label class="mb-2" for="nama_unit">Nama Unit</label>
											<input class="form-control" type="text" name="nama_unit" id="nama_unit" placeholder="Masukkan Nama Unit..." required>
                                        </div>
                                    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
								<div class="modal-header">
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
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
										<button type="submit" name="edit_unit" class="btn btn-primary">Simpan</button>
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
									<h5 class="modal-title" id="deletemodalLabel">Hapus Unit</h5>
                                </div>
								<form method="POST" action="../php/delete_unit.php">
									<div class="modal-body">
										<input type="hidden" name="hapus_id_unit"id="hapus_id_unit">
                                    <p class="h5">Apakah anda yakin ingin menghapus unit <span id="hapus_nama"></span>?</p>
                                    </div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    	<button type="submit" name="hapus_unit" class="btn btn-danger">Hapus</button>
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
        $(document).ready(function() {
	        $('#tabel-rapat').DataTable({
	            "language": {
	                "emptyTable": "Tidak ada unit",
	                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ unit",
	                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 unit",
	                "infoFiltered": "(difilter dari total _MAX_ unit)",
	                "lengthMenu": "Tampilkan _MENU_ unit",
	                "search": "Cari:",
	                "zeroRecords": "Tidak ditemukan unit yang cocok"
	            }
	        });
        });

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

                $('#edit_id_unit').val(id);
                $('#edit_nama_unit').val(nama);
            });

            // Logika untuk mengisi data modal Hapus saat tombol Hapus diklik
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