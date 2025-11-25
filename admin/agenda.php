<?php
// Memulai sesi
session_start();

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

include("../php/koneksi.php");

// --- Set Zona Waktu ke WIB ---
date_default_timezone_set('Asia/Jakarta'); 
// --- End Set Zona Waktu ---

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

// --- Tambahkan Fungsi Ambil Data Rapat berdasarkan ID (AJAX Helper) ---
function get_rapat_detail($koneksi, $id_rapat) {
    $id_rapat = mysqli_real_escape_string($koneksi, $id_rapat);
    $sql = "SELECT 
                r.*, 
                o.nama_organisasi 
            FROM agenda_rapat r
            JOIN organisasi o ON r.id_organisasi = o.id_organisasi
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
$sql_read = "SELECT 
                r.*, 
                o.nama_organisasi 
             FROM agenda_rapat r
             JOIN organisasi o ON r.id_organisasi = o.id_organisasi
             WHERE r.id_pembuat = '$id_pembuat_rapat' 
             -- MODIFIKASI: Hanya ambil rapat yang belum berlangsung
             AND CONCAT(r.tanggal_rapat, ' ', r.jam_rapat) > NOW() 
             ORDER BY r.tanggal_rapat ASC, r.jam_rapat ASC"; // Diurutkan dari yang paling dekat
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
		<a href="../landing/index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
		<a href="../landing/index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
		<ul class="side-menu" data-aos="fade-right">
			<li><a href="agenda.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
			<li><a href="organisasi.php"><i class="fa-solid fa-users icon"></i> Organisasi</a></li>
			<li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
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
					<h2>Agenda Rapat</h2>
					<button type="button" class="btn btn-primary tambah" data-bs-toggle="modal" data-bs-target="#exampleModal">Tambah</button>
				</div>
				<table id="example" class="table table-striped">
        			<thead>
        			    <tr>
        			        <th class="text-center">No</th>
        			        <th>Tanggal Rapat</th>
        			        <th>Jam Rapat</th>
        			        <th>Organisasi</th>
        			        <th>Judul Rapat</th>
        			        <th class="text-center">Aksi</th>
        			    </tr>
        			</thead>
        			<tbody>
    				    <?php $no = 1; foreach ($list_rapat as $rapat) : ?>
    				    <tr>
    				        <td class="text-center"><?php echo $no++; ?></td>
    				        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($rapat['tanggal_rapat']))); ?></td>
    				        <td><?php echo htmlspecialchars(date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB'); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['nama_organisasi']); ?></td>
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
								data-organisasiid="<?php echo $rapat['id_organisasi']; ?>"
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
			<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">Tambah Agenda Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<form action="../php/add_rapat.php" method="POST" enctype="multipart/form-data">
								<div class="mb-3">
        					        <label class="mb-2" for="date">Tanggal Rapat</label>
        					        <input class="form-control" type="date" name="date" id="date" required>
        					    </div>
        					    <div class="mb-3">
        					        <label class="mb-2" for="time">Jam Rapat</label>
        					        <input class="form-control" type="time" name="time" id="time" required>
        					    </div>
        					    <div class="mb-3">
        					        <label class="mb-2" for="judul">Judul Rapat</label>
        					        <input class="form-control" type="text" name="judul" id="judul" placeholder="Masukkan Judul Rapat..." required>
        					    </div>
        					    <div class="mb-3">
        					        <label class="mb-2" for="ruangan">Ruang Rapat</label>
        					        <input class="form-control" type="text" name="ruangan" id="ruangan" placeholder="Masukkan Ruang Rapat...">
        					    </div>
        					    <div class="mb-3">
        					        <label class="mb-2" for="organisasi">Organisasi</label>
        					        <select class="form-select" name="organisasi" id="organisasi" required>
        					            <option value="" disabled selected>Pilih Organisasi</option>
        					            <?php foreach ($list_organisasi as $org) : ?>
        					                <option value="<?php echo $org['id_organisasi']; ?>"><?php echo htmlspecialchars($org['nama_organisasi']); ?></option>
        					            <?php endforeach; ?>
        					        </select>
        					    </div>
        					    <div class="mb-3">
        					        <label class="mb-2" for="multiple-select-field">Peserta Rapat (Bisa pilih lebih dari 1)</label>
        					        <select class="form-select" id="multiple-select-field" name="peserta_rapat[]" data-placeholder="Pilih Peserta" multiple>
        					            <?php foreach ($list_peserta as $peserta) : ?>
        					                <option value="<?php echo $peserta['id_user']; ?>"><?php echo htmlspecialchars($peserta['nim'] . ' - ' . $peserta['nama_lengkap']); ?></option>
        					            <?php endforeach; ?>
        					        </select>
        					    </div>
        					    <div class="mb-3">
        					        <label class="mb-2" for="keterangan">Keterangan</label>
        					        <textarea class="form-control" name="keterangan" id="keterangan" placeholder="Masukkan Keterangan..."></textarea>
        					    </div>
        					    <div class="mb-3">
        					        <label class="mb-2" for="file">Upload Notulen</label>
        					        <input class="form-control" type="file" id="myFile" name="filename" accept=".pdf,.doc,.docx,.jpg">
        					    </div>
        					    <div class="modal-footer">
        					        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        					        <button type="submit" name="tambah_rapat" class="btn btn-primary">Tambah Rapat</button> 
								</div>
        					</form>
						</div>
				    </div>
				</div>
			</div>

			<!-- Modal View Detail Agenda Rapat -->
			<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
			    <div class="modal-dialog">
			        <div class="modal-content">
			            <div class="modal-header">
			                <h5 class="modal-title" id="viewModalLabel">Detail Agenda Rapat</h5>
			                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			            </div>
			            <div class="modal-body">
			                <table class="table table-bordered table-striped" id="view_rapat_modal">
			                    <tbody>
			                        <tr>
			                            <th style="width: 30%;">Tanggal Rapat</th>
			                            <td id="view_tanggal"></td>
			                        </tr>
			                        <tr>
			                            <th>Jam Rapat</th>
			                            <td id="view_jam"></td>
			                        </tr>
			                        <tr>
			                            <th>Judul Rapat</th>
			                            <td id="view_judul"></td>
			                        </tr>
			                        <tr>
			                            <th>Ruang Rapat</th>
			                            <td id="view_ruangan"></td>
			                        </tr>
			                        <tr>
			                            <th>Organisasi</th>
			                            <td id="view_organisasi"></td>
			                        </tr>
			                        <tr>
			                            <th>Keterangan</th>
			                            <td id="view_keterangan"></td>
			                        </tr>
			                        <tr>
			                            <th>File Notulen</th>
			                            <td id="view_notulen_file"></td>
			                        </tr>
			                        <tr>
			                            <th>Peserta Rapat</th>
			                            <td id="view_peserta"></td>
			                        </tr>
			                    </tbody>
			                </table>
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
			                <h5 class="modal-title" id="editModalLabel">Edit Agenda Rapat</h5>
			                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			            </div>
			            <div class="modal-body">
			                <form action="../php/edit_rapat.php" method="POST" enctype="multipart/form-data" id="form-edit-rapat">
			                    <input type="hidden" name="edit_id_rapat" id="edit_rapat_id_unik">
			                    <input type="hidden" name="notulen_file_lama" id="notulen_file_lama">
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit_date">Tanggal Rapat</label>
			                        <input class="form-control" type="date" name="edit_date" id="edit_date" required>
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit_time">Jam Rapat</label>
			                        <input class="form-control" type="time" name="edit_time" id="edit_time" required>
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit_judul">Judul Rapat</label>
			                        <input class="form-control" type="text" name="edit_judul" id="edit_judul" placeholder="Masukkan Judul Rapat..." required>
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit_ruangan">Ruang Rapat</label>
			                        <input class="form-control" type="text" name="edit_ruangan" id="edit_ruangan" placeholder="Masukkan Ruang Rapat...">
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit_organisasi">Organisasi</label>
			                        <select class="form-select" name="edit_organisasi" id="edit_organisasi" required>
			                            <option value="" disabled selected>Pilih Organisasi</option>
			                            <?php foreach ($list_organisasi as $org) : ?>
			                                <option value="<?php echo $org['id_organisasi']; ?>"><?php echo htmlspecialchars($org['nama_organisasi']); ?></option>
			                            <?php endforeach; ?>
			                        </select>
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit-multiple-select-field">Peserta Rapat (Bisa pilih lebih dari 1)</label>
			                        <select class="form-select select2-edit" id="edit-multiple-select-field" name="edit_peserta_rapat[]" data-placeholder="Pilih Peserta" multiple required>
			                            <?php foreach ($list_peserta as $peserta) : ?>
			                                <option value="<?php echo $peserta['id_user']; ?>"><?php echo htmlspecialchars($peserta['nim'] . ' - ' . $peserta['nama_lengkap']); ?></option>
			                            <?php endforeach; ?>
			                        </select>
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit_keterangan">Keterangan</label>
			                        <textarea class="form-control" name="edit_keterangan" id="edit_keterangan" placeholder="Masukkan Keterangan..."></textarea>
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2">File Notulen Saat Ini:</label>
			                        <div id="current_file_info"></div>
			                    </div>
			                    <div class="mb-3">
			                        <label class="mb-2" for="edit_file">Upload Notulen Baru (Kosongkan jika tidak diubah)</label>
			                        <input class="form-control" type="file" id="edit_file" name="edit_filename" accept=".pdf,.doc,.docx,.jpg">
			                    </div>
										
			                    <div class="modal-footer">
			                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
			                        <button type="submit" name="edit_rapat" class="btn btn-primary">Simpan Perubahan</button>
			                    </div>
			                </form>
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
			            <form method="POST" action="../php/delete_rapat.php">
			                <div class="modal-body">
			                    <input type="hidden" name="hapus_id_rapat" id="hapus_id_rapat_modal"> 
			                    <p class="h5">Apakah anda yakin ingin menghapus agenda rapat ini?</p>
			                </div>
			                <div class="modal-footer">
			                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
			                    <button type="submit" name="hapus_rapat" class="btn btn-danger">Hapus</button> 
			                </div>
			            </form>
			        </div>
			    </div>
			</div>
			<!-- End Modal Delete Riwayat Rapat -->

			<!-- Modal Notifikasi -->
			<div class="modal fade" id="notifmodal" tabindex="-1" aria-labelledby="notifmodalLabel" aria-hidden="true">
			    <div class="modal-dialog">
			        <div class="modal-content">
			            <div class="modal-header">
			                <h5 class="modal-title" id="notifmodalLabel">Kirim Notifikasi Rapat</h5>
			                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			            </div>
			            <form method="POST" action="../php/send_notification.php"> 
			                <div class="modal-body">
			                    <input type="hidden" name="notif_id_rapat" id="notif_id_rapat"> 
			                    <p class="h5">Anda akan mengirimkan notifikasi rapat ini melalui email kepada semua peserta yang terdaftar.</p>
			                    <p>Lanjutkan?</p>
			                </div>
			                <div class="modal-footer">
			                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
			                    <button type="submit" name="send_notification" class="btn btn-success">Kirim Notifikasi</button> 
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

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="../assets/admin.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
  AOS.init();
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
$( document ).ready(function() {
    
    // Inisialisasi Select2 untuk modal Tambah
    $( '#multiple-select-field' ).select2( {
        theme: "bootstrap-5",
        width: '100%',
        placeholder: 'Pilih Peserta',
        dropdownParent: $('#exampleModal'), 
        closeOnSelect: false,
    } );
    
    // Inisialisasi Select2 untuk modal Edit
    $( '.select2-edit' ).select2( {
        theme: "bootstrap-5",
        width: '100%',
        placeholder: 'Pilih Peserta',
        dropdownParent: $('#editModal'), 
        closeOnSelect: false,
    } );
});

// Handler SweetAlert dari PHP Session
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

// Modal Delete Handler menggunakan delegasi
$(document).on('click', 'button[data-bs-target="#deletemodal"]', function (event) {
    var id_rapat = $(this).data('id'); 
    $('#hapus_id_rapat_modal').val(id_rapat); 
    // Biarkan Bootstrap menampilkan modal
});

// Modal Edit Handler menggunakan delegasi
$(document).on('click', 'button[data-bs-target="#editModal"]', function (event) {
    var button = $(this);
    
    // 1. AMBIL SEMUA DATA DARI DATA ATTRIBUTE TOMBOL (Menggunakan var)
    var id_rapat_terpilih = $(this).data('id'); 
    var tanggal = button.data('tanggal');
    var jam = button.data('jam');
    var judul = button.data('judul');
    var ruangan = button.data('ruangan');
    var keterangan = button.data('keterangan');
    var organisasi_id = button.data('organisasiid');
    var notulen_file = button.data('notulen');
    var peserta_data = button.data('peserta'); // Ini akan berupa array jika berhasil di-parse
    
    // 2. RESET/BERSIHKAN FIELD UTAMA
    
    // Bersihkan Select2 (penting)
    $('#edit-multiple-select-field').val(null).trigger('change');
    $('#current_file_info').html(''); // Bersihkan info file lama

    // 3. ISI DATA KE INPUT MODAL
    
    // ID Rapat (Penting!)
    $('#edit_rapat_id_unik').val(id_rapat_terpilih); 
    
    // Data Dasar
    $('#edit_date').val(tanggal);
    $('#edit_time').val(jam);
    $('#edit_judul').val(judul);
    $('#edit_ruangan').val(ruangan);
    $('#edit_keterangan').val(keterangan);
    
    // Select Organisasi
    $('#edit_organisasi').val(organisasi_id).trigger('change');

    // Peserta (Select2)
    if (peserta_data && peserta_data.length > 0) {
        $('#edit-multiple-select-field').val(peserta_data).trigger('change');
    }

    // File Notulen
    $('#notulen_file_lama').val(notulen_file);
    
    var notulenHtml = 'Tidak ada file notulen saat ini. ';
    if (notulen_file) {
        var fileUrl = '../notulen_files/' + notulen_file;
        notulenHtml = 'File: <strong>' + notulen_file + '</strong>. (<a href="' + fileUrl + '" target="_blank">Lihat</a>) <br>Centang untuk menghapus: <input type="checkbox" name="hapus_file_lama" value="yes">';
    }
    $('#current_file_info').html(notulenHtml);
    
    // Catatan: Karena Anda menggunakan data-bs-toggle="modal", modal akan otomatis muncul.
});

// Modal View Handler menggunakan delegasi dan AJAX
$(document).on('click', '.view-rapat-btn', function (event) { // <--- Targeting Class Baru
    var id_rapat = $(this).data('id');
	$('#view_rapat_modal').val(id_rapat);
    
    $('#view_tanggal').html('Memuat...');
    $('#view_jam').html('Memuat...');
    $('#view_judul').html('Memuat...');
    $('#view_ruangan').html('Memuat...');
    $('#view_organisasi').html('Memuat...');
    $('#view_keterangan').html('Memuat...');
    $('#view_peserta').html('Memuat...');
    $('#view_notulen_file').html('Memuat...');
    
    // 2. Panggil AJAX untuk mengambil detail lengkap
    $.ajax({
        // URL menggunakan ID Rapat yang sudah dipastikan ada
        url: '../php/ajax_detail.php?id=' + id_rapat,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            // ... (Kode mengisi data ke #view_tanggal, #view_judul, dst. tetap sama) ...

            if (data && !data.error) {
                // Konversi tanggal (contoh: 24-November-2025)
                var tanggalFormatted = data.tanggal_rapat ? new Date(data.tanggal_rapat + 'T00:00:00').toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'}) : '-';
				var jamFormatted = data.jam_rapat ? data.jam_rapat.substring(0, 5) + ' WIB' : '-';
                
                // Isi data ke dalam sel tabel (<td>)
                $('#view_tanggal').html(tanggalFormatted);
                $('#view_jam').html(jamFormatted);
                $('#view_judul').html(data.judul_rapat || '-');
                $('#view_ruangan').html(data.ruang_rapat || '-'); 
                $('#view_organisasi').html(data.nama_organisasi || '-'); 
                $('#view_keterangan').html(data.keterangan || '-');

                // Tampilkan daftar peserta
                var pesertaHtml = 'Tidak ada peserta.';
                if (data.peserta_details && data.peserta_details.length > 0) {
                    pesertaHtml = data.peserta_details.join(', ');
                }
                $('#view_peserta').html(pesertaHtml);

                // Tampilkan file notulen
                var fileHtml = 'Tidak ada file notulen.';
                if (data.notulen_file) {
                    var fileUrl = '../notulen_files/' + data.notulen_file;
                    fileHtml = '<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-info"><i class="fa-solid fa-file-alt"></i> Lihat File Notulen</a>';
                }
                $('#view_notulen_file').html(fileHtml);

            } else {
                 // Menangani error dari PHP (misalnya: Data rapat tidak ditemukan untuk ID ini.)
                $('#view_tanggal').html('ERROR: ' + (data.error || 'Data rapat tidak ditemukan.'));
                console.error("Respon Server Error:", data.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Jika koneksi AJAX gagal
            $('#view_tanggal').html('Kesalahan Server/Koneksi. Status: ' + jqXHR.status);
            console.error("AJAX GAGAL:", textStatus, errorThrown);
        }
    });
});

$(document).on('click', 'button[data-bs-target="#notifmodal"]', function (event) {
    var id_rapat = $(this).data('id'); 
    $('#notif_id_rapat').val(id_rapat); 
    // Biarkan Bootstrap menampilkan modal
});
</script>
</body>
</html>