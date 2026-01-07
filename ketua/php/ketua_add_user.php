<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; 

// Tambah Pengguna
if (isset($_POST['tambah_pengguna'])) {
    
    $current_user_id = $_SESSION['id_user'] ?? null;
    $unit_id_ketua = null; 

    if (empty($current_user_id)) {
        $message = "Kesalahan sesi: ID pengguna tidak ditemukan.";
        $icon = "error";
    } else {
        $unit_sql = "SELECT unit_id FROM users WHERE id_user = ?";
        $unit_stmt = $koneksi->prepare($unit_sql);
        $unit_stmt->bind_param("i", $current_user_id);
        $unit_stmt->execute();
        $unit_result = $unit_stmt->get_result();

        if ($unit_result && $unit_result->num_rows > 0) {
            $unit_row = $unit_result->fetch_assoc();
            $unit_id_ketua = $unit_row['unit_id'];
        }
        $unit_stmt->close();
    }

    $nim = trim($_POST['nim']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $password_raw = $_POST['password'];
    $email = trim($_POST['email']);
    $role = "peserta"; 
    
    $unit_id = $unit_id_ketua; 

    if (empty($unit_id)) {
        $message = "Kesalahan sistem: Anda (Ketua) tidak terasosiasi dengan Unit mana pun. Gagal menambahkan pengguna.";
        $icon = "error";
    } elseif (empty($nim) || empty($nama_lengkap) || empty($password_raw) || empty($email)) {
        $message = "NIM, Nama, Password, dan Email harus diisi!";
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
            $icon = "warning";
        } else {
            $insert_sql = "INSERT INTO users (nim, nama_lengkap, email, password, role, unit_id) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $koneksi->prepare($insert_sql);
            $insert_stmt->bind_param("sssssi", $nim, $nama_lengkap, $email, $password_hash, $role, $unit_id);
            if ($insert_stmt->execute()) {
                $message = "Pengguna Berhasil ditambahkan ke unit Anda!";
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
    header("Location: ../manage_user.php");
    exit();
}