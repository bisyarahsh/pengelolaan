<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">
	<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
	<link rel="stylesheet" href="../assets/admin.css">
	<title>Rapatin </title>
</head>
<body>
	
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
		<a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
		<ul class="side-menu" data-aos="fade-right">
			<li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="riwayat.php" class="active"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
			<li><a href="organisasi.php"><i class="fa-solid fa-users icon"></i> Organisasi</a></li>
			<li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
			<li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Ganti Password</a></li>
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
					<h2>Riwayat Rapat</h2>
				</div>
				<table id="example" class="table table-striped">
        			<thead>
        			    <tr>
        			        <th class="text-center">No</th>
        			        <th>Tanggal Rapat</th>
        			        <th>Jam Rapat</th>
        			        <th>Judul Rapat</th>
        			        <th>Keterangan</th>
        			        <th class="text-center">Aksi</th>
        			    </tr>
        			</thead>
        			<tbody>
        			    <tr>
        			        <td class="text-center">1</td>
        			        <td>25-09-2025</td>
        			        <td>10:00 WIB</td>
        			        <td>HMTI Fair</td>
        			        <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
        			        <td class="text-center">
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
								<button type="button" class="btn btn-success aksi"><i class="fa-solid fa-arrow-down"></i></button>
							</td>
        			    </tr>
						<tr>
        			        <td class="text-center">2</td>
        			        <td>25-09-2025</td>
        			        <td>10:00 WIB</td>
        			        <td>HMTI Fair</td>
        			        <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
        			        <td class="text-center">
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
								<button type="button" class="btn btn-success aksi"><i class="fa-solid fa-arrow-down"></i></button>
							</td>
        			    </tr>
						<tr>
        			        <td class="text-center">3</td>
        			        <td>25-09-2025</td>
        			        <td>10:00 WIB</td>
        			        <td>HMTI Fair</td>
        			        <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
        			        <td class="text-center">
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
								<button type="button" class="btn btn-success aksi"><i class="fa-solid fa-arrow-down"></i></button>
							</td>
        			    </tr>
						<tr>
        			        <td class="text-center">4</td>
        			        <td>25-09-2025</td>
        			        <td>10:00 WIB</td>
        			        <td>HMTI Fair</td>
        			        <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
        			        <td class="text-center">
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
								<button type="button" class="btn btn-success aksi"><i class="fa-solid fa-arrow-down"></i></button>
							</td>
        			    </tr>
        			</tbody>
    			</table>
			
			<!-- Modal Detail Riwayat Rapat -->
			<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Detail Riwayat Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<form action="#">
								<div class="mb-3">
									<label class="mb-2" for="name">Judul Rapat</label>
									<input class="form-control" type="name" name="name" id="name" placeholder="Masukkan Judul Rapat...">
								</div>
								<div class="mb-3">
									<label class="mb-2" for="name">Ruang Rapat</label>
									<input class="form-control" type="name" name="name" id="name" placeholder="Masukkan Ruang Rapat...">
								</div>
								<div class="mb-3">
									<label class="mb-2" for="peserta">Organisasi</label>
									<select class="form-select" name="peserta" id="peserta">
										<option class="disabled" value="">Pilih Organisasi</option>
										<option value="">HMTI</option>
										<option value="">BEM</option>
										<option value="">BLUG</option>
										<option value="">REKAM</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="peserta">Peserta Rapat</label>
									<select class="form-select" name="peserta" id="peserta">
										<option class="disabled" value="">Pilih Peserta Rapat</option>
										<option value="">3312501064 - Adrian Septiaji</option>
										<option value="">3312501065 - Syarifah Bisyarah Shahab</option>
										<option value="">3312501066 - M. Fauzi Azhari</option>
										<option value="">3312501067 - Apri Catur Pramudiansyah</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="keterangan">Keterangan</label>
									<textarea class="form-control" name="keterangan" id="keterangan" placeholder="Masukkan Keterangan..."></textarea>
								</div>
							</form>
						</div>
				    </div>
				</div>
			</div>
			<!-- End Modal Edit Riwayat Rapat -->

			<!-- Modal Delete Riwayat Rapat -->
			<div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="deletemodalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Hapus Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<p class="h5">Apakah anda yakin ingin menghapus rapat ini?</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
							<button type="button" class="btn btn-danger" onclick="hapus()">Hapus</button>
						</div>
				    </div>
				</div>
			</div>
			<!-- End Modal Delete Riwayat Rapat -->
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
function hapus(){
	Swal.fire({
  title: "Selamat!",
  text: "Rapat Berhasil dihapus!",
  icon: "success"
	}).then((result) => {
            $('#deletemodal').modal('hide'); 
        });
}
</script>
</body>
</html>