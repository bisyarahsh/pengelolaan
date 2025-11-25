<?php
// Pastikan tidak ada spasi di atas baris ini

include("../php/koneksi.php"); 
// Catatan: Pastikan path ke koneksi.php benar relatif terhadap file ini!

// --- Fungsi get_rapat_detail() dari agenda.php ---
// Pindahkan atau copy paste fungsi get_rapat_detail() dari agenda.php ke sini!
function get_rapat_detail($koneksi, $id_rapat) {
    // Pastikan query ini sudah ada di agenda.php dan berfungsi
    $query = "SELECT 
                a.*, 
                o.nama_organisasi, 
                GROUP_CONCAT(CONCAT(u.nim, ' - ', u.nama_lengkap) SEPARATOR '|||') AS peserta_nama_lengkap,
                GROUP_CONCAT(u.id_user SEPARATOR '|||') AS peserta_id
              FROM agenda_rapat a
              LEFT JOIN organisasi o ON a.id_organisasi = o.id_organisasi
              LEFT JOIN peserta_rapat pr ON a.id_rapat = pr.id_rapat
              LEFT JOIN users u ON pr.id_user = u.id_user
              WHERE a.id_rapat = '$id_rapat'
              GROUP BY a.id_rapat";
              
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        // Proses peserta
        $data['peserta_details'] = $data['peserta_nama_lengkap'] ? explode('|||', $data['peserta_nama_lengkap']) : [];
        $data['peserta_id'] = $data['peserta_id'] ? explode('|||', $data['peserta_id']) : [];
        unset($data['peserta_nama_lengkap']);
    }

    return $data;
}
// --- Akhir Fungsi get_rapat_detail() ---


// Logika Utama AJAX
header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Jika ID kosong, kirim respons error
    echo json_encode(["error" => "ID Rapat tidak ditemukan."]);
    exit;
}

$id_rapat = mysqli_real_escape_string($koneksi, $_GET['id']);
$data = get_rapat_detail($koneksi, $id_rapat);

// Pastikan data tidak NULL sebelum di-encode
if (!$data) {
    echo json_encode(["error" => "Data rapat tidak ditemukan untuk ID ini."]);
} else {
    // JSON encode dan exit secara bersih
    echo json_encode($data);
}
exit; // SANGAT PENTING
?>