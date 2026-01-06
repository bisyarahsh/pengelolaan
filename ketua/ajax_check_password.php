<?php
include '../php/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || !isset($_POST['password_lama'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$user_id = $_SESSION['id_user'];
$password_lama = $_POST['password_lama'];

$sql = "SELECT password FROM users WHERE id_user = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password_lama, $user['password'])) {
    echo json_encode(['status' => 'valid']);
} else {
    echo json_encode(['status' => 'invalid']);
}
?>