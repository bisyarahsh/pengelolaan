<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; // Pastikan path ini benar

// Edit Organisasi
if (isset($_POST['edit_organisasi'])) {
    $id = $_POST['edit_id_organisasi'];
    $nama_baru = trim($_POST['edit_nama_organisasi']);

    if (!empty($nama_baru) && !empty($id)) {
        // Cek duplikasi, kecuali untuk ID yang sedang diedit
        $check_sql = "SELECT id_organisasi FROM organisasi WHERE nama_organisasi = ? AND id_organisasi != ?";
        $check_stmt = $koneksi->prepare($check_sql);
        $check_stmt->bind_param("si", $nama_baru, $id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Nama organisasi sudah ada!";
            $icon = "warning";
        } else {
            // Update data
            $update_sql = "UPDATE organisasi SET nama_organisasi = ? WHERE id_organisasi = ?";
            $update_stmt = $koneksi->prepare($update_sql);
            $update_stmt->bind_param("si", $nama_baru, $id);
            if ($update_stmt->execute()) {
                $message = "Perubahan Berhasil dilakukan!";
                $icon = "success";
            } else {
                $message = "Gagal mengedit organisasi: " . $koneksi->error;
                $icon = "error";
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    } else {
        $message = "Nama organisasi atau ID tidak valid!";
        $icon = "warning";
    }
    // Set session flash message
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/organisasi.php");
    exit();
}