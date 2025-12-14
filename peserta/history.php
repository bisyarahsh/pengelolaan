<?php
include '../php/koneksi.php';
// Memulai sesi
session_start();

// --- 1. Set Zona Waktu dan Cek Akses ---
date_default_timezone_set('Asia/Jakarta'); 

if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']); // Ambil ID user yang sedang login

// Cek Role
if (strtolower($_SESSION['role']) != "peserta") { 
    header("location:../login/login.php?error=noaccess");
    exit;
}

function tgl_indo($tanggal){
	$bulan = array (
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);
 
	return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

// --- 2. AJAX Detail Handler (untuk memuat data ke modal) ---
if (isset($_GET['action']) && $_GET['action'] == 'get_rapat_detail' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id_rapat = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Query untuk detail rapat, termasuk nama unit
    $sql_detail = "
        SELECT 
            ar.*,
            o.nama_unit
        FROM 
            agenda_rapat ar
        LEFT JOIN
            unit o ON ar.unit_id = o.id_unit
        WHERE 
            ar.id_rapat = '$id_rapat'";
    
    $q_detail = mysqli_query($koneksi, $sql_detail);
    $data_rapat = mysqli_fetch_assoc($q_detail);
    
    if ($data_rapat) {
        // Ambil daftar peserta untuk rapat ini
        $sql_peserta = "
            SELECT u.nama_lengkap 
            FROM peserta_rapat pr
            JOIN user u ON pr.id_user = u.id_user
            WHERE pr.id_rapat = '$id_rapat'";
        $q_peserta = mysqli_query($koneksi, $sql_peserta);
        $peserta_list = [];
        while ($r_peserta = mysqli_fetch_assoc($q_peserta)) {
            $peserta_list[] = $r_peserta['nama_lengkap'];
        }

        $data_rapat['peserta_details'] = $peserta_list;
        echo json_encode($data_rapat);
    } else {
        echo json_encode(['error' => 'Rapat tidak ditemukan.']);
    }
    // Tidak menutup koneksi di sini agar bisa digunakan untuk query utama di bawah (jika belum keluar)
    // mysqli_close($koneksi); // Tidak perlu close karena akan di-exit
    exit;
}
// --- End AJAX Detail Handler ---


// --- 3. Data Fetching for Main Table (Rapat yang sudah lewat) ---
$now_datetime = date('Y-m-d H:i:s'); 
$list_riwayat = [];

// Query untuk mengambil riwayat rapat yang diikuti oleh $id_user dan sudah terlewat
$sql_read = "
    SELECT 
        ar.id_rapat,
        ar.tanggal_rapat,
        ar.jam_rapat,
        ar.judul_rapat,
        ar.keterangan,
        ar.notulen_file,
        ar.ruang_rapat,
        o.nama_unit
    FROM
        agenda_rapat ar
    JOIN
        peserta_rapat pr ON ar.id_rapat = pr.id_rapat
    LEFT JOIN 
        unit o ON ar.id_unit = o.id_unit
    WHERE
        pr.id_user = '$id_user' 
        -- Filter: Gabungan tanggal dan jam rapat HARUS lebih kecil atau sama dengan waktu saat ini
        AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) <= '$now_datetime' 
    ORDER BY
        ar.tanggal_rapat DESC, ar.jam_rapat DESC;
";
$q_read = mysqli_query($koneksi, $sql_read);
while ($r_read = mysqli_fetch_assoc($q_read)) {
    $list_riwayat[] = $r_read;
}

// Menutup koneksi database setelah selesai mengambil semua data
// Note: Hanya jika tidak ada AJAX handler di atas yang ter-trigger
if (!isset($_GET['action'])) {
    // mysqli_close($koneksi); // Dibiarkan terbuka untuk koneksi yang ada di file 'koneksi.php' jika diperlukan
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css"
    />
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/responsive/2.3.4/css/responsive.bootstrap5.min.css"
    />
    <link rel="stylesheet" href="../assets/peserta.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <title>Rapatin</title>
  </head>
  <body>
    <!-- SIDEBAR -->
    <section id="sidebar">
      <a href="../index.html" data-aos="fade-down" class="logo ps-3"
        ><i class="ps-5"></i> Rapatin</a
      >
      <a
        href="../index.html"
        data-aos="fade-down"
        class="logo-mini fw-bold"
      >
        R</a
      >
      <ul class="side-menu" data-aos="fade-right">
        <li>
          <a href="dashboard.php"
            ><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a
          >
        </li>
        <li>
          <a href="history.php" class="active"
            ><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a
          >
        </li>
        <li>
          <a href="ganti_password.php"
            ><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a
          >
        </li>
        <li>
          <a href="logout.php"
            ><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a
          >
        </li>
      </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- Content -->
    <section id="content">
      <!-- Toggle Sidebar -->
      <nav class="atas">
        <i data-aos="fade-right" class="fa-solid fa-bars toggle-sidebar"></i>
      </nav>
      <!-- End Toggle Sidebar -->

      <!-- Main -->
      <main>
        <div data-aos="fade-down" class="rapat bg-light">
          <div class="tableheader">
            <h2>History Rapat</h2>
          </div>
          <table id="example" class="table table-striped">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal Rapat</th>
                <th>Jam Rapat</th>
                <th>unit</th>
                <th>Judul Rapat</th>
                <th>Ruangan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($list_riwayat as $rapat) : ?>
                <tr>
                  <td class="text-center"><?php echo $no++; ?></td>
                  <td><?php echo htmlspecialchars(tgl_indo($rapat['tanggal_rapat'])); ?></td>
                  <td><?php echo htmlspecialchars(date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB'); ?></td>
                  <td><?php echo htmlspecialchars($rapat['nama_unit']); ?></td>
                  <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
                  <td><?php echo htmlspecialchars($rapat['ruang_rapat']); ?></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-warning aksi view-rapat-btn" data-bs-toggle="modal" data-bs-target="#viewModal" data-id="<?php echo htmlspecialchars($rapat['id_rapat']); ?>" title="Lihat Detail Rapat"><i class="fa-solid fa-eye"></i></button>
                    <button type="button" class="btn btn-success aksi print-rapat-detail-btn" title="Download Detail Rapat PDF" data-id="<?php echo $rapat['id_rapat']; ?>"><i class="fa-solid fa-arrow-down"></i></button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

        <!-- Modal Detail Rapat -->
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
			                    <tr><th>unit</th><td id="view_unit"></td></tr>
			                    <tr><th>Keterangan</th><td id="view_keterangan"></td></tr>
			                    <tr><th>File Notulen</th><td id="view_notulen_file"></td></tr>
			                    <tr><th>Peserta Rapat</th><td id="view_peserta"></td></tr>
			                </tbody>
			            </table>
			    			</div>
			    	  </div>
			    	</div>
			    </div>
          <!-- End Modal Detail Rapat -->
        </div>
      </main>
      <!-- End Main -->
    </section>
    <!-- End Content -->

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/peserta.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).on('click', '.view-rapat-btn', function (event) { // <--- Targeting Class Baru
    var id_rapat = $(this).data('id');
	$('#view_rapat_modal').val(id_rapat);
    
    $('#view_tanggal').html('Memuat...');
    $('#view_jam').html('Memuat...');
    $('#view_judul').html('Memuat...');
    $('#view_ruangan').html('Memuat...');
    $('#view_unit').html('Memuat...');
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
                $('#view_unit').html(data.nama_unit || '-'); 
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
    </script>
  </body>
</html>
