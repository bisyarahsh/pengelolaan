<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; // Pastikan path ini benar

// Tambah Organisasi
if (isset($_POST['tambah_organisasi'])) {
    $nama = trim($_POST['nama_organisasi']);

    if (!empty($nama)) {
        // Cek duplikasi
        $check_sql = "SELECT id_organisasi FROM organisasi WHERE nama_organisasi = ?";
        $check_stmt = $koneksi->prepare($check_sql);
        $check_stmt->bind_param("s", $nama);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Nama organisasi sudah ada!";
            $icon = "warning";
        } else {
            // Insert data
            $insert_sql = "INSERT INTO organisasi (nama_organisasi) VALUES (?)";
            $insert_stmt = $koneksi->prepare($insert_sql);
            $insert_stmt->bind_param("s", $nama);
            if ($insert_stmt->execute()) {
                $message = "Organisasi Berhasil ditambahkan!";
                $icon = "success";
            } else {
                $message = "Gagal menambahkan organisasi: " . $koneksi->error;
                $icon = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $message = "Nama organisasi tidak boleh kosong!";
        $icon = "warning";
    }
    // Set session flash message
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/organisasi.php");
    exit();
}