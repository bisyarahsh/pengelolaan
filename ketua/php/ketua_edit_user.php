<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php'; 

$current_user_id = $_SESSION['id_user'] ?? null;
$unit_id_ketua = null; 

if (empty($current_user_id) || ($_SESSION['role'] ?? '') !== 'Ketua') {
    $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Akses ditolak atau sesi berakhir.'];
    header("Location: ../login/login.php");
    exit();
}

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

if (isset($_POST['edit_pengguna'])) {
    $id_user = $_POST['edit_id_user'];
    $nim = trim($_POST['edit_nim']);
    $nama_lengkap = trim($_POST['edit_nama_lengkap']);
    $password_raw = $_POST['edit_password'];
    $email = trim($_POST['edit_email']);

    $role = "Peserta";
    $unit_id = $unit_id_ketua;

    if (empty($unit_id_ketua)) {
        $message = "Kesalahan sistem: Anda (Ketua) tidak terasosiasi dengan Unit mana pun. Gagal mengedit pengguna.";
        $icon = "error";

    } elseif (empty($id_user) || empty($nim) || empty($nama_lengkap) || empty($email)) {
        $message = "NIM, Nama, dan Email harus diisi!";
        $icon = "warning";

    } else {
        // Cek duplikasi NIK atau Email
        $check_sql = "SELECT id_user FROM users WHERE (nim = ? OR email = ?) AND id_user != ?";
        $check_stmt = $koneksi->prepare($check_sql);
        $check_stmt->bind_param("ssi", $nim, $email, $id_user);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $message = "NIM atau Email sudah terdaftar pada pengguna lain!";
            $icon = "warning";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            if (!empty($password_raw)) {
                $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET nim = ?, nama_lengkap = ?, email = ?, password = ?, role = ?, unit_id = ? WHERE id_user = ?";
                
                $params = [$nim, $nama_lengkap, $email, $password_hash, $role, $unit_id, $id_user];
                $types = "ssssssi";
            } else {
                $update_sql = "UPDATE users SET nim = ?, nama_lengkap = ?, email = ?, role = ?, unit_id = ? WHERE id_user = ?";
                
                $params = [$nim, $nama_lengkap, $email, $role, $unit_id, $id_user];
                $types = "sssssi"; // s(nim), s(nama), s(email), s(role), s(unit_id), i(id_user)
            }
            
            $update_stmt = $koneksi->prepare($update_sql);

            if ($update_stmt === false) {
                 $message = "Gagal mempersiapkan statement SQL: " . $koneksi->error;
                 $icon = "error";
            } else {
                $update_stmt->bind_param($types, ...$params);

                if ($update_stmt->execute()) {
                    $message = "Perubahan Berhasil dilakukan! Pengguna kini terdaftar sebagai Peserta di unit Anda.";
                    $icon = "success";
                } else {
                    $message = "Gagal mengedit pengguna: " . $update_stmt->error;
                    $icon = "error";
                }
                $update_stmt->close();
            }
        }
    }

    $_SESSION['alert'] = ['icon' => $icon, 'message' => $message];
    header("Location: ../manage_user.php"); 
    exit();
}