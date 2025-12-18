<?php
include '../php/koneksi.php';
session_start();

// --- 1. Set Zona Waktu dan Ambil ID User ---
date_default_timezone_set('Asia/Jakarta'); 

if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

$id_user = mysqli_real_escape_string($koneksi, $_SESSION['id_user']); 

// Cek Role
if (strtolower($_SESSION['role']) != "peserta") { 
    header("location:../login/login.php?error=noaccess");
    exit;
}

$now_datetime = date('Y-m-d H:i:s'); 

function tgl_indo($tanggal){
    $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

// --- 2. LOGIKA FILTER & QUERY ---
$tgl_awal  = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : null;
$tgl_akhir = isset($_POST['tgl_akhir']) ? $_POST['tgl_akhir'] : null;

// Query mengambil data termasuk kolom 'status'
// Kita gunakan IFNULL pada status untuk antisipasi data lama yang kosong
$sql_agenda = "
    SELECT
        ar.tanggal_rapat,
        ar.jam_rapat,
        ar.judul_rapat,
        ar.keterangan,
        ar.ruang_rapat,
        ar.status, 
        o.nama_unit
    FROM
        agenda_rapat ar
    JOIN
        peserta_rapat pr ON ar.id_rapat = pr.id_rapat
    LEFT JOIN 
        unit o ON ar.id_unit = o.id_unit
    WHERE
        pr.id_user = '$id_user'";

$sql_agenda .= " AND (
                    (ar.status = 'dibatalkan' AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) >= '$now_datetime') 
                    OR 
                    (ar.status = 'aktif' AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) >= '$now_datetime')
                 )";

// Tambahan Filter Tanggal dari User
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql_agenda .= " AND ar.tanggal_rapat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

// Urutkan: Yang dibatalkan taruh paling bawah atau sesuai tanggal?
// Di sini kita urutkan sesuai tanggal agar rapi
$sql_agenda .= " ORDER BY ar.tanggal_rapat ASC, ar.jam_rapat ASC";

$query_agenda = mysqli_query($koneksi, $sql_agenda);

