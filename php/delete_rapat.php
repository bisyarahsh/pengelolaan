<?php
session_start();
include("koneksi.php");

if (isset($_POST['hapus_rapat'])) {
    $id_rapat = $_POST['hapus_id_rapat'];

    // JANGAN DELETE, TAPI UPDATE STATUS MENJADI 'DIBATALKAN'
    $sql = "UPDATE agenda_rapat SET status = 'dibatalkan' WHERE id_rapat = '$id_rapat'";
    $query = mysqli_query($koneksi, $sql);

    if ($query) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Rapat berhasil dibatalkan.'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal menghapus rapat.'];
    }

    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}
?>