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
    1 =>   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  );
  $pecahkan = explode('-', $tanggal);
  return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

// --- 2. AJAX Detail Handler (untuk memuat data ke modal) ---
// ... (Logika AJAX tetap sama seperti sebelumnya) ...
function get_rapat_detail($koneksi, $id_rapat) {
    $id_rapat = mysqli_real_escape_string($koneksi, $id_rapat);
    $sql = "SELECT r.*, o.nama_unit FROM agenda_rapat r JOIN unit o ON r.id_unit = o.id_unit WHERE r.id_rapat = '$id_rapat'";
    $q = mysqli_query($koneksi, $sql);
    $rapat_data = mysqli_fetch_assoc($q);

    if ($rapat_data) {
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

if (isset($_GET['action']) && $_GET['action'] == 'get_rapat_detail' && isset($_GET['id'])) {
    header('Content-Type: application/json');
    $id_rapat = $_GET['id'];
    $data = get_rapat_detail($koneksi, $id_rapat);
    
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
// --- End AJAX Detail Handler ---


// --- 3. Data Fetching for Main Table (Rapat yang sudah lewat) ---
$now_datetime = date('Y-m-d H:i:s'); 
$list_riwayat = [];

// [BARU] Menangkap Filter Tanggal
$tgl_awal  = isset($_POST['tgl_awal']) ? $_POST['tgl_awal'] : null;
$tgl_akhir = isset($_POST['tgl_akhir']) ? $_POST['tgl_akhir'] : null;

// Query Dasar
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
        AND CONCAT(ar.tanggal_rapat, ' ', ar.jam_rapat) <= '$now_datetime' AND ar.status = 'aktif'"; // Hanya yang sudah lewat

// [BARU] Logika Filter
if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $sql_read .= " AND ar.tanggal_rapat BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$sql_read .= " ORDER BY ar.tanggal_rapat DESC, ar.jam_rapat DESC";

$q_read = mysqli_query($koneksi, $sql_read);
while ($r_read = mysqli_fetch_assoc($q_read)) {
    $list_riwayat[] = $r_read;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <title>Riwayat | Peserta - Rapatin</title>
    <style>
    /* Agar input tanggal tetap terlihat putih meski readonly (bawaan flatpickr) */
    .form-control.flatpickr-input[readonly] {
        background-color: #fff; 
    }
    </style>
  </head>
  <body>
    <section id="sidebar">
      <a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"
        ><i class="ps-5"></i> Rapatin</a
      >
      <a
        href="../landing/index.php"
        data-aos="fade-down"
        class="logo-mini fw-bold"
      >
        R</a
      >
      <ul class="side-menu" data-aos="fade-right">
        <li>
          <a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a>
        </li>
        <li>
          <a href="agenda.php"
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
    <section id="content">
      <nav class="atas">
        <i data-aos="fade-right" class="fa-solid fa-bars toggle-sidebar"></i>
      </nav>
      <main>
        <div data-aos="fade-down" class="rapat bg-light">
          
          <div class="card border-0 shadow-sm mb-4">
              <div class="card-body p-3">
                  <h5 class="text-primary">Saring Riwayat Rapat</h5>
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
                                  <a href="history.php" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                                      <i class="fa-solid fa-arrows-rotate"></i> Reset
                                  </a>
                              <?php endif; ?>
                          </div>
                      </div>
                  </form>
              </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 page-header-mobile">
              <h2 class="text-primary fw-bold m-0 fs-3">Riwayat Rapat</h2>
          </div>
          <table id="tabel-rapat" class="table table-hover nowrap" style="width:100%">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal Rapat</th>
                <th>Jam Rapat</th>
                <th>Unit</th>
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

        <div class="modal fade modal-compact" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered"> <div class="modal-content">
                    <div class="modal-header header-primary text-white" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                        <h5 class="modal-title" id="viewModalLabel"><i class="fa-solid fa-circle-info me-2"></i>Detail Agenda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-4">

                            <div class="col-md-7 border-end">

                                <div class="d-flex align-items-center mb-4">
                                    <div class="view-date-box me-3" style="min-width: 80px;">
                                        <div class="day" id="view_tanggal_day">--</div> 
                                        <div class="month-year" id="view_tanggal_month">--</div>
                                    </div>
                                    <div>
                                        <div class="detail-label">Waktu Pelaksanaan</div>
                                        <div class="h5 fw-bold text-dark mb-0"><i class="fa-regular fa-clock me-2 text-warning"></i><span id="view_jam"></span></div>
                                        <small class="text-muted" id="view_tanggal_full"></small> </div>
                                </div>

                                <div class="mb-3">
                                    <div class="detail-label">Judul Rapat</div>
                                    <div class="h5 fw-bold text-primary" id="view_judul"></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="detail-label">Ruangan</div>
                                        <div class="detail-value"><i class="fa-solid fa-location-dot me-1 text-danger"></i> <span id="view_ruangan"></span></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-label">Unit Penyelenggara</div>
                                        <div class="detail-value"><i class="fa-solid fa-users-gear me-1 text-info"></i> <span id="view_unit"></span></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="detail-label">Keterangan</div>
                                    <div class="description-box" id="view_keterangan">
                                        </div>
                                </div>

                                <div>
                                    <div class="detail-label mb-2">Dokumen Notulen</div>
                                    <div id="view_notulen_container">
                                        <span id="view_notulen_file"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-users me-2"></i>Daftar Peserta</h6>
                                    <span class="badge bg-secondary rounded-pill" id="view_peserta_count">0 Orang</span>
                                </div>

                                <div class="participant-list-container" id="view_peserta_list_box">
                                    <div class="text-center text-muted mt-5">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Memuat...
                                     </div>
                                </div>
                                <span id="view_peserta" style="display:none;"></span> 
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
                    </div>
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
    <script src="../assets/peserta.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            // Inisialisasi Flatpickr
            flatpickr(".input-tanggal", {
                locale: "id",
                altInput: true,
                altFormat: "j F Y",
                dateFormat: "Y-m-d",
                allowInput: true
            });

            $('.view-rapat-btn').on('click', function() {
                var id_rapat = $(this).data('id');
                
                // Reset tampilan peserta
                $('#view_peserta_list_box').html('<div class="text-center text-muted mt-5"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>');
                
                $.ajax({
                    url: 'history.php', 
                    type: 'GET',
                    data: { action: 'get_rapat_detail', id: id_rapat },
                    dataType: 'json',
                    success: function(data) {
                        if(data) {
                            // 1. Set Info Utama 
                            $('#view_judul').text(data.judul_rapat);
                            var jamClean = data.jam_rapat ? data.jam_rapat.substring(0, 5) : '--:--';
                            $('#view_jam').text(jamClean + ' WIB');
                            $('#view_ruangan').text(data.ruang_rapat ? data.ruang_rapat : '-');
                            $('#view_unit').text(data.nama_unit);
                            $('#view_keterangan').text(data.keterangan ? data.keterangan : '-');
                            
                            // 2. Format Tanggal
                            if (data.tanggal_rapat) {
                                const dateObj = new Date(data.tanggal_rapat);
                                const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                                const months = ["JAN", "FEB", "MAR", "APR", "MEI", "JUN", "JUL", "AGU", "SEP", "OKT", "NOV", "DES"];
                                
                                $('#view_tanggal_day').text(dateObj.getDate());
                                $('#view_tanggal_month').text(months[dateObj.getMonth()]);
                                const fullDate = days[dateObj.getDay()] + ', ' + dateObj.getDate() + ' ' + months[dateObj.getMonth()] + ' ' + dateObj.getFullYear();
                                $('#view_tanggal_full').text(fullDate);
                            }

                            // 3. Format File Notulen
                            if (data.notulen_file) {
                                var fileName = data.notulen_file;
                                var fileExt = fileName.split('.').pop().toLowerCase(); 
                                var iconClass = 'fa-file'; 
                                var colorClass = 'text-secondary'; 

                                if (fileExt === 'pdf') { iconClass = 'fa-file-pdf'; colorClass = 'text-danger'; } 
                                else if (fileExt === 'doc' || fileExt === 'docx') { iconClass = 'fa-file-word'; colorClass = 'text-primary'; } 
                                else if (fileExt === 'jpg' || fileExt === 'jpeg' || fileExt === 'png') { iconClass = 'fa-file-image'; colorClass = 'text-success'; }

                                $('#view_notulen_container').html(`
                                    <div class="file-attachment-box">
                                        <div class="d-flex align-items-center" style="overflow: hidden;">
                                            <i class="fa-solid ${iconClass} ${colorClass} fa-xl me-3"></i>
                                            <div class="d-flex flex-column" style="overflow: hidden;">
                                                <span class="fw-bold small text-truncate" style="max-width: 100%;" title="${fileName}">${fileName}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">Format: ${fileExt.toUpperCase()}</span>
                                            </div>
                                        </div>
                                        <a href="../notulen_files/${fileName}" target="_blank" class="btn btn-sm btn-outline-dark ms-2" title="Download File">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                `);
                            } else {
                                $('#view_notulen_container').html('<span class="text-muted small"><i>Tidak ada file notulen yang diunggah.</i></span>');
                            }

                            // 4. Daftar Peserta
                            var pesertaHtml = '';
                            if (data.peserta_details && data.peserta_details.length > 0) {
                                $('#view_peserta_count').text(data.peserta_details.length + ' Orang');
                                $.each(data.peserta_details, function(index, value) {
                                    pesertaHtml += `
                                        <div class="participant-item">
                                            <i class="fa-solid fa-user-circle fa-lg"></i>
                                            <div>${value}</div>
                                        </div>
                                    `;
                                });
                            } else {
                                $('#view_peserta_count').text('0 Orang');
                                pesertaHtml = '<div class="text-center text-muted mt-4"><small>Belum ada peserta yang ditambahkan.</small></div>';
                            }
                            $('#view_peserta_list_box').html(pesertaHtml);
                        }
                    }
                });
            });

            // Handler Tombol DOWNLOAD PDF DETAIL RAPAT
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
        });
    </script>
  </body>
</html>