// Fungsi Status Display
function get_status_display($db_status, $tanggal, $jam) {
    // 1. Cek Status Database Dulu (Prioritas Utama)
    if ($db_status == 'dibatalkan') {
        return ['text' => 'Dibatalkan', 'class' => 'bg-danger'];
    }

    // 2. Jika status aktif, cek waktu
    $datetime_rapat = date('Y-m-d H:i:s', strtotime("$tanggal $jam"));
    $now = date('Y-m-d H:i:s');
    
    if ($datetime_rapat >= $now) {
        return ['text' => 'Menunggu', 'class' => 'bg-warning text-dark'];
    } 
    return ['text' => 'Selesai', 'class' => 'bg-success']; 
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.4/css/responsive.bootstrap5.min.css"/>
    <link rel="stylesheet" href="../assets/peserta.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <title>Agenda | Peserta - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
    .form-control.flatpickr-input[readonly] { background-color: #fff; }
    /* Style khusus baris yang dibatalkan (opsional) */
    .row-cancelled { background-color: #ffecec !important; }
    .row-cancelled td { color: #888; text-decoration: line-through; }
    .row-cancelled td.status-cell { text-decoration: none; } /* Badge jangan dicoret */
    </style>
  </head>
  <body>
    <section id="sidebar">
    <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
    <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
    <ul class="side-menu" data-aos="fade-right">
      <li><a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
      <li><a href="agenda.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
      <li><a href="history.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
      <li><a href="ganti_password.php"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
      <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
    </ul>
  </section>
  <section id="content">
      <nav class="atas">
        <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar' ></i>
      </nav>
      <main>
        <div data-aos="fade-down" class="rapat bg-light">
          
          <div class="card border-0 shadow-sm mb-4">
              <div class="card-body p-3">
                  <h5 class="text-primary">Saring berdasarkan tanggal</h5>
                  <form action="" method="POST">
                      <div class="row align-items-end g-2">
                          <div class="col-md-4">
                              <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-calendar-days me-1"></i> Dari Tanggal</label>
                              <input type="text" name="tgl_awal" class="form-control form-control-sm input-tanggal" value="<?php echo $tgl_awal; ?>" placeholder="Pilih Tanggal..." autocomplete="off">
                          </div>
                          <div class="col-md-4">
                              <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-calendar-check me-1"></i> Sampai Tanggal</label>
                              <input type="text" name="tgl_akhir" class="form-control form-control-sm input-tanggal" value="<?php echo $tgl_akhir; ?>" placeholder="Pilih Tanggal..." autocomplete="off">
                          </div>
                          <div class="col-md-4 d-flex gap-2">
                              <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                  <i class="fa-solid fa-filter me-1"></i> Terapkan
                              </button>
                              <?php if(!empty($tgl_awal)): ?>
                                  <a href="agenda.php" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                      <i class="fa-solid fa-arrows-rotate"></i> Reset
                                  </a>
                              <?php endif; ?>
                          </div>
                      </div>
                  </form>
              </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
            <h2 class="text-primary fw-bold m-0 fs-3">Agenda Rapat</h2>
        </div>
          <table id="tabel-rapat" class="table table-hover nowrap" style="width:100%">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th>Tanggal Rapat</th>
                <th>Jam Rapat</th>
                <th>Unit</th>
                <th>Judul Rapat</th>
                <th>Ruangan</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($query_agenda) > 0) {
                while ($rapat = mysqli_fetch_assoc($query_agenda)) {
                    // Cek status dari database, default 'aktif' jika null
                    $db_status = isset($rapat['status']) ? $rapat['status'] : 'aktif'; 
                    
                    // Panggil fungsi status display baru
                    $status_display = get_status_display($db_status, $rapat['tanggal_rapat'], $rapat['jam_rapat']);
                    
                    $jam_wib = date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB';
                    
                    // Efek visual baris (opsional)
                    $row_class = ($db_status == 'dibatalkan') ? 'row-cancelled' : '';
            ?>
                <tr class="<?php echo $row_class; ?>">
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars(tgl_indo($rapat['tanggal_rapat'])); ?></td>
                    <td><?php echo $jam_wib; ?></td>
                    <td><?php echo htmlspecialchars($rapat['nama_unit']); ?></td>
                    <td><?php echo htmlspecialchars($rapat['judul_rapat']); ?></td>
                    <td><?php echo htmlspecialchars($rapat['ruang_rapat']); ?></td>
                    <td class="text-center status-cell">
                        <span class="badge <?php echo $status_display['class']; ?> rounded-pill px-3 shadow-sm">
                            <?php echo $status_display['text']; ?>
                        </span>
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
    <script src="https://cdn.datatables.net/responsive/2.3.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.4/js/responsive.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/peserta.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    <script>
      AOS.init();
      
      $(document).ready(function () {
        $('#tabel-rapat').DataTable({
            responsive: false, 
            scrollX: true,
            scrollCollapse: true,
            columnDefs: [
                { className: "text-center", targets: [0, 6] },
                { className: "align-middle", targets: "_all" },
                { width: "50px", targets: 0 },   
                { width: "150px", targets: 1 },  
                { width: "100px", targets: 2 },  
                { width: "150px", targets: 3 },  
                { width: "150px", targets: 4 },  
                { width: "150px", targets: 5 },
                { width: "100px", targets: 6 }
            ],
            "language": {
                "emptyTable": "Tidak ada agenda rapat",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ agenda",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 agenda",
                "infoFiltered": "(difilter dari total _MAX_ agenda)",
                "lengthMenu": "Tampilkan _MENU_ agenda",
                "search": "Cari:",
                "zeroRecords": "Tidak ditemukan agenda rapat yang cocok"
            }
        });

        flatpickr(".input-tanggal", {
            locale: "id",
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "Y-m-d",
            allowInput: true
        });
      });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </body>
</html>