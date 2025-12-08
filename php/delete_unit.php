<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; // Pastikan path ini benar

// Hapus unit
if (isset($_POST['hapus_unit'])) {
    $id = $_POST['hapus_id_unit'];

    if (!empty($id)) {
        $delete_sql = "DELETE FROM unit WHERE id_unit = ?";
        $delete_stmt = $koneksi->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        if ($delete_stmt->execute()) {
            $message = "Unit Berhasil dihapus!";
            $icon = "success";
        } else {
            $message = "Gagal menghapus unit: " . $koneksi->error;
            $icon = "error";
        }
        $delete_stmt->close();
    } else {
        $message = "ID unit tidak valid!";
        $icon = "warning";
    }
    // Set session flash message
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/unit.php");
    exit();
}