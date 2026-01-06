<?php
// FILE: ../php/hapus_rapat.php

session_start();
include 'koneksi.php'; // Path koneksi.php dari folder php

// Cek Sesi dan Role (Hanya admin yang boleh menghapus)
if ($_SESSION['status'] != "login" || strtolower($_SESSION['role']) != "admin" || !isset($_POST['hapus_rapat'])) {
    header("location:../login/login.php?error=noaccess");
    exit;
}

$id_rapat_hapus = mysqli_real_escape_string($koneksi, $_POST['hapus_id_rapat']);
// Ambil halaman pengalihan dari input tersembunyi
$redirect_page = $_POST['redirect_to'] ?? 'agenda.php'; 

// Sanitize redirect page untuk keamanan
if ($redirect_page !== 'agenda.php' && $redirect_page !== 'riwayat.php') {
    $redirect_page = 'agenda.php';
}

// 1. Ambil nama file notulen sebelum dihapus dari DB
$q_file = mysqli_query($koneksi, "SELECT notulen_file FROM agenda_rapat WHERE id_rapat = '$id_rapat_hapus'");
$r_file = mysqli_fetch_assoc($q_file);
$file_to_delete = $r_file['notulen_file'] ?? '';

// 2. Hapus peserta terkait
mysqli_query($koneksi, "DELETE FROM peserta_rapat WHERE id_rapat = '$id_rapat_hapus'");

// 3. Hapus agenda rapat
$sql_delete = "DELETE FROM agenda_rapat WHERE id_rapat = '$id_rapat_hapus'";

if (mysqli_query($koneksi, $sql_delete)) {
    // 4. Hapus file fisik jika ada
    $target_dir = "../notulen_files/";
    if (!empty($file_to_delete) && file_exists($target_dir . $file_to_delete)) {
        unlink($target_dir . $file_to_delete);
    }
    
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Rapat berhasil dihapus!'];
    // Arahkan kembali ke halaman sebelumnya
    header("location:../admin/" . $redirect_page);
    exit;
} else {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal menghapus rapat: ' . mysqli_error($koneksi)];
    header("location:../admin/" . $redirect_page);
    exit;
}
?>