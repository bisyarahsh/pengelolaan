<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// --- Set Zona Waktu ke WIB ---
date_default_timezone_set('Asia/Jakarta'); 
// --- End Set Zona Waktu ---

if ($_SESSION['status'] != "login" || strtolower($_SESSION['role']) != "admin" || !isset($_POST['tambah_rapat'])) {
    header("location:../login/login.php?error=noaccess");
    exit;
}

$id_pembuat_rapat = $_SESSION['id_user'];
$tanggal = mysqli_real_escape_string($koneksi, $_POST['date']);
$jam = mysqli_real_escape_string($koneksi, $_POST['time']);
$judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
$ruangan = mysqli_real_escape_string($koneksi, $_POST['ruangan']);
$id_unit = mysqli_real_escape_string($koneksi, $_POST['unit']);
$peserta_arr = $_POST['peserta_rapat'] ?? [];
$keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
$notulen_file = '';

if (isset($_FILES['filename']) && $_FILES['filename']['error'] == 0) {
    
    $target_dir = "../notulen_files/"; 

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); 
    }

    $file_name_original = basename($_FILES["filename"]["name"]);
    $file_tmp = $_FILES["filename"]["tmp_name"];
    $file_ext = pathinfo($file_name_original, PATHINFO_EXTENSION);

    $new_file_name = "notulen_" . $tanggal . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        $notulen_file = $new_file_name; 
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengupload file notulen. Cek izin folder!'];
        header("location:../admin/agenda.php");
        exit;
    }
}

$sql_insert = "INSERT INTO agenda_rapat (tanggal_rapat, jam_rapat, judul_rapat, ruang_rapat, keterangan, notulen_file, id_unit, id_pembuat) 
           VALUES ('$tanggal', '$jam', '$judul', '$ruangan', '$keterangan', '$notulen_file', '$id_unit', '$id_pembuat_rapat')";

if (mysqli_query($koneksi, $sql_insert)) {
    $last_id_rapat = mysqli_insert_id($koneksi);

    foreach ($peserta_arr as $id_user_peserta) {
        $id_user_peserta = mysqli_real_escape_string($koneksi, $id_user_peserta);
        $sql_peserta = "INSERT INTO peserta_rapat (id_rapat, id_user) VALUES ('$last_id_rapat', '$id_user_peserta')";
        mysqli_query($koneksi, $sql_peserta);
    }

    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Rapat berhasil ditambahkan!'];
    header("location:../admin/agenda.php");
    exit;
} else {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal menambahkan rapat: ' . mysqli_error($koneksi)];
    header("location:../admin/agenda.php");
    exit;
}
?>