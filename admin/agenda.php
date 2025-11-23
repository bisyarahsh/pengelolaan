<?php
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
?>
	
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
	<title>Rapatin </title>
</head>
<body>
	
	<!-- SIDEBAR -->
	<section id="sidebar">
		<a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
		<a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
		<ul class="side-menu" data-aos="fade-right">
<<<<<<< HEAD:admin/agenda.php
			<li><a href="agenda.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
			<li><a href="organisasi.php"><i class="fa-solid fa-users icon"></i> Organisasi</a></li>
			<li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
			<li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Ganti Password</a></li>
			<li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Logout</a></li>
=======
			<li><a href="agenda.html" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="riwayat.html"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
			<li><a href="organisasi.html"><i class="fa-solid fa-users icon"></i> Organisasi</a></li>
			<li><a href="manage_user.html"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
			<li><a href="pengaturan.html"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
			<li><a href="../login/login.html"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
>>>>>>> 99d0ad547529d1ee75965a8c35b7dfb1b2cb8075:admin/agenda.html
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
					<h2>Agenda Rapat</h2>
					<button type="button" class="btn btn-primary tambah" data-bs-toggle="modal" data-bs-target="#exampleModal">Tambah</button>
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
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#viewModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-primary aksi" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-pen-to-square"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
							</td>
        			    </tr>
						<tr>
        			        <td class="text-center">2</td>
        			        <td>25-09-2025</td>
        			        <td>10:00 WIB</td>
        			        <td>HMTI Fair</td>
        			        <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
        			        <td class="text-center">
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#viewModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-primary aksi" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-pen-to-square"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
							</td>
        			    </tr>
						<tr>
        			        <td class="text-center">3</td>
        			        <td>25-09-2025</td>
        			        <td>10:00 WIB</td>
        			        <td>HMTI Fair</td>
        			        <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
        			        <td class="text-center">
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#viewModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-primary aksi" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-pen-to-square"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
							</td>
        			    </tr>
						<tr>
        			        <td class="text-center">4</td>
        			        <td>25-09-2025</td>
        			        <td>10:00 WIB</td>
        			        <td>HMTI Fair</td>
        			        <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
        			        <td class="text-center">
								<button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#viewModal"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-primary aksi" data-bs-toggle="modal" data-bs-target="#editModal"><i class="fa-solid fa-pen-to-square"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal"><i class="fa-solid fa-trash"></i></i></button>
							</td>
        			    </tr>
        			</tbody>
    			</table>
			
			<!-- Modal Tambah Rapat -->
			<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Tambah Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<form action="#">
								<div class="mb-3">
									<label class="mb-2" for="date">Tanggal Rapat</label>
									<input class="form-control" type="date" name="date" id="date">
								</div>
								<div class="mb-3">
									<label class="mb-2" for="time">Jam Rapat</label>
									<input class="form-control" type="time" name="time" id="time">
								</div>
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
										<option value="">DPM</option>
										<option value="">KUAS</option>
										<option value="">ENERGI</option>
										<option value="">HME</option>
										<option value="">HMM</option>
										<option value="">HMMB</option>
										<option value="">IMMPB</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="peserta">Peserta Rapat</label>
									<select class="form-select" id="multiple-select-field" data-placeholder="Pilih Peserta" multiple>
										<option>3312501064 - Adrian Septiaji</option>
										<option>3312501065 - Syarifah Bisyarah Shahab</option>
										<option>3312501066 - M. Fauzi Azhari</option>
										<option>3312501067 - Apri Catur Pramudiansyah</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="keterangan">Keterangan</label>
									<textarea class="form-control" name="keterangan" id="keterangan" placeholder="Masukkan Keterangan..."></textarea>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="file">Upload Notulen</label>
									<input class="form-control" type="file" id="myFile" name="filename">
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
							<button type="button" class="btn btn-primary" onclick="sweet()">Tambah</button>
						</div>
				    </div>
				</div>
			</div>

			<!-- Modal View Detail Agenda Rapat -->
			<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Edit Agenda Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<form action="#" aria-readonly="true">
								<div class="mb-3">
									<label class="mb-2" for="date">Tanggal Rapat</label>
									<input class="form-control" type="date" name="date" id="date" readonly>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="time">Jam Rapat</label>
									<input class="form-control" type="time" name="time" id="time" readonly>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="name">Judul Rapat</label>
									<input class="form-control" type="name" name="name" id="name" placeholder="HMTI Fair" readonly>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="name">Ruang Rapat</label>
									<input class="form-control" type="name" name="name" id="name" placeholder="GU 705" readonly>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="peserta">Organisasi</label>
									<select class="form-select" name="peserta" id="peserta">
										<option class="disabled">Pilih Organisasi</option>
										<option value="" selected>HMTI</option>
										<option value="">BEM</option>
										<option value="">BLUG</option>
										<option value="">REKAM</option>
										<option value="">DPM</option>
										<option value="">KUAS</option>
										<option value="">ENERGI</option>
										<option value="">HME</option>
										<option value="">HMM</option>
										<option value="">HMMB</option>
										<option value="">IMMPB</option>
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
									<textarea class="form-control" name="keterangan" id="keterangan" placeholder="Membahas Terkait Kepanitiaan HMTI Fair 2025" readonly></textarea>
								</div>
							</form>
						</div>
				    </div>
				</div>
			</div>
			<!-- End Modal View Detail Agenda Rapat -->

			<!-- Modal Edit Agenda Rapat -->
			<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Edit Agenda Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<form action="#">
								<div class="mb-3">
									<label class="mb-2" for="date">Tanggal Rapat</label>
									<input class="form-control" type="date" name="date" id="date">
								</div>
								<div class="mb-3">
									<label class="mb-2" for="time">Jam Rapat</label>
									<input class="form-control" type="time" name="time" id="time">
								</div>
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
										<option value="">DPM</option>
										<option value="">KUAS</option>
										<option value="">ENERGI</option>
										<option value="">HME</option>
										<option value="">HMM</option>
										<option value="">HMMB</option>
										<option value="">IMMPB</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="peserta">Peserta Rapat</label>
									<select class="form-select" id="edit-select-field" data-placeholder="Pilih Peserta" multiple>
										<option>3312501064 - Adrian Septiaji</option>
										<option>3312501065 - Syarifah Bisyarah Shahab</option>
										<option>3312501066 - M. Fauzi Azhari</option>
										<option>3312501067 - Apri Catur Pramudiansyah</option>
									</select>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="keterangan">Keterangan</label>
									<textarea class="form-control" name="keterangan" id="keterangan" placeholder="Masukkan Keterangan..."></textarea>
								</div>
								<div class="mb-3">
									<label class="mb-2" for="file">Upload Notulen</label>
									<input class="form-control" type="file" id="myFile" name="filename">
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
							<button type="button" class="btn btn-primary" onclick="edit()">Simpan Perubahan</button>
						</div>
				    </div>
				</div>
			</div>
			<!-- End Modal Edit Agenda Rapat -->

			<!-- Modal Delete Agenda Rapat -->
			<div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="deletemodalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Hapus Agenda Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<p class="h5">Apakah anda yakin ingin menghapus agenda rapat ini?</p>
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
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
$( '#multiple-select-field' ).select2( {
    theme: "bootstrap-5",
    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
    placeholder: $( this ).data( 'placeholder' ),
    closeOnSelect: false,
} );
$( '#edit-select-field' ).select2( {
    theme: "bootstrap-5",
    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
    placeholder: $( this ).data( 'placeholder' ),
    closeOnSelect: false,
} );
function sweet(){
	Swal.fire({
  title: "Selamat!",
  text: "Rapat Berhasil ditambahkan!",
  icon: "success"
	}).then((result) => {
            $('#exampleModal').modal('hide'); 
        });
}
function hapus(){
	Swal.fire({
  title: "Selamat!",
  text: "Rapat Berhasil dihapus!",
  icon: "success"
	}).then((result) => {
            $('#deletemodal').modal('hide'); 
        });
}
function edit(){
	Swal.fire({
  title: "Selamat!",
  text: "Perubahan Berhasil dilakukan!",
  icon: "success"
	}).then((result) => {
            $('#editModal').modal('hide'); 
        });
}
</script>
</body>
</html>