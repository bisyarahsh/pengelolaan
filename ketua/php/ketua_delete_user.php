<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; 

// Hapus Pengguna
if (isset($_POST['hapus_pengguna'])) {
    $id_user = $_POST['hapus_id_user']; 

    if (!empty($id_user)) {
        $delete_sql = "DELETE FROM users WHERE id_user = ?";
        $delete_stmt = $koneksi->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id_user);
        if ($delete_stmt->execute()) {
            $message = "Pengguna Berhasil dihapus!";
            $icon = "success";
        } else {
            $message = "Gagal menghapus pengguna: " . $koneksi->error;
            $icon = "error";
        }
        $delete_stmt->close();
    } else {
        $message = "ID pengguna tidak valid!";
        $icon = "warning";
    }
    
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../manage_user.php");
    exit();
}