<?php
include '../php/koneksi.php';
// Memulai sesi
session_start();

// --- Set Zona Waktu ke WIB ---
date_default_timezone_set('Asia/Jakarta'); 
// --- End Set Zona Waktu ---

// Cek apakah user sudah login
if ($_SESSION['status'] != "login") {
    header("location:../login/login.php");
    exit;
}
// Cek apakah role user bukan 'ketua'
if ($_SESSION['role'] != "Ketua") {
    header("location:../login/login.php");
    exit;
}

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 

// --- Data Fetching untuk Riwayat Rapat (Rapat yang sudah lewat) ---
$id_pembuat_rapat = $_SESSION['id_user'] ?? null;
$list_riwayat = [];
if ($id_pembuat_rapat) {
    $sql_read = "SELECT 
                    r.*, 
                    o.nama_organisasi 
                 FROM agenda_rapat r
                 JOIN organisasi o ON r.id_organisasi = o.id_organisasi
                 WHERE r.id_pembuat = '$id_pembuat_rapat' 
                 -- Hanya ambil rapat yang sudah berlangsung/terlewat
                 AND CONCAT(r.tanggal_rapat, ' ', r.jam_rapat) <= NOW() 
                 ORDER BY r.tanggal_rapat DESC, r.jam_rapat DESC"; // Diurutkan dari yang paling baru
    $q_read = mysqli_query($koneksi, $sql_read);
    while ($r_read = mysqli_fetch_assoc($q_read)) {
        $list_riwayat[] = $r_read;
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
	<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
	<link rel="stylesheet" href="../assets/admin.css">
	<title>Rapatin </title>
</head>
<body>
	
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
	<section id="content">
		<nav class="atas">
			<i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar' ></i>
		</nav>
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
        			        <th>Organisasi</th>
        			        <th>Judul Rapat</th>
        			        <th class="text-center">Aksi</th>
        			    </tr>
        			</thead>
        			<tbody>
					    <?php $no = 1; foreach ($list_riwayat as $rapat) : ?>
					    <tr>
					        <td class="text-center"><?php echo $no++; ?></td>
    				        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($rapat['tanggal_rapat']))); ?></td>
    				        <td><?php echo htmlspecialchars(date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB'); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['nama_organisasi']); ?></td>
    				        <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
					        <td class="text-center">
								<button type="button" class="btn btn-warning aksi view-rapat-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-eye"></i></button>
								<button type="button" class="btn btn-danger aksi" data-bs-toggle="modal" data-bs-target="#deletemodal" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-trash"></i></i></button>
								<button type="button" class="btn btn-success aksi print-rapat-detail-btn" title="Download Detail Rapat PDF" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-arrow-down"></i></button>
							</td>
					    </tr>
					    <?php endforeach; ?>
        			</tbody>
    			</table>
			
			<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="viewModalLabel">Detail Riwayat Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<table class="table table-bordered table-striped" id="view_rapat_modal">
			                    <tbody>
			                        <tr><th style="width: 30%;">Tanggal Rapat</th><td id="view_tanggal"></td></tr>
			                        <tr><th>Jam Rapat</th><td id="view_jam"></td></tr>
			                        <tr><th>Judul Rapat</th><td id="view_judul"></td></tr>
			                        <tr><th>Ruang Rapat</th><td id="view_ruangan"></td></tr>
			                        <tr><th>Organisasi</th><td id="view_organisasi"></td></tr>
			                        <tr><th>Keterangan</th><td id="view_keterangan"></td></tr>
			                        <tr><th>File Notulen</th><td id="view_notulen_file"></td></tr>
			                        <tr><th>Peserta Rapat</th><td id="view_peserta"></td></tr>
			                    </tbody>
			                </table>
						</div>
				    </div>
				</div>
			</div>
			<div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="deletemodalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="deletemodalLabel">Hapus Riwayat Rapat</h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<form method="POST" action="../php/delete_rapat.php">
			                <div class="modal-body">
			                    <input type="hidden" name="hapus_id_rapat" id="hapus_id_rapat_modal"> 
			                    <p class="h5">Apakah anda yakin ingin menghapus riwayat rapat ini?</p>
			                </div>
			                <div class="modal-footer">
			                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
			                    <button type="submit" name="hapus_rapat" class="btn btn-danger">Hapus</button> 
			                </div>
			            </form>
				    </div>
				</div>
			</div>
			</div>
		</main>
		</section>
	<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/admin.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  AOS.init();
</script>
<script type="text/javascript">
$(document).ready(function() {
    $('#example').DataTable();
});

// Handler SweetAlert dari PHP Session
<?php if (isset($alert) && $alert['type'] == 'success') : ?>
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

// 1. Handler Tombol Hapus (Delete Modal)
$(document).on('click', 'button[data-bs-target="#deletemodal"]', function (event) {
    var id_rapat = $(this).data('id'); 
    $('#hapus_id_rapat_modal').val(id_rapat); 
});

// 2. Handler Tombol DOWNLOAD PDF DETAIL RAPAT
$(document).on('click', '.print-rapat-detail-btn', function (e) {
    e.preventDefault();
    var id_rapat = $(this).data('id');
    
    Swal.fire({
        title: "Memproses Download",
        text: "Mohon tunggu sebentar, file PDF sedang disiapkan...",
        icon: "info",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
            window.location.href = '../php/download_rapat_pdf.php?id=' + id_rapat;
        }
    });

    setTimeout(() => {
        Swal.close();
    }, 5000); 
});

// 3. Handler Tombol View (Memuat Data AJAX ke Modal)
$(document).on('click', '.view-rapat-btn', function (event) {
    var id_rapat = $(this).data('id');

    // Reset konten modal saat loading
    $('#view_tanggal').html('Memuat...');
    $('#view_jam').html('Memuat...');
    $('#view_judul').html('Memuat...');
    $('#view_ruangan').html('Memuat...');
    $('#view_organisasi').html('Memuat...');
    $('#view_keterangan').html('Memuat...');
    $('#view_peserta').html('Memuat...');
    $('#view_notulen_file').html('Memuat...');
    
    // Panggil AJAX untuk mengambil detail lengkap
    // Menggunakan endpoint dari agenda.php yang sudah ada logika AJAX-nya
    $.ajax({
        url: 'agenda.php?action=get_rapat_detail&id=' + id_rapat,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            
            if (data && !data.error) {
                // Konversi tanggal 
                var tanggalFormatted = data.tanggal_rapat ? new Date(data.tanggal_rapat + 'T00:00:00').toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'}) : '-';
                
                // FIX: Tampilkan jam tanpa detik + WIB (HH:MM WIB)
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
                    // Tampilkan peserta sebagai string dipisahkan koma
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
                $('#view_tanggal').html('ERROR: ' + (data.error || 'Data rapat tidak ditemukan.'));
                console.error("Respon Server Error:", data.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#view_tanggal').html('Kesalahan Server/Koneksi. Status: ' + jqXHR.status);
            console.error("AJAX GAGAL:", textStatus, errorThrown);
        }
    });
});
</script>
</body>
</html>