<?php
// Pastikan path ke file Dompdf yang benar
require '../lib/dompdf/autoload.inc.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Ambil ID Rapat dan Koneksi
include 'koneksi.php';

$id_rapat = $_GET['id'] ?? null;

if (!$id_rapat) {
    die("ID Rapat tidak ditemukan.");
}

// 2. Ambil Data Rapat (Query tetap)
$sql = "SELECT 
            r.*, 
            o.nama_organisasi,
            GROUP_CONCAT(u.nama_lengkap SEPARATOR '|||') AS peserta_details
        FROM agenda_rapat r
        LEFT JOIN organisasi o ON r.id_organisasi = o.id_organisasi
        LEFT JOIN peserta_rapat pr ON r.id_rapat = pr.id_rapat
        LEFT JOIN users u ON pr.id_user = u.id_user
        WHERE r.id_rapat = '" . mysqli_real_escape_string($koneksi, $id_rapat) . "'
        GROUP BY r.id_rapat";

$result = mysqli_query($koneksi, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data rapat tidak ditemukan.");
}

// 3. Persiapan Data (MODIFIKASI PENTING DI BAGIAN INI)
// =======================================================

// A. SETEL ZONA WAKTU PHP ke WIB
// 'Asia/Jakarta' mencakup Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

// B. FORMAT TANGGAL
$tanggalFormatted = date('d F Y', strtotime($data['tanggal_rapat']));

// C. FORMAT JAM (Jam:Menit, tanpa Detik, + WIB)
// Menggunakan 'H:i' untuk format 24 jam (tanpa detik)
$jamRapatFormatted = date('H:i', strtotime($data['jam_rapat'])) . ' WIB'; 

// D. DATA LAIN
$judulRapat = htmlspecialchars($data['judul_rapat']);
$namaFilePDF = 'Detail_Rapat_' . str_replace([' ', '/', '\\'], '_', $judulRapat) . '_' . date('Ymd') . '.pdf';

// Bangun BASE URL ABSOLUT untuk Tautan File (Logika tetap sama)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_uri = $_SERVER['SCRIPT_NAME'];
$admin_index = array_search('admin', explode('/', $script_uri)); 
$root_path_uri = implode('/', array_slice(explode('/', $script_uri), 0, $admin_index));
$base_uri = $protocol . '://' . $host . $root_path_uri;
$notulen_files_base = rtrim($base_uri, '/') . '/pengelolaan/notulen_files'; 

// Peserta
$pesertaArray = [];
if (!empty($data['peserta_details'])) {
    $pesertaArray = explode('|||', $data['peserta_details']);
}
$pesertaHtml = '<ul>';
if (count($pesertaArray) > 0) {
    foreach ($pesertaArray as $peserta) {
        $pesertaHtml .= '<li>' . htmlspecialchars(trim($peserta)) . '</li>';
    }
} else {
    $pesertaHtml .= '<li>Tidak ada peserta.</li>';
}
$pesertaHtml .= '</ul>';

// File Notulen
$notulenHtml = 'Tidak ada file notulen.';
if (!empty($data['notulen_file'])) {
    $fileName = htmlspecialchars($data['notulen_file']);
    $fileUrl = rtrim($notulen_files_base, '/') . '/' . $fileName; 
    $notulenHtml = '<a href="' . $fileUrl . '" style="color: #007bff; text-decoration: underline;">' . $fileName . '</a>';
}

// =======================================================


// 4. Buat Konten HTML untuk PDF (Menggunakan variabel $jamRapatFormatted yang baru)
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $judulRapat . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        h1 { color: #333; border-bottom: 2px solid #ccc; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; width: 30%; font-weight: bold; }
        ul { margin: 0; padding-left: 0px; list-style: none; }
        li { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h1>Detail Rapat: ' . $judulRapat . '</h1>
    
    <table>
        <tbody>
            <tr><th>Tanggal Rapat</th><td>' . $tanggalFormatted . '</td></tr>
            <tr><th>Jam Rapat</th><td>' . $jamRapatFormatted . '</td></tr>
            <tr><th>Judul Rapat</th><td>' . $judulRapat . '</td></tr>
            <tr><th>Ruang Rapat</th><td>' . htmlspecialchars($data['ruang_rapat']) . '</td></tr>
            <tr><th>Organisasi</th><td>' . htmlspecialchars($data['nama_organisasi']) . '</td></tr>
            <tr><th>Keterangan</th><td>' . nl2br(htmlspecialchars($data['keterangan'])) . '</td></tr>
            <tr><th>File Notulen</th><td>' . $notulenHtml . '</td></tr>
            <tr><th>Peserta Rapat</th><td>' . $pesertaHtml . '</td></tr>
        </tbody>
    </table>
</body>
</html>';

// 5. Konfigurasi dan Generasi PDF (Dompdf)
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true); 
$options->set('isRemoteEnabled', true); 

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream (Unduh) file yang dihasilkan ke browser
$dompdf->stream($namaFilePDF, ["Attachment" => true]);

exit;
?>