<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

if (isset($_POST['edit_unit'])) {
    $id = $_POST['edit_id_unit'];
    $nama_baru = trim($_POST['edit_nama_unit']);

    if (!empty($nama_baru) && !empty($id)) {
        $check_sql = "SELECT id_unit FROM unit WHERE nama_unit = ? AND id_unit != ?";
        $check_stmt = $koneksi->prepare($check_sql);
        $check_stmt->bind_param("si", $nama_baru, $id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "Nama unit sudah ada!";
            $icon = "warning";
        } else {
            $update_sql = "UPDATE unit SET nama_unit = ? WHERE id_unit = ?";
            $update_stmt = $koneksi->prepare($update_sql);
            $update_stmt->bind_param("si", $nama_baru, $id);
            if ($update_stmt->execute()) {
                $message = "Perubahan Berhasil dilakukan!";
                $icon = "success";
            } else {
                $message = "Gagal mengedit unit: " . $koneksi->error;
                $icon = "error";
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    } else {
        $message = "Nama unit atau ID tidak valid!";
        $icon = "warning";
    }
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/unit.php");
    exit();
}