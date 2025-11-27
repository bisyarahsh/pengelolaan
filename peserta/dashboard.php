<?php
include '../php/koneksi.php';
// Memulai sesi
session_start();

// --- 1. Set Zona Waktu dan Ambil ID User ---
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

// Definisikan waktu saat ini untuk filtering
$now_datetime = date('Y-m-d H:i:s'); 

// --- 2. Query untuk Mengambil Data Rapat yang BELUM SELESAI ---
$sql_agenda = "
    SELECT
        ar.tanggal_rapat,
        ar.jam_rapat,
        ar.judul_rapat,
        ar.keterangan,
        ar.ruang_rapat,
        o.nama_organisasi
    FROM
        agenda_rapat ar
    JOIN
        peserta_rapat pr ON ar.id_rapat = pr.id_rapat
    LEFT JOIN 
        organisasi o ON ar.id_organisasi = o.id_organisasi
    WHERE
        pr.id_user = '$id_user' 
        -- Filter: Gabungan tanggal dan jam rapat HARUS lebih besar atau sama dengan waktu saat ini
        AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) >= '$now_datetime' 
    ORDER BY
        ar.tanggal_rapat ASC, ar.jam_rapat ASC;
";
$query_agenda = mysqli_query($koneksi, $sql_agenda);

// Fungsi Status (Disimpan untuk konsistensi meskipun hasilnya seharusnya 'Menunggu')
function get_rapat_status($tanggal, $jam) {
    $datetime_rapat = date('Y-m-d H:i:s', strtotime("$tanggal $jam"));
    $now = date('Y-m-d H:i:s');
    
    // Karena kita sudah memfilter di kueri SQL, semua ini seharusnya "Menunggu"
    if ($datetime_rapat >= $now) {
        return ['text' => 'Menunggu', 'class' => 'bg-warning'];
    } 
    // Baris ini tidak akan terpanggil karena sudah difilter di SQL
    return ['text' => 'Selesai', 'class' => 'bg-success']; 
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/peserta.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <title>Dashboard | Peserta - Rapatin</title>
	  <link rel="shortcut icon" href="../assets/logo/logo.png">
  </head>
  <body>
    <section id="sidebar">
    <a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
    <a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
    <ul class="side-menu" data-aos="fade-right">
      <li><a href="dashboard.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
      <li><a href="history.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
      <li><a href="ganti__password.php"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
      <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
    </ul>
  </section>
  <section id="content">
      <nav class="atas">
        <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar' ></i>
      </nav>
      <main>
        <div data-aos="fade-down" class="rapat bg-light">
          <div class="tableheader">
            <h2>Agenda Rapat</h2>
          </div>
          <table id="example" class="table table-striped">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal Rapat</th>
                <th>Jam Rapat</th>
                <th>Organisasi</th>
                <th>Judul Rapat</th>
                <th>Ruangan</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php
            // --- 3. Loop untuk menampilkan data dari database ---
            $no = 1;
            if (mysqli_num_rows($query_agenda) > 0) {
                while ($rapat = mysqli_fetch_assoc($query_agenda)) {
                    $status = get_rapat_status($rapat['tanggal_rapat'], $rapat['jam_rapat']);
                    
                    // Format tanggal ke format Indonesia
                    $tanggal_indo = date('d-m-Y', strtotime($rapat['tanggal_rapat']));
                    $jam_wib = date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB';
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $tanggal_indo; ?></td>
                    <td><?php echo $jam_wib; ?></td>
                    <td><?php echo htmlspecialchars($rapat['nama_organisasi']); ?></td>
                    <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
                    <td><?php echo htmlspecialchars($rapat['ruang_rapat']); ?></td>
                    <td class="text-center">
                        <p class="<?php echo $status['class']; ?> rounded px-2 py-1 mb-0 mx-auto text-light">
                            <?php echo $status['text']; ?>
                        </p>
                    </td>
                </tr>
            <?php
                }
            }
            ?>
            </tbody>
          </table>
        </div>
      </main>
      </section>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/peserta.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init();
      // Inisialisasi DataTables setelah data dimuat
      $(document).ready(function () {
        $('#example').DataTable();
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </body>
</html>