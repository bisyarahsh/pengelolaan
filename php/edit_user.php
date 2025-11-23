<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; 

// Edit Pengguna
if (isset($_POST['edit_pengguna'])) {
    $id_user = $_POST['edit_id_user'];
    $nim = trim($_POST['edit_nim']);
    $nama_lengkap = trim($_POST['edit_nama_lengkap']);
    $password_raw = $_POST['edit_password']; // Opsional, hanya jika diisi
    $email = trim($_POST['edit_email']);
    $role = $_POST['edit_role'];
    $organisasi_id = $_POST['edit_organisasi_id'];

    if (empty($id_user) || empty($nim) || empty($nama_lengkap) || empty($email) || empty($role) || empty($organisasi_id)) {
        $message = "Semua kolom harus diisi!";
        $icon = "warning";
    } else {
        // Cek duplikasi NIM atau Email (kecuali milik user ini)
        $check_sql = "SELECT id_user FROM users WHERE (nim = ? OR email = ?) AND id_user != ?";
        $check_stmt = $koneksi->prepare($check_sql);
        $check_stmt->bind_param("ssi", $nim, $email, $id_user);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "NIM atau Email sudah terdaftar pada pengguna lain!";
            $icon = "warning"; // SweetAlert Warning untuk duplikasi
        } else {
            // Bangun Query Update
            $update_sql = "UPDATE users SET nim = ?, nama_lengkap = ?, email = ?, role = ?, organisasi_id = ?";
            $params = [$nim, $nama_lengkap, $email, $role, $organisasi_id];
            $types = "ssssi";

            if (!empty($password_raw)) {
                // Jika password diisi, update password juga
                $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
                $update_sql .= ", password = ?";
                array_splice($params, 3, 0, [$password_hash]);
                $types = "sssssi";
            }
            
            $update_sql .= " WHERE id_user = ?";
            $params[] = $id_user;
            $types .= "i";

            $update_stmt = $koneksi->prepare($update_sql);
            $update_stmt->bind_param($types, ...$params);

            if ($update_stmt->execute()) {
                $message = "Perubahan Berhasil dilakukan!";
                $icon = "success";
            } else {
                $message = "Gagal mengedit pengguna: " . $koneksi->error;
                $icon = "error";
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    }

    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/manage_user.php");
    exit();
}