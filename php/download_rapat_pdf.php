<?php
require '../lib/dompdf/autoload.inc.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

include 'koneksi.php';

$id_rapat = $_GET['id'] ?? null;

if (!$id_rapat) {
    die("ID Rapat tidak ditemukan.");
}

$sql = "SELECT 
            r.*, 
            o.nama_unit,
            GROUP_CONCAT(CONCAT(IFNULL(u.nim, ''), ' - ', u.nama_lengkap) SEPARATOR '|||') AS peserta_details
        FROM agenda_rapat r
        LEFT JOIN unit o ON r.id_unit = o.id_unit
        LEFT JOIN peserta_rapat pr ON r.id_rapat = pr.id_rapat
        LEFT JOIN users u ON pr.id_user = u.id_user
        WHERE r.id_rapat = '" . mysqli_real_escape_string($koneksi, $id_rapat) . "'
        GROUP BY r.id_rapat";

$result = mysqli_query($koneksi, $sql);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data rapat tidak ditemukan.");
}

// ZONA WAKTU WIB
date_default_timezone_set('Asia/Jakarta');
$tanggalFormatted = date('d F Y', strtotime($data['tanggal_rapat']));

$jamRapatFormatted = date('H:i', strtotime($data['jam_rapat'])) . ' WIB'; 

$judulRapat = htmlspecialchars($data['judul_rapat']);
$namaFilePDF = 'Detail_Rapat_' . str_replace([' ', '/', '\\'], '_', $judulRapat) . '_' . date('Ymd') . '.pdf';

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_uri = $_SERVER['SCRIPT_NAME'];
$admin_index = array_search('admin', explode('/', $script_uri)); 
$root_path_uri = implode('/', array_slice(explode('/', $script_uri), 0, $admin_index));
$base_uri = $protocol . '://' . $host . $root_path_uri;
$notulen_files_base = rtrim($base_uri, '/') . '/pengelolaan/notulen_files'; 

$pesertaArray = [];
if (!empty($data['peserta_details'])) {
    $pesertaArray = explode('|||', $data['peserta_details']);
}

$pesertaHtml = '<div style="font-size: 0;">';

if (count($pesertaArray) > 0) {
    foreach ($pesertaArray as $index => $pesertaString) {
        $parts = explode(' - ', trim($pesertaString), 2);
        
        if (count($parts) == 2) {
            $nimTampil = $parts[0];
            $namaTampil = $parts[1];
        } else {
            $nimTampil = ''; 
            $namaTampil = $pesertaString;
        }

        $pesertaHtml .= '
        <div style="display: inline-block; width: 48%; vertical-align: top; margin-bottom: 6px; padding: 6px 0; border-bottom: 1px solid #eee; margin-right: 2%; font-size: 13px;">
            <span style="color: #1a237e; margin-right: 4px;">&#8226;</span> 
            
            <span style="color: #333;">' . htmlspecialchars($nimTampil) . '</span>
            
            <span style="color: #ccc; margin: 0 3px;">-</span>
            
            <span style="color: #333;">' . htmlspecialchars($namaTampil) . '</span>
        </div>';
    }
} else {
    $pesertaHtml .= '<div style="font-size: 13px; color: #999; padding: 10px 0;">Tidak ada peserta.</div>';
}
$pesertaHtml .= '</div>';

$notulenHtml = 'Tidak ada file notulen.';
if (!empty($data['notulen_file'])) {
    $fileName = htmlspecialchars($data['notulen_file']);
    $fileUrl = rtrim($notulen_files_base, '/') . '/' . $fileName; 
    $notulenHtml = '<a href="' . $fileUrl . '" style="color: #007bff; text-decoration: underline;">' . $fileName . '</a>';
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $judulRapat . '</title>
    <style>
        /* Mengatur Margin Halaman agar Header bisa Full Width */
        @page { margin: 0px; }
        
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #444;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        .header-banner {
            background-color: #1a237e; /* Deep Blue Professional */
            color: #fff;
            padding: 40px 50px;
            border-bottom: 5px solid #ffab00; /* Gold Accent */
        }
        
        .header-table { width: 100%; }
        .brand-text { font-size: 14px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; }
        .doc-title { font-size: 28px; font-weight: bold; margin: 10px 0 5px 0; }
        .doc-subtitle { font-size: 16px; font-weight: 300; opacity: 0.9; }

        /* MAIN CONTENT CONTAINER */
        .container {
            padding: 40px 50px;
        }

        /* SECTION STYLING */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1a237e;
            text-transform: uppercase;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 15px;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        /* INFO GRID (Menggunakan Table) */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .label {
            width: 140px;
            color: #888;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .value {
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }

        /* CONTENT BOX (Keterangan) */
        .content-box {
            background-color: #f8f9fa;
            border-left: 4px solid #1a237e;
            padding: 15px 20px;
            text-align: justify;
            font-size: 14px;
            color: #555;
            margin-bottom: 30px;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            background-color: #e8eaf6;
            color: #1a237e;
            padding: 6px 12px;
            border-radius: 20px; /* Fully Rounded */
            font-size: 11px;
            font-weight: bold;
            margin-right: 5px;
            margin-bottom: 8px;
            border: 1px solid #c5cae9;
        }
        .empty-state { font-style: italic; color: #999; font-size: 13px; }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            background-color: #f4f4f4;
            color: #777;
            text-align: center;
            line-height: 40px;
            font-size: 10px;
            border-top: 1px solid #ddd;
        }
        
        /* Utility */
        .icon { font-family: sans-serif; margin-right: 5px; }
        .highlight { color: #d32f2f; font-weight: bold; }
    </style>
</head>
<body>

    <div class="header-banner">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-text">RAPATIN</div>
                    <div class="doc-title">DETAIL RAPAT</div>
                </td>
                <td align="right" style="vertical-align: middle;">
                    <div style="border: 2px solid rgba(255,255,255,0.3); padding: 10px 20px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 10px; opacity: 0.7;">TANGGAL CETAK</div>
                        <div style="font-size: 14px; font-weight: bold;">' . date('d M Y') . '</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="container">

        <div class="section-title">Informasi Utama</div>
        <table class="info-table">
            <tr>
                <td class="label">Judul Rapat</td>
                <td class="value" style="font-size: 14px; font-weight: 500; color: #333;">' . $judulRapat . '</td>
            </tr>
            <tr>
                <td class="label">Waktu</td>
                <td class="value">
                    ' . $tanggalFormatted . ' &nbsp;|&nbsp; ' . $jamRapatFormatted . '
                </td>
            </tr>
            <tr>
                <td class="label">Ruang Rapat</td>
                <td class="value">' . htmlspecialchars($data['ruang_rapat']) . '</td>
            </tr>
            <tr>
                <td class="label">Dokumen</td>
                <td class="value">' . $notulenHtml . '</td>
            </tr>
        </table>

        <div class="section-title">Agenda & Pembahasan</div>
        <div class="content-box">
            ' . nl2br(htmlspecialchars($data['keterangan'])) . '
        </div>

        <div class="section-title">
            Daftar Peserta <span style="font-size: 11px; color: #888; font-weight: normal; text-transform: none;">(Total: ' . count($pesertaArray) . ' Orang)</span>
        </div>
        <div style="margin-top: 10px;">
            ' . $pesertaHtml . '
        </div>

    </div>

</body>
</html>';

$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true); 
$options->set('isRemoteEnabled', true); 

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream($namaFilePDF, ["Attachment" => true]);

exit;
?>