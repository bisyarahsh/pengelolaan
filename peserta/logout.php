<?php
// logout.php

// 1. Cek dan Mulai Sesi (Hanya jika belum aktif)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hapus semua variabel sesi
$_SESSION = array();

// 3. Hancurkan sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// 4. Redireksi ke halaman login
header("location:../login/login.php");
exit();
?>