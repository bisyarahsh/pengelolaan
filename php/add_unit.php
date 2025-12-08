<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; // Pastikan path ini benar

// Tambah unit
if (isset($_POST['tambah_unit'])) {
    $nama = trim($_POST['nama_unit']);

    if (!empty($nama)) {
        // Cek duplikasi
        $check_sql = "SELECT id_unit FROM unit WHERE nama_unit = ?";
        $check_stmt = $koneksi->prepare($check_sql);
        $check_stmt->bind_param("s", $nama);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Nama unit sudah ada!";
            $icon = "warning";
        } else {
            // Insert data
            $insert_sql = "INSERT INTO unit (nama_unit) VALUES (?)";
            $insert_stmt = $koneksi->prepare($insert_sql);
            $insert_stmt->bind_param("s", $nama);
            if ($insert_stmt->execute()) {
                $message = "Unit Berhasil ditambahkan!";
                $icon = "success";
            } else {
                $message = "Gagal menambahkan unit: " . $koneksi->error;
                $icon = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $message = "Nama unit tidak boleh kosong!";
        $icon = "warning";
    }
    // Set session flash message
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/unit.php");
    exit();
}