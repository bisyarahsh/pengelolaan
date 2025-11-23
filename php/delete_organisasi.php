<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; // Pastikan path ini benar

// Hapus Organisasi
if (isset($_POST['hapus_organisasi'])) {
    $id = $_POST['hapus_id_organisasi'];

    if (!empty($id)) {
        $delete_sql = "DELETE FROM organisasi WHERE id_organisasi = ?";
        $delete_stmt = $koneksi->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        if ($delete_stmt->execute()) {
            $message = "Organisasi Berhasil dihapus!";
            $icon = "success";
        } else {
            $message = "Gagal menghapus organisasi: " . $koneksi->error;
            $icon = "error";
        }
        $delete_stmt->close();
    } else {
        $message = "ID organisasi tidak valid!";
        $icon = "warning";
    }
    // Set session flash message
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/organisasi.php");
    exit();
}