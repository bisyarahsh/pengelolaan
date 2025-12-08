<?php
    // Inisialisasi variabel koneksi
    $host = "localhost"; // Nama host database (biasanya localhost)
    $user = "root";      // Username database (sesuaikan jika perlu)
    $pass = "";          // Password database (kosong secara default di XAMPP/WAMP)
    $db   = "db_rapat"; // Nama database yang sudah dibuat

    // Melakukan koneksi ke database menggunakan MySQLi
    $koneksi = mysqli_connect($host, $user, $pass, $db);

    // Cek apakah koneksi berhasil atau gagal
    if (mysqli_connect_errno()){
        // Jika gagal, tampilkan pesan error dan hentikan script
        echo "Koneksi database gagal: " . mysqli_connect_error();
        die(); // Hentikan eksekusi script
    }
?>