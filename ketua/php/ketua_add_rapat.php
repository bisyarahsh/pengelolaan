<?php
// FILE: ../php/tambah_rapat.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; // Path koneksi.php dari folder php

// --- Set Zona Waktu ke WIB ---
date_default_timezone_set('Asia/Jakarta'); 
// --- End Set Zona Waktu ---

// Cek Sesi dan Role (Hanya Ketua yang boleh menambah)
if ($_SESSION['status'] != "login" || strtolower($_SESSION['role']) != "ketua" || !isset($_POST['tambah_rapat'])) {
    header("location:../login/login.php?error=noaccess");
    exit;
}

$id_pembuat_rapat = $_SESSION['id_user'];

// --- PERUBAHAN UTAMA: Ambil unit_id dari sesi ---
// Ambil unit_id Ketua dari sesi. Asumsi unit_id sudah tersimpan di $_SESSION
// Jika unit_id tidak ada di sesi, Anda harus mengambilnya dari database
if (isset($_SESSION['unit_id'])) {
    $id_unit = mysqli_real_escape_string($koneksi, $_SESSION['unit_id']);
} else {
    // FALLBACK: Ambil dari database jika tidak ada di sesi
    $q_unit = mysqli_query($koneksi, "SELECT unit_id FROM users WHERE id_user = '$id_pembuat_rapat'");
    $r_unit = mysqli_fetch_assoc($q_unit);
    $id_unit = mysqli_real_escape_string($koneksi, $r_unit['unit_id'] ?? NULL);
    // Jika unit_id masih null atau tidak valid, berikan pesan error dan exit
    if (empty($id_unit)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal menentukan unit: Ketua tidak terasosiasi dengan unit.'];
        header("location:../agenda.php"); // Ganti redirect ke ketua/agenda.php
        exit;
    }
}
// Nilai dari $_POST['unit'] (jika ada) diabaikan.
// --------------------------------------------------

$tanggal = mysqli_real_escape_string($koneksi, $_POST['date']);
$jam = mysqli_real_escape_string($koneksi, $_POST['time']);
$judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
$ruangan = mysqli_real_escape_string($koneksi, $_POST['ruangan']);
$peserta_arr = $_POST['peserta_rapat'] ?? []; // Array ID Peserta
$keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
$notulen_file = '';

// Penanganan File Upload
if (isset($_FILES['filename']) && $_FILES['filename']['error'] == 0) {
    
    // Tentukan lokasi penyimpanan file (pastikan folder ini ada dan bisa ditulis)
    $target_dir = "../notulen_files/"; 

    // Pastikan folder target ada
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); 
    }

    $file_name_original = basename($_FILES["filename"]["name"]);
    $file_tmp = $_FILES["filename"]["tmp_name"];
    $file_ext = pathinfo($file_name_original, PATHINFO_EXTENSION);

    // Sanitasi dan buat nama file unik
    $new_file_name = "notulen_" . $tanggal . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;

    // Pindahkan file yang di-upload dari lokasi sementara ke lokasi target
    if (move_uploaded_file($file_tmp, $target_file)) {
        // Jika berhasil dipindahkan, simpan nama file baru ke database
        $notulen_file = $new_file_name; 
    } else {
        // Logika sederhana jika upload file gagal
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengupload file notulen. Cek izin folder!'];
        header("location:../agenda.php"); // Ganti redirect ke ketua/agenda.php
        exit;
    }
}

$sql_insert = "INSERT INTO agenda_rapat (tanggal_rapat, jam_rapat, judul_rapat, ruang_rapat, keterangan, notulen_file, id_unit, id_pembuat) 
            VALUES ('$tanggal', '$jam', '$judul', '$ruangan', '$keterangan', '$notulen_file', '$id_unit', '$id_pembuat_rapat')";

if (mysqli_query($koneksi, $sql_insert)) {
    $last_id_rapat = mysqli_insert_id($koneksi);

    // Insert Peserta Rapat
    foreach ($peserta_arr as $id_user_peserta) {
        $id_user_peserta = mysqli_real_escape_string($koneksi, $id_user_peserta);
        $sql_peserta = "INSERT INTO peserta_rapat (id_rapat, id_user) VALUES ('$last_id_rapat', '$id_user_peserta')";
        mysqli_query($koneksi, $sql_peserta);
    }

    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Rapat berhasil ditambahkan!'];
    header("location:../agenda.php"); // Ganti redirect ke ketua/agenda.php
    exit;
} else {
    // Handle error
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal menambahkan rapat: ' . mysqli_error($koneksi)];
    header("location:../agenda.php"); // Ganti redirect ke ketua/agenda.php
    exit;
}
?>