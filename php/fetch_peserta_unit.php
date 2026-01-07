<?php
include("koneksi.php"); 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_unit'])) {
    $id_unit = mysqli_real_escape_string($koneksi, $_POST['id_unit']);

    $sql = "SELECT id_user, nama_lengkap 
            FROM users 
            WHERE unit_id = '$id_unit' 
            AND role = 'Peserta' 
            ORDER BY nama_lengkap ASC";
    
    $result = mysqli_query($koneksi, $sql);
    
    $peserta_list = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $peserta_list[] = [
                'id_user' => $row['id_user'],
                'nama_lengkap' => $row['nama_lengkap']
            ];
        }
        echo json_encode(['success' => true, 'peserta' => $peserta_list]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query database gagal: ' . mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Parameter tidak lengkap atau metode request salah.']);
}
?>