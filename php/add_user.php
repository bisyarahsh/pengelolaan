<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; 

// Tambah Pengguna
if (isset($_POST['tambah_pengguna'])) {
    $nim = trim($_POST['nim']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $password_raw = $_POST['password'];
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $organisasi_id = $_POST['organisasi_id'];

    // Validasi input
    if (empty($nim) || empty($nama_lengkap) || empty($password_raw) || empty($email) || empty($role) || empty($organisasi_id)) {
        $message = "Semua kolom harus diisi!";
        $icon = "warning";
    } else {
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

        // Cek duplikasi NIM atau Email
        $check_sql = "SELECT id_user FROM users WHERE nim = ? OR email = ?";
        $check_stmt = $koneksi->prepare($check_sql);
        $check_stmt->bind_param("ss", $nim, $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "NIM atau Email sudah terdaftar!";
            $icon = "warning"; // SweetAlert Warning untuk duplikasi
        } else {
            // Insert data
            $insert_sql = "INSERT INTO users (nim, nama_lengkap, email, password, role, organisasi_id) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $koneksi->prepare($insert_sql);
            $insert_stmt->bind_param("sssssi", $nim, $nama_lengkap, $email, $password_hash, $role, $organisasi_id);
            if ($insert_stmt->execute()) {
                $message = "Pengguna Berhasil ditambahkan!";
                $icon = "success";
            } else {
                $message = "Gagal menambahkan pengguna: " . $koneksi->error;
                $icon = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    
    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../admin/manage_user.php");
    exit();
}