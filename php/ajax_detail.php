<?php

include("../php/koneksi.php"); 

function get_rapat_detail($koneksi, $id_rapat) {
    $query = "SELECT 
                a.*, 
                o.nama_unit, 
                GROUP_CONCAT(CONCAT(u.nim, ' - ', u.nama_lengkap) SEPARATOR '|||') AS peserta_nama_lengkap,
                GROUP_CONCAT(u.id_user SEPARATOR '|||') AS peserta_id
              FROM agenda_rapat a
              LEFT JOIN unit o ON a.id_unit = o.id_unit
              LEFT JOIN peserta_rapat pr ON a.id_rapat = pr.id_rapat
              LEFT JOIN users u ON pr.id_user = u.id_user
              WHERE a.id_rapat = '$id_rapat'
              GROUP BY a.id_rapat";
              
    $result = mysqli_query($koneksi, $query);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        $data['peserta_details'] = $data['peserta_nama_lengkap'] ? explode('|||', $data['peserta_nama_lengkap']) : [];
        $data['peserta_id'] = $data['peserta_id'] ? explode('|||', $data['peserta_id']) : [];
        unset($data['peserta_nama_lengkap']);
    }

    return $data;
}

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["error" => "ID Rapat tidak ditemukan."]);
    exit;
}

$id_rapat = mysqli_real_escape_string($koneksi, $_GET['id']);
$data = get_rapat_detail($koneksi, $id_rapat);

if (!$data) {
    echo json_encode(["error" => "Data rapat tidak ditemukan untuk ID ini."]);
} else {
    echo json_encode($data);
}
exit;
?>