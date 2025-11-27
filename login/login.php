<?php
session_start();
include("../php/koneksi.php");

// Cek apakah user sudah login, jika ya, arahkan ke halaman sesuai role
if(isset($_SESSION['role'])){
    $user_role = $_SESSION['role'];
    if ($user_role == "admin") {
        header("location:../admin/agenda.php");
        exit();
    } else if ($user_role == "peserta") {
        header("location:../peserta/dashboard.php");
        exit();
    }
}

// Inisialisasi variabel
$email = $_POST['email'] ?? ''; // Ambil email dari post, jika ada
$password = $_POST['password'] ?? '';
$err = "";
$r1 = array();

// Variabel untuk menampung pesan error spesifik
$email_err = "";
$password_err = "";

if(isset($_POST['login'])){
    // Validasi input kosong (Front-end sudah menangani, tapi perlu penanganan di Back-end)
    if(empty($email)){
        $email_err = "Email tidak boleh kosong.";
    }
    if(empty($password)){
        $password_err = "Kata Sandi tidak boleh kosong.";
    }

    if(empty($email_err) && empty($password_err)){
        $sql1 = "SELECT * FROM users WHERE email = '$email'";
        $q1 = mysqli_query($koneksi, $sql1);
        $r1 = mysqli_fetch_array($q1);

        if(!$r1){
            // Email tidak ditemukan
            $email_err = "Email tidak terdaftar.";
        }
        // Pastikan $r1 ada sebelum mencoba mengakses password
        else if(isset($r1['password']) && !password_verify($password, $r1['password'])){
            // Kata Sandi salah
            $password_err = "Kata Sandi salah.";
        }
    }

    // Jika tidak ada error (Login berhasil)
    if(empty($email_err) && empty($password_err)){
        $user = $r1["id_user"];
        $user_role = $r1['role'];
        $_SESSION['id_user'] = $user;
        $_SESSION['email'] = $r1['email'];
        $_SESSION['role'] = $user_role;
        $_SESSION['status'] = "login";
        $_SESSION['pesan_sukses'] = "Selamat datang! Anda berhasil masuk sebagai **" . $user_role . "**.";

        // Clear email sementara dari sesi jika berhasil
        unset($_SESSION['temp_email']);

        if ($user_role == "Ketua") {
            header("location:../admin/agenda.php");
            exit();
        } else if ($user_role == "Peserta") {
            header("location:../peserta/dashboard.php");
            exit();
        }
    } else {
        // Jika ada error, simpan email ke sesi agar input tetap terisi
        $_SESSION['temp_email'] = $email;
    }
}

// Ambil email dari sesi jika ada error sebelumnya
$email_value = $_SESSION['temp_email'] ?? '';
// Hapus temp_email agar tidak muncul lagi pada refresh normal
if(isset($_SESSION['temp_email']) && !isset($_POST['login'])){
    unset($_SESSION['temp_email']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
    <!-- Bootstrap CSS (v5.3) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
</head>
<body>

  <div class="login-card d-flex flex-row align-items-stretch">
    <!-- left panel (visual) -->
    <div class="left-panel col-lg-5 d-none d-lg-flex position-relative">
      <div>
        <div class="brand-logo mb-3">
          <i class="bi bi-calendar-check-fill" style="font-size:1.25rem"></i>
        </div>
        <h3>Rapatin</h3>
        <p class="mb-4">Kelola setiap rapat dengan lebih teratur dan efisien.  
      Membantu Anda mencatat agenda, peserta, serta hasil rapat.</p>

        <ul class="list-unstyled small opacity-85">
          <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Membuat dan mengirim undangan rapat dengan mudah</li>
          <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Mencatat keputusan dan tindak lanjut rapat</li>
          <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Ekspor notulen ke PDF/Word</li>
        </ul>
      </div>
      <div class="decor-circles"></div>
    </div>

    <!-- form panel -->
    <div class="form-panel col-lg-7">
      <div class="p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center gap-3">
            <div class="brand-logo d-lg-none bg-primary text-white"><i class="bi bi-calendar-check-fill"></i></div>
            <div>
              <h5 class="mb-0">Masuk ke Aplikasi Rapat</h5>
              <small class="text-muted">Silakan masuk menggunakan akun Anda</small>
            </div>
          </div>
        </div>

        <form action="" method="post" id="loginForm" class="needs-validation" novalidate>
          <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input name="email" type="email" class="form-control <?php echo !empty($email_err) ? 'is-invalid' : ''; ?>" 
                       id="email" placeholder="Masukkan email" required 
                       value="<?php echo htmlspecialchars($email_value); ?>">
                <div class="invalid-feedback">
                    <?php echo !empty($email_err) ? $email_err : 'Masukkan email yang valid.'; ?>
                </div>
          </div>

          <div class="mb-3">
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="input-group">
                    <input type="password" name="password" class="form-control <?php echo !empty($password_err) ? 'is-invalid' : ''; ?>" 
                           id="password" placeholder="Masukkan kata sandi" required>
                    <span class="input-group-text show-pass" id="togglePassword" title="Tampilkan / sembunyikan"><i class="bi bi-eye"></i></span>
                    <div class="invalid-feedback">
                        <?php echo !empty($password_err) ? $password_err : 'Kata sandi harus diisi.'; ?>
                    </div>
                </div>
          </div>
          <button type="submit" name="login" class="btn btn-primary w-100 mb-3" value="Login">Masuk</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Form validation
    (function () {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()

    // Toggle show/hide password
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    togglePassword.addEventListener('click', function (){
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.innerHTML = type === 'text' ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    })
  </script>
</body>
</html>
