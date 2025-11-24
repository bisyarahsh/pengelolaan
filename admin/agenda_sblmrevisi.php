<?php
// Memulai sesi
session_start();
include("../php/koneksi.php"); // Pastikan path benar

// --- Cek Sesi dan Akses ---
if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

// Gunakan strtolower untuk mengatasi inkonsistensi role dari database
$current_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : '';

if ($current_role != "ketua") {
    header("location:../login/login.php?error=noaccess");
    exit;
}

$id_pembuat_rapat = $_SESSION['id_user'];
$list_rapat = [];
$sql_read = "SELECT 
                r.*, 
                o.nama_organisasi 
             FROM agenda_rapat r
             JOIN organisasi o ON r.id_organisasi = o.id_organisasi
             WHERE r.id_pembuat = '$id_pembuat_rapat' 
             ORDER BY r.tanggal_rapat DESC, r.jam_rapat DESC";
$q_read = mysqli_query($koneksi, $sql_read);
while ($r_read = mysqli_fetch_assoc($q_read)) {
    $list_rapat[] = $r_read;
}

// Ambil list Organisasi dan Peserta untuk dropdown modal
$list_organisasi = [];
$q_organisasi = mysqli_query($koneksi, "SELECT * FROM organisasi ORDER BY nama_organisasi");
while ($r_org = mysqli_fetch_assoc($q_organisasi)) {
    $list_organisasi[] = $r_org;
}

// Ambil list semua Peserta (Role 'Peserta')
$list_peserta = [];
$q_peserta = mysqli_query($koneksi, "SELECT id_user, nim, nama_lengkap FROM users WHERE role = 'Peserta' ORDER BY nama_lengkap");
while ($r_peserta = mysqli_fetch_assoc($q_peserta)) {
    $list_peserta[] = $r_peserta;
}
if (isset($_POST['tambah_rapat'])) {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['date']);
    $jam = mysqli_real_escape_string($koneksi, $_POST['time']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $ruangan = mysqli_real_escape_string($koneksi, $_POST['ruangan']);
    $id_organisasi = mysqli_real_escape_string($koneksi, $_POST['organisasi']);
    $peserta_arr = $_POST['peserta_rapat'] ?? []; // Array ID Peserta
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $notulen_file = '';

    // File Upload
    if (isset($_FILES['filename']) && $_FILES['filename']['error'] == 0) {
        $target_dir = "../notulen_files/"; // Pastikan folder ini ada
        $file_name = basename($_FILES["filename"]["name"]);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = "notulen_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES["filename"]["tmp_name"], $target_file)) {
            $notulen_file = $new_file_name;
        } else {
            // Handle error upload
        }
    }

    $sql_insert = "INSERT INTO agenda_rapat (tanggal_rapat, jam_rapat, judul_rapat, ruang_rapat, keterangan, notulen_file, id_organisasi, id_pembuat) 
                   VALUES ('$tanggal', '$jam', '$judul', '$ruangan', '$keterangan', '$notulen_file', '$id_organisasi', '$id_pembuat_rapat')";

    if (mysqli_query($koneksi, $sql_insert)) {
        $last_id_rapat = mysqli_insert_id($koneksi);

        // Insert Peserta Rapat
        foreach ($peserta_arr as $id_user_peserta) {
            $id_user_peserta = mysqli_real_escape_string($koneksi, $id_user_peserta);
            $sql_peserta = "INSERT INTO peserta_rapat (id_rapat, id_user) VALUES ('$last_id_rapat', '$id_user_peserta')";
            mysqli_query($koneksi, $sql_peserta);
        }

        // Redirect with success message
        header("location:agenda.php?status=sukses_tambah");
        exit;
    } else {
        // Handle error
        header("location:agenda.php?status=gagal_tambah&error=" . urlencode(mysqli_error($koneksi)));
        exit;
    }
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
			<li><a href="agenda.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
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
    				    <?php $no = 1; foreach ($list_rapat as $rapat) : ?>
    				    <tr>
    				        <td class="text-center"><?php echo $no++; ?></td>
    				        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($rapat['tanggal_rapat']))); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['jam_rapat']); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['keterangan']); ?></td>
    				        <td class="text-center">
    				            <button type="button" class="btn btn-warning aksi" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-eye"></i></button>
    				            <button type="button" class="btn btn-primary aksi" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-pen-to-square"></i></button>
    				            <button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-trash"></i></i></button>
    				        </td>
    				    </tr>
    				    <?php endforeach; ?>
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