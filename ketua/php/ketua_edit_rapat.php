<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../php/koneksi.php");

// --- Cek Sesi dan Akses ---
if ($_SESSION['status'] != "login" || !isset($_SESSION['id_user'])) {
    header("location:../login/login.php");
    exit;
}

$id_user_ketua = $_SESSION['id_user'];
$unit_id_ketua = $_SESSION['unit_id'] ?? null;

if (empty($unit_id_ketua)) {
    $q_unit_user = mysqli_query($koneksi, "SELECT unit_id FROM users WHERE id_user = '$id_user_ketua'");
    $r_unit_user = mysqli_fetch_assoc($q_unit_user);
    $unit_id_ketua = $r_unit_user['unit_id'] ?? null;
    if ($unit_id_ketua) {
        $_SESSION['unit_id'] = $unit_id_ketua;
    }
}

if (isset($_POST['edit_rapat'])) {
    
    if (empty($unit_id_ketua)) {
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Kesalahan sistem: Unit Ketua tidak ditemukan. Gagal menyimpan perubahan.'];
         header("location:../agenda.php");
         exit;
    }
    
    $id_rapat = mysqli_real_escape_string($koneksi, $_POST['edit_id_rapat']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['edit_date']);
    $jam = mysqli_real_escape_string($koneksi, $_POST['edit_time']);
    $judul = mysqli_real_escape_string($koneksi, $_POST['edit_judul']);
    $ruangan = mysqli_real_escape_string($koneksi, $_POST['edit_ruangan']);
    
    $id_unit = $unit_id_ketua; 
    
    $peserta_arr = $_POST['edit_peserta_rapat'] ?? [];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['edit_keterangan']);
    
    $notulen_file_lama = mysqli_real_escape_string($koneksi, $_POST['notulen_file_lama']);
    $hapus_file_lama = $_POST['hapus_file_lama'] ?? null;
    
    $target_dir = "../notulen_files/"; 
    $new_notulen_file = $notulen_file_lama;

    if ($hapus_file_lama == 'yes' && !empty($notulen_file_lama)) {
        if (file_exists($target_dir . $notulen_file_lama)) {
            unlink($target_dir . $notulen_file_lama);
        }
        $new_notulen_file = '';
    }

    if (isset($_FILES['edit_filename']) && $_FILES['edit_filename']['error'] == 0) {
        
        if (!empty($notulen_file_lama) && file_exists($target_dir . $notulen_file_lama)) {
            unlink($target_dir . $notulen_file_lama);
        }
    
        $file_name_original = basename($_FILES["edit_filename"]["name"]);
        $file_tmp = $_FILES["edit_filename"]["tmp_name"];
        $file_ext = pathinfo($file_name_original, PATHINFO_EXTENSION);
    
        $new_file_name = "notulen_" . $tanggal . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;
    
        if (move_uploaded_file($file_tmp, $target_file)) {
            $new_notulen_file = $new_file_name;
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengupload file notulen baru. Perubahan data rapat tidak disimpan.'];
            header("location:../agenda.php");
            exit;
        }
    }

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
        if (empty($id_rapat) || !is_numeric($id_rapat)) {
             $_SESSION['alert'] = ['type' => 'error', 'message' => 'ID Rapat tidak valid. Perubahan peserta dibatalkan.'];
             header("location:../agenda.php");
             exit;
        }
        
        mysqli_query($koneksi, "DELETE FROM peserta_rapat WHERE id_rapat = '$id_rapat'");

        if (!empty($peserta_arr)) {
            foreach ($peserta_arr as $id_user_peserta) {
                $id_user_peserta = mysqli_real_escape_string($koneksi, $id_user_peserta);
                $sql_peserta = "INSERT INTO peserta_rapat (id_rapat, id_user) VALUES ('$id_rapat', '$id_user_peserta')";
                
                if (!mysqli_query($koneksi, $sql_peserta)) {
                     error_log("Gagal insert peserta: " . mysqli_error($koneksi) . " untuk rapat ID: " . $id_rapat);
                }
            }
        }
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Agenda rapat berhasil diubah! Unit rapat ditetapkan sesuai Unit Anda.'];
        header("location:../agenda.php");
        exit;
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengubah rapat: ' . mysqli_error($koneksi)];
        header("location:../agenda.php");
        exit;
    }
} else {
    header("location:../agenda.php");
    exit;
}
?>