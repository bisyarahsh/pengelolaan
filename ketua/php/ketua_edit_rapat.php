<?php
// Memulai sesi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../php/koneksi.php"); // Pastikan path ke koneksi benar

// --- Cek Sesi dan Akses ---
if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

// ----------------------------------------------------
// START: LOGIKA PENGAMBILAN UNIT ID KETUA
// ----------------------------------------------------
$id_user_ketua = $_SESSION['id_user'];
$unit_id_ketua = $_SESSION['unit_id'] ?? null; // Coba ambil dari sesi dulu

// Fallback: Ambil dari DB jika unit_id belum ada di sesi
if (empty($unit_id_ketua)) {
    $q_unit_user = mysqli_query($koneksi, "SELECT unit_id FROM users WHERE id_user = '$id_user_ketua'");
    $r_unit_user = mysqli_fetch_assoc($q_unit_user);
    $unit_id_ketua = $r_unit_user['unit_id'] ?? null;
    if ($unit_id_ketua) {
        $_SESSION['unit_id'] = $unit_id_ketua; // Simpan ke sesi untuk penggunaan berikutnya
    }
}
// ----------------------------------------------------
// END: LOGIKA PENGAMBILAN UNIT ID KETUA
// ----------------------------------------------------


// Hanya proses jika form edit_rapat disubmit
if (isset($_POST['edit_rapat'])) {
    
    // Periksa apakah Unit Ketua berhasil ditemukan
    if (empty($unit_id_ketua)) {
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Kesalahan sistem: Unit Ketua tidak ditemukan. Gagal menyimpan perubahan.'];
         header("location:../agenda.php");
         exit;
    }
    
    // 1. Ambil dan Sanitasi Data
    $id_rapat = mysqli_real_escape_string($koneksi, $_POST['edit_id_rapat']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['edit_date']);
    $jam = mysqli_real_escape_string($koneksi, $_POST['edit_time']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['edit_judul']);
    $ruangan = mysqli_real_escape_string($koneksi, $_POST['edit_ruangan']);
    
    // --- MODIFIKASI UTAMA: OVERRIDE UNIT ID ---
    // Variabel $id_unit dari POST diabaikan dan di-override
    $id_unit = $unit_id_ketua; 
    // -----------------------------------------
    
    $peserta_arr = $_POST['edit_peserta_rapat'] ?? []; // Array ID Peserta
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['edit_keterangan']);
    
    $notulen_file_lama = mysqli_real_escape_string($koneksi, $_POST['notulen_file_lama']);
    $hapus_file_lama = $_POST['hapus_file_lama'] ?? null;
    
    $target_dir = "../notulen_files/"; 
    $new_notulen_file = $notulen_file_lama; // Default: pertahankan file lama

    // --- 2. Penanganan File Notulen ---

    // a. Cek apakah ada permintaan penghapusan file lama
    if ($hapus_file_lama == 'yes' && !empty($notulen_file_lama)) {
        if (file_exists($target_dir . $notulen_file_lama)) {
            unlink($target_dir . $notulen_file_lama); // Hapus file fisik
        }
        $new_notulen_file = ''; // Set nama file di DB menjadi kosong
    }

    // b. Cek apakah ada file baru yang diupload
    if (isset($_FILES['edit_filename']) && $_FILES['edit_filename']['error'] == 0) {
        // Jika ada upload baru:
        
        // Hapus file lama jika masih ada di disk (kecuali sudah dihapus di langkah a)
        if (!empty($notulen_file_lama) && file_exists($target_dir . $notulen_file_lama)) {
            unlink($target_dir . $notulen_file_lama);
        }
    
        $file_name_original = basename($_FILES["edit_filename"]["name"]);
        $file_tmp = $_FILES["edit_filename"]["tmp_name"];
        $file_ext = pathinfo($file_name_original, PATHINFO_EXTENSION);
    
        // Buat nama file unik
        $new_file_name = "notulen_" . $tanggal . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;
    
        // Pindahkan file yang di-upload
        if (move_uploaded_file($file_tmp, $target_file)) {
            $new_notulen_file = $new_file_name; // Simpan nama file baru
        } else {
            // Jika gagal upload, hentikan proses
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengupload file notulen baru. Perubahan data rapat tidak disimpan.'];
            header("location:../agenda.php");
            exit;
        }
    }


    // --- 3. Update Data Utama Rapat ---
    // Perhatikan variabel $id_unit yang sudah di-override
    $sql_update = "UPDATE agenda_rapat SET 
                      tanggal_rapat = '$tanggal', 
                      jam_rapat = '$jam', 
                      judul_rapat = '$judul', 
                      ruang_rapat = '$ruangan', 
                      keterangan = '$keterangan', 
                      notulen_file = '$new_notulen_file', 
                      id_unit = '$id_unit'
                      WHERE id_rapat = '$id_rapat'";

    if (mysqli_query($koneksi, $sql_update)) {
    
        // --- 4. Update Peserta Rapat (Reset dan Insert Ulang) ---
        
        // Pastikan ID Rapat valid sebelum melanjutkan operasi Peserta
        if (empty($id_rapat) || !is_numeric($id_rapat)) {
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'ID Rapat tidak valid. Perubahan peserta dibatalkan.'];
             header("location:../agenda.php");
             exit;
        }
        
        // Hapus semua peserta lama untuk ID rapat ini
        mysqli_query($koneksi, "DELETE FROM peserta_rapat WHERE id_rapat = '$id_rapat'");

        // Insert peserta baru HANYA jika ada peserta yang dipilih
        if (!empty($peserta_arr)) {
            foreach ($peserta_arr as $id_user_peserta) {
                $id_user_peserta = mysqli_real_escape_string($koneksi, $id_user_peserta);
                $sql_peserta = "INSERT INTO peserta_rapat (id_rapat, id_user) VALUES ('$id_rapat', '$id_user_peserta')";
                
                // Lakukan INSERT
                if (!mysqli_query($koneksi, $sql_peserta)) {
                     // Jika ada kegagalan INSERT, log error dan lanjutkan atau hentikan
                     error_log("Gagal insert peserta: " . mysqli_error($koneksi) . " untuk rapat ID: " . $id_rapat);
                }
            }
        }

        // Redirect dengan pesan sukses
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Agenda rapat berhasil diubah! Unit rapat ditetapkan sesuai Unit Anda.'];
        header("location:../agenda.php");
        exit;
    } else {
        // Handle error SQL
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengubah rapat: ' . mysqli_error($koneksi)];
        header("location:../agenda.php");
        exit;
    }
} else {
    // Jika diakses tanpa submit form
    header("location:../agenda.php");
    exit;
}
?>