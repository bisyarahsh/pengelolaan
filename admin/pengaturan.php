<?php
include '../php/koneksi.php'; 
session_start();

if ($_SESSION['status'] != "login") {
    exit;
}
if ($_SESSION['role'] != "Ketua") {
      exit;
}

if (isset($_SESSION['id_user'])) {
    $user_id = $_SESSION['id_user'];
} else {
    header("Location: ../login/login.php");
    exit();
}

$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']);

// Proses Ganti Kata Sandi
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi Kolom Kosong
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        $_SESSION['alert'] = ['icon' => 'warning', 'message' => 'Semua kolom wajib diisi.'];
        header("Location: pengaturan.php");
        exit();
    }

    // Validasi Password Baru dan Konfirmasi
    if ($password_baru !== $konfirmasi_password) {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Password Baru dan Konfirmasi Password tidak cocok.'];
        header("Location: pengaturan.php");
        exit();
    }
    
    // Verifikasi Password Lama
    $sql_check = "SELECT password FROM users WHERE id_user = ?";
    $stmt_check = $koneksi->prepare($sql_check);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $user = $result_check->fetch_assoc();
    $stmt_check->close();

    if (!$user) {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Sesi pengguna tidak valid. Silakan login ulang.'];
        header("Location: pengaturan.php");
        exit();
    }
    
    // Verifikasi Password Lama menggunakan password_verify()
    if (!password_verify($password_lama, $user['password'])) {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Password Lama salah.'];
        header("Location: pengaturan.php");
        exit();
    }
    
    // Validasi Password Baru sama dengan yang Lama (Tambahan yang diminta)
    if (password_verify($password_baru, $user['password'])) {
        $_SESSION['alert'] = ['icon' => 'warning', 'message' => 'Password baru tidak boleh sama dengan password lama.'];
        header("Location: pengaturan.php");
        exit();
    }

    // Update Password Baru
    // Gunakan password_hash() untuk hash password baru
    $password_hash_baru = password_hash($password_baru, PASSWORD_DEFAULT); 

    $sql_update = "UPDATE users SET password = ? WHERE id_user = ?";
    $stmt_update = $koneksi->prepare($sql_update);
    $stmt_update->bind_param("si", $password_hash_baru, $user_id);

    if ($stmt_update->execute()) {
        $_SESSION['alert'] = ['icon' => 'success', 'message' => 'Password berhasil diubah!'];
        // Tutup statement sebelum redirect
        $stmt_update->close(); 
        $koneksi->close(); 
        header("Location: pengaturan.php");
        exit();
    } else {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Gagal mengubah password: ' . $koneksi->error];
        // Tutup statement sebelum redirect
        $stmt_update->close(); 
        $koneksi->close(); 
        header("Location: pengaturan.php");
        exit();
    }
}

// Tutup koneksi jika tidak ada post (hanya menampilkan halaman)
if ($koneksi->ping()) {
    $koneksi->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css"/>
    <link rel="stylesheet" href="../assets/admin.css" />
    <title>Rapatin</title>
</head>
<body>
    <section id="sidebar">
      <a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class="ps-5"></i> Rapatin</a>
      <a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
      <ul class="side-menu" data-aos="fade-right">
        <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
        <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
        <li><a href="organisasi.php"><i class="fa-solid fa-users icon"></i> Organisasi</a></li>
        <li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
        <li><a href="pengaturan.php" class="active"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Logout</a></li>
      </ul>
    </section>
    <section id="content">
      <nav class="atas">
        <i data-aos="fade-right" class="fa-solid fa-bars toggle-sidebar"></i>
      </nav>
      <main>
        <div data-aos="fade-down" class="pengaturan bg-light mx-auto">
          <div class="tableheader">
            <h2>Ganti Kata Sandi</h2>
          </div>
          <form action="" method="POST"> 
            <div class="mb-3">
              <label class="mb-2" for="password_lama">Kata Sandi Lama</label>
              <input class="form-control" type="password" name="password_lama" id="password_lama" placeholder="Masukkan Password Lama Anda..." required/>
            </div>
            <div class="mb-3">
              <label class="mb-2" for="password_baru">Kata Sandi Baru</label>
              <input class="form-control" type="password" name="password_baru" id="password_baru" placeholder="Masukkan Password Baru Anda..." required/>
            </div>
            <div class="mb-3">
              <label class="mb-2" for="konfirmasi_password">Konfirmasi Kata Sandi Baru</label>
              <input class="form-control" type="password" name="konfirmasi_password" id="konfirmasi_password" placeholder="Konfirmasi Password Baru..." required/>
            </div>
            <button type="submit" name="ganti_password" class="btn btn-primary d-block ms-auto px-3">
              Simpan
            </button>
          </form>
        </div>
      </main>
      </section>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/admin.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    AOS.init();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    const alertData = <?= json_encode($alert); ?>;
    
    if (alertData) {
        Swal.fire({
            title: alertData.icon === 'success' ? "Berhasil!" : "Gagal!",
            text: alertData.message,
            icon: alertData.icon
        });
    }

    // Fungsi edit() yang lama telah dihapus karena SweetAlert dipicu oleh PHP
</script>
</body>
</html>