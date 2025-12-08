<?php
// FILE: ../php/send_notification.php

session_start();
// Pastikan path koneksi.php benar
include 'koneksi.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Sesuaikan path ini dengan lokasi PHPMailer Anda
require '../lib/PHPMailer/src/Exception.php';
require '../lib/PHPMailer/src/PHPMailer.php';
require '../lib/PHPMailer/src/SMTP.php';

// Pastikan path koneksi.php benar
// include 'koneksi.php'; // Sudah di-include di awal, hapus duplikasi ini

// --- KONFIGURASI SMTP ANDA DI SINI ---
$smtpHost     = 'smtp.gmail.com';         // HOST: smtp.gmail.com (jika pakai Gmail)
$smtpUsername = 'dabledobel@gmail.com';   // Alamat email pengirim
$smtpPassword = 'opun nfgf uche hnmx';             // GANTI DENGAN APP PASSWORD JIKA MENGGUNAKAN GMAIL
$smtpPort     = 587;                      // Port SMTP (587 dengan STARTTLS)
// ------------------------------------

// Cek Sesi dan Role (Hanya Ketua yang boleh mengirim notifikasi)
if ($_SESSION['status'] != "login" || strtolower($_SESSION['role']) != "ketua" || !isset($_POST['send_notification'])) {
    header("location:../login/login.php?error=noaccess");
    exit;
}

$id_rapat = mysqli_real_escape_string($koneksi, $_POST['notif_id_rapat']);

// --- 1. Ambil Detail Rapat (Kode ini tidak berubah) ---
$sql_rapat = "SELECT 
                r.judul_rapat, 
                r.tanggal_rapat, 
                r.jam_rapat, 
                r.ruang_rapat, 
                r.keterangan, 
                o.nama_unit 
              FROM agenda_rapat r
              JOIN unit o ON r.id_unit = o.id_unit
              WHERE r.id_rapat = '$id_rapat'";
$q_rapat = mysqli_query($koneksi, $sql_rapat);
$rapat = mysqli_fetch_assoc($q_rapat);

if (!$rapat) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Rapat tidak ditemukan.'];
    header("location:../admin/agenda.php");
    exit;
}

// --- 2. Ambil Email Peserta (Kode ini tidak berubah) ---
$sql_peserta = "SELECT u.email, u.nama_lengkap 
                 FROM peserta_rapat p
                 JOIN users u ON p.id_user = u.id_user
                 WHERE p.id_rapat = '$id_rapat' 
                   AND u.role = 'Peserta'
                   AND u.email IS NOT NULL AND u.email != ''";
$q_peserta = mysqli_query($koneksi, $sql_peserta);

$emails = [];
while ($peserta = mysqli_fetch_assoc($q_peserta)) {
    $emails[] = $peserta;
}

if (empty($emails)) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Tidak ada peserta dengan alamat email yang valid untuk rapat ini.'];
    header("location:../admin/agenda.php");
    exit;
}

// --- 3. Siapkan Konten Email (Kode ini tidak berubah) ---
date_default_timezone_set('Asia/Jakarta');
$tanggal_indo = date('d F Y', strtotime($rapat['tanggal_rapat']));
$jam_indo = date('H:i', strtotime($rapat['jam_rapat'])) . ' WIB';

$subject = "PEMBERITAHUAN RAPAT: " . $rapat['judul_rapat'];

$message_template = "
Yth. Rekan Peserta Rapat,

Anda diundang untuk menghadiri rapat dengan detail sebagai berikut:

    - Judul Rapat : {$rapat['judul_rapat']}
    - Unit : {$rapat['nama_unit']}
    - Tanggal : {$tanggal_indo}
    - Jam : {$jam_indo}
    - Ruangan : {$rapat['ruang_rapat']}

{$rapat['keterangan']}

Mohon hadir tepat waktu. Terima kasih.

Salam,
Ketua Unit
";

$success_count = 0;
$failed_emails = [];

// =========================================================
//         4. INI ADALAH BAGIAN PHPMailer BARU
// =========================================================

$mail = new PHPMailer(true); // true mengaktifkan Exception
$mail->CharSet = 'UTF-8';

try {
    // Pengaturan Server SMTP
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Menggunakan TLS
    $mail->Port       = $smtpPort;

    // Pengaturan Pengirim (FROM)
    $mail->setFrom($smtpUsername, 'Rapatin - Notifikasi');
    $mail->addReplyTo('no-reply@rapatin.com', 'No Reply');

    // Konten Email umum
    $mail->isHTML(false); // Menggunakan format Teks Biasa (Plain Text)
    $mail->Subject = $subject;
    $mail->Body    = $message_template;
    
    // --- Loop untuk Mengirim ke Setiap Peserta ---
    foreach ($emails as $peserta) {
        // Hapus alamat penerima dari pengiriman sebelumnya
        $mail->clearAddresses(); 
        
        // Tambahkan penerima saat ini
        $mail->addAddress($peserta['email'], $peserta['nama_lengkap']); 

        // Kirim email
        if ($mail->send()) {
            $success_count++;
        } else {
            // Jika ada masalah saat mengirim ke email tertentu
            $failed_emails[] = $peserta['nama_lengkap'] . " (" . $peserta['email'] . ")";
        }
    }
    
} catch (Exception $e) {
    // Jika ada masalah pada konfigurasi PHPMailer atau koneksi
    $_SESSION['alert'] = ['type' => 'error', 'message' => "Gagal mengirim notifikasi email. PHPMailer Error: {$mail->ErrorInfo}"];
    header("location:../admin/agenda.php");
    exit;
}

// =========================================================
//         5. Beri Feedback dan Redirect (Tidak Berubah)
// =========================================================

if ($success_count > 0) {
    $message = "Notifikasi berhasil dikirim kepada $success_count peserta.";
    if (!empty($failed_emails)) {
        $message .= " (Gagal dikirim ke: " . implode(', ', $failed_emails) . ")";
        $_SESSION['alert'] = ['type' => 'warning', 'message' => $message];
    } else {
        $_SESSION['alert'] = ['type' => 'success', 'message' => $message];
    }
} else {
    // Jika success_count adalah 0 dan tidak ada error Exception
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengirim notifikasi ke semua peserta. Cek kembali App Password dan koneksi SMTP.'];
}

header("location:../admin/agenda.php");
exit;
?>