<?php

session_start();
include 'koneksi.php';

// Cek Sesi dan Role
if ($_SESSION['status'] != "login" || strtolower($_SESSION['role']) != "admin" || !isset($_POST['hapus_rapat'])) {
    header("location:../login/login.php?error=noaccess");
    exit;
}

$id_rapat_hapus = mysqli_real_escape_string($koneksi, $_POST['hapus_id_rapat']);
$redirect_page = $_POST['redirect_to'] ?? 'riwayat.php'; 

$q_file = mysqli_query($koneksi, "SELECT notulen_file FROM agenda_rapat WHERE id_rapat = '$id_rapat_hapus'");
$r_file = mysqli_fetch_assoc($q_file);
$file_to_delete = $r_file['notulen_file'] ?? '';

mysqli_query($koneksi, "DELETE FROM peserta_rapat WHERE id_rapat = '$id_rapat_hapus'");

$sql_delete = "DELETE FROM agenda_rapat WHERE id_rapat = '$id_rapat_hapus'";

if (mysqli_query($koneksi, $sql_delete)) {
    $target_dir = "../notulen_files/";
    if (!empty($file_to_delete) && file_exists($target_dir . $file_to_delete)) {
        unlink($target_dir . $file_to_delete);
    }
    
    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Rapat berhasil dihapus!'];
    header("location:../admin/" . $redirect_page);
    exit;
} else {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal menghapus rapat: ' . mysqli_error($koneksi)];
    header("location:../admin/" . $redirect_page);
    exit;
}
?>