<?php
// Pastikan koneksi.php berada di direktori yang benar relatif terhadap file ini
include("koneksi.php"); 

header('Content-Type: application/json');

// Cek sesi login dan role jika diperlukan (keamanan)
// ... (Tambahkan cek sesi/role di sini jika endpoint ini harus dilindungi)

// Cek apakah request adalah POST dan id_unit telah dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_unit'])) {
    $id_unit = mysqli_real_escape_string($koneksi, $_POST['id_unit']);

    // Query untuk mengambil semua pengguna dengan role 'Peserta' dari unit_id tertentu
    $sql = "SELECT id_user, nama_lengkap 
            FROM users 
            WHERE unit_id = '$id_unit' 
            AND role = 'Peserta' 
            ORDER BY nama_lengkap ASC";
    
    $result = mysqli_query($koneksi, $sql);
    
    $peserta_list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Mengembalikan id_user dan nama_lengkap
            $peserta_list[] = [
                'id_user' => $row['id_user'],
                'nama_lengkap' => $row['nama_lengkap']
            ];
        }
        // Kirim data sebagai JSON
        echo json_encode(['success' => true, 'peserta' => $peserta_list]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query database gagal: ' . mysqli_error($koneksi)]);
    }
} else {
    // Jika parameter tidak lengkap atau metode salah
    echo json_encode(['success' => false, 'error' => 'Parameter tidak lengkap atau metode request salah.']);
}
?>