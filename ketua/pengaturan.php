<?php
include '../php/koneksi.php'; 
session_start();

// Cek Sesi
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

// Ambil Data User Saat Ini
$sql_user = "SELECT u.*, o.nama_unit FROM users u LEFT JOIN unit o ON u.unit_id = o.id_unit
             WHERE u.id_user = ?";
$stmt_user = $koneksi->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$current_user = $stmt_user->get_result()->fetch_assoc();

// Gambar Profil
$nama_user = $current_user['nama_lengkap'];
$file_foto = $current_user['profile_pic'];
$path_foto = "../assets/img/profile/" . $file_foto;

if (!empty($file_foto) && file_exists($path_foto)) {
    $profile_img_src = $path_foto;
} else {
    $profile_img_src = "https://ui-avatars.com/api/?name=" . urlencode($nama_user) . "&background=4e73df&color=fff&size=512&font-size=0.4&bold=true";
}

// DATA USER, FOTO & INISIAL
$id_user_login = $_SESSION['id_user'];

$sql_user_info = "SELECT nama_lengkap, profile_pic FROM users WHERE id_user = '$id_user_login'"; 
$q_user_info = mysqli_query($koneksi, $sql_user_info);
$d_user_info = mysqli_fetch_assoc($q_user_info);

$nama_user = $d_user_info['nama_lengkap'] ?? "Ketua Prodi";
$foto_db   = $d_user_info['profile_pic'] ?? null;

$path_foto_target = "../assets/img/profile/" . $foto_db;
$tampilkan_foto = false;

if (!empty($foto_db) && file_exists($path_foto_target)) {
    $tampilkan_foto = true;
}

// Membuat Profil Inisial
$words = explode(" ", $nama_user);
$initials = "";
if (count($words) >= 1) {
    $initials .= strtoupper(substr($words[0], 0, 1));
    if (count($words) > 1) {
        $initials .= strtoupper(substr(end($words), 0, 1));
    }
} else {
    $initials = "KP";
}

// Update Profil
if (isset($_POST['update_profil'])) {
    $email_baru = trim($_POST['email']);
    $upload_ok = true;
    
    if (empty($email_baru)) {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Email tidak boleh kosong!'];
        $upload_ok = false;
    } elseif (!filter_var($email_baru, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Format email tidak valid!'];
        $upload_ok = false;
    }

    if ($upload_ok) {
        $img_name = $current_user['profile_pic']; 

        if (!empty($_FILES['foto']['name'])) {
            $target_dir = "../assets/img/profile/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

            $file_extension = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Format file harus JPG, JPEG, atau PNG!'];
                $upload_ok = false;
            } elseif ($_FILES["foto"]["size"] > 2000000) {
                $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Ukuran file terlalu besar (Maksimal 2MB)!'];
                $upload_ok = false;
            } else {
                $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
                if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_dir . $new_filename)) {
                    $img_name = $new_filename;
                    // Hapus foto lama
                    if (!empty($current_user['profile_pic']) && file_exists($target_dir . $current_user['profile_pic'])) {
                        unlink($target_dir . $current_user['profile_pic']);
                    }
                } else {
                    $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Gagal mengupload gambar!'];
                    $upload_ok = false;
                }
            }
        }
    }

    if ($upload_ok) {
        $sql_update_profile = "UPDATE users SET email = ?, profile_pic = ? WHERE id_user = ?";
        $stmt_update = $koneksi->prepare($sql_update_profile);
        $stmt_update->bind_param("ssi", $email_baru, $img_name, $user_id);
        
        if ($stmt_update->execute()) {
            $_SESSION['alert'] = ['icon' => 'success', 'message' => 'Profil berhasil diperbarui!'];
            header("Location: pengaturan.php"); 
            exit();
        } else {
            $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Terjadi kesalahan database!'];
        }
    }
}

// Ganti Kata Sandi
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    if (!password_verify($password_lama, $current_user['password'])) {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Kata Sandi lama tidak sesuai.'];
        $_SESSION['active_tab'] = 'password';
    } else if ($password_baru !== $konfirmasi_password) {
        $_SESSION['alert'] = ['icon' => 'error', 'message' => 'Konfirmasi kata sandi tidak cocok.'];
        $_SESSION['active_tab'] = 'password';
    } else {
        $password_hash_baru = password_hash($password_baru, PASSWORD_DEFAULT); 
        $sql_pw = "UPDATE users SET password = ? WHERE id_user = ?";
        $stmt_pw = $koneksi->prepare($sql_pw);
        $stmt_pw->bind_param("si", $password_hash_baru, $user_id);
        
        if ($stmt_pw->execute()) {
            $_SESSION['alert'] = ['icon' => 'success', 'message' => 'Kata Sandi berhasil diubah!'];
            $_SESSION['active_tab'] = 'password';
            header("Location: pengaturan.php");
            exit();
        }
    }
}

$active_tab = isset($_SESSION['active_tab']) ? $_SESSION['active_tab'] : 'profile';
unset($_SESSION['active_tab']); 

if ($koneksi->ping()) { $koneksi->close(); }
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
    <title>Pengaturan | Ketua - Rapatin</title>
	<link rel="shortcut icon" href="../assets/logo/logo.png">
    <style>
        .form-control { 
        border-radius: 10px; 
        padding: 10px 15px; 
        border: 1px solid #e3e6f0; 
    }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <section id="sidebar">
        <a href="../index.html" data-aos="fade-down" class="logo ps-3"><i class="ps-5"></i> Rapatin</a>
        <a href="../index.html" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="dashboard.php"><i class="fa-solid fa-home icon"></i> Dasbor</a></li>
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="manage_user.php"><i class="fa-solid fa-user icon"></i> Anggota</a></li>
            <li><a href="pengaturan.php" class="active"><i class="fa-solid fa-gear icon"></i> Pengaturan</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
        </ul>
    </section>
    <!-- End Sidebar -->

    <section id="content">
      <!-- Navbar -->
		<nav class="atas mb-4 shadow">
            <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar'></i>

            <div class="d-flex align-items-center" data-aos="fade-left">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle hide-arrow" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 d-none d-lg-inline text-gray-600 small fw-bold">
                            <?php echo $nama_user; ?>
                        </span>

                        <?php if ($tampilkan_foto): ?>
                            <img src="../assets/img/profile/<?= $foto_db; ?>" 
                                 alt="Profile" 
                                 class="rounded-circle object-fit-cover shadow-sm" 
                                 style="width: 40px; height: 40px;">
                        <?php else: ?>
                            <div class="img-profile-initials">
                                <?php echo $initials; ?>
                            </div>
                        <?php endif; ?>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="profileDropdown">
                        <li>
                            <a class="dropdown-item" href="pengaturan.php">
                                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400 me-2"></i>
                                Pengaturan
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="logout.php" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400 me-2"></i>
                                Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
		<!-- End Navbar -->
        
        <!-- Main -->
        <main>
            <div data-aos="fade-down" class="container-fluid py-2">
                <div class="row g-4">
                    <!-- Preview -->
                    <div class="col-lg-4">
                        <div class="card card-custom text-center h-100">
                            <div class="profile-banner"></div>
                            <div class="card-body pt-0">
                                <div class="profile-pic-container">
                                    <div class="profile-pic-wrapper">
                                        <img src="<?= $profile_img_src; ?>" id="sidebarProfilePic" alt="Profile">
                                    </div>
                                </div>

                                <h5 class="fw-bold mt-3 text-dark"><?= htmlspecialchars($current_user['nama_lengkap']); ?></h5>

                                <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-user-tag me-1"></i> 
                                        <?= ($current_user['role'] == 'Ketua') ? 'Ketua Prodi' : $current_user['role']; ?>
                                    </span>
                                </div>

                                <hr class="my-4" style="opacity: 0.1;">

                                <div class="row text-start px-3">
                                    <div class="col-12 mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Unit Kerja</small>
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="bg-light rounded-circle p-2 me-3 text-primary">
                                                <i class="fa-solid fa-building"></i>
                                            </div>
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($current_user['nama_unit'] ?? '-'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">Email Terdaftar</small>
                                        <div class="d-flex align-items-center mt-1">
                                            <div class="bg-light rounded-circle p-2 me-3 text-success">
                                                <i class="fa-solid fa-envelope"></i>
                                            </div>
                                            <span class="fw-semibold text-dark text-truncate"><?= htmlspecialchars($current_user['email']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Preview -->

                    <!-- Content Tab -->
                    <div class="col-lg-8">
                        <div class="card card-custom h-100">
                            <!-- Tab Switch Profil dan Keamanan -->
                            <div class="card-header bg-white border-0 py-4 px-4">
                                <ul class="nav nav-pills" id="settingTabs" role="tablist">
                                    <li class="nav-item me-2">
                                        <button class="nav-link <?= $active_tab == 'profile' ? 'active' : ''; ?>" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                            <i class="fa-solid fa-user-pen me-2"></i>Edit Profil
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link <?= $active_tab == 'password' ? 'active' : ''; ?>" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                            <i class="fa-solid fa-shield-halved me-2"></i>Keamanan
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <!-- End Tab Switch Profil dan Keamanan -->
                            
                            <!-- Tab Profil dan Keamanan -->
                            <div class="card-body px-4 pb-4">
                                <div class="tab-content" id="settingTabsContent">
                                    <!-- Tab Profil -->
                                    <div class="tab-pane fade <?= $active_tab == 'profile' ? 'show active' : ''; ?>" id="profile" role="tabpanel">
                                        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" id="formProfil" novalidate>
                                            <h6 class="text-uppercase text-muted small fw-bold mb-4">Informasi Dasar</h6>

                                            <div class="row align-items-center mb-5">
                                                <div class="col-auto">
                                                    <div class="position-relative" style="width: 100px; height: 100px;">
                                                        <img src="<?= $profile_img_src; ?>" id="previewImg" class="rounded-circle w-100 h-100 shadow-sm object-fit-cover" alt="Preview">
                                                        <div class="loading-overlay rounded-circle" id="imgLoading">
                                                            <div class="loading-spinner"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <label for="foto" class="btn btn-outline-primary btn-upload mb-2 shadow-sm">
                                                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Unggah Foto
                                                    </label>
                                                    <input type="file" name="foto" id="foto" class="d-none" accept="image/png, image/jpeg, image/jpg">
                                                    <div class="text-muted small" style="font-size: 0.8rem;">
                                                        JPG/PNG, Maksimal 2MB. Foto persegi direkomendasikan.
                                                    </div>
                                                    <div id="fotoFeedback" class="text-danger small mt-1 fw-bold" style="display: none;"></div>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label fw-semibold text-dark">Alamat Email</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-at text-muted"></i></span>
                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($current_user['email']); ?>" required placeholder="contoh@email.com">
                                                    <div class="invalid-feedback">Email tidak boleh kosong dan harus format email yang benar.</div>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-end pt-3">
                                                <button type="submit" name="update_profil" class="btn btn-custom-primary">
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- End Tab Profil -->

                                    <!-- Tab Keamanan -->
                                    <div class="tab-pane fade <?= $active_tab == 'password' ? 'show active' : ''; ?>" id="password" role="tabpanel">
                                        <form action="" method="POST" class="needs-validation" id="formGantiPassword" novalidate>
                                            <h6 class="text-uppercase text-muted small fw-bold mb-4">Ganti Kata Sandi</h6>

                                            <div class="mb-4">
                                                <label class="form-label fw-semibold text-dark">Kata Sandi Lama</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="fa-solid fa-lock-open text-muted"></i></span>
                                                    <input type="password" class="form-control" name="password_lama" id="password_lama" required placeholder="••••••••">
                                                    <div class="invalid-feedback">Kata sandi lama wajib diisi.</div>
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-4">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-dark">Kata Sandi Baru</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fa-solid fa-key text-muted"></i></span>
                                                        <input type="password" class="form-control" name="password_baru" id="password_baru" required placeholder="••••••••">
                                                        <div class="invalid-feedback">Wajib diisi.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold text-dark">Konfirmasi Sandi</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="fa-solid fa-check-double text-muted"></i></span>
                                                        <input type="password" class="form-control" name="konfirmasi_password" id="konfirmasi_password" required placeholder="••••••••">
                                                        <div class="invalid-feedback" id="konfirmasiFeedback">Konfirmasi wajib diisi.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-info py-2 small">
                                                <i class="fa-solid fa-circle-info me-1"></i> Pastikan kata sandi baru Anda kuat.
                                            </div>

                                            <div class="d-flex justify-content-end pt-3">
                                                <button type="submit" name="ganti_password" class="btn btn-custom-primary">
                                                    Perbarui Kata Sandi
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- End Tab Keamanan -->
                                </div>
                            </div>
                            <!-- End Tab Profil dan Keamanan -->
                        </div>
                    </div>
                    <!-- End Content Tab -->
                </div>
            </div>
        </main>
        <!-- End Main -->
    </section>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/admin.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  AOS.init();

  // Validasi Foto dan Preview
  const fotoInput = document.getElementById('foto');
  const previewImg = document.getElementById('previewImg');
  const imgLoading = document.getElementById('imgLoading');
  const fotoFeedback = document.getElementById('fotoFeedback'); 
  if (fotoInput) {
      fotoInput.addEventListener('change', function(e) {
          const file = this.files[0];
          fotoFeedback.style.display = 'none';
          fotoFeedback.textContent = '';
          if (file) {
              if (file.size > 2 * 1024 * 1024) {
                  fotoFeedback.textContent = 'File terlalu besar! Maksimal 2MB.';
                  fotoFeedback.style.display = 'block';
                  this.value = ''; return; 
              }
              const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
              if (!allowedTypes.includes(file.type)) {
                  fotoFeedback.textContent = 'Format salah! Gunakan JPG/PNG.';
                  fotoFeedback.style.display = 'block';
                  this.value = ''; return; 
              }
              
              imgLoading.style.display = 'flex';
              const reader = new FileReader();
              reader.onload = function(event) {
                  setTimeout(() => {
                      previewImg.src = event.target.result;
                      imgLoading.style.display = 'none';
                  }, 400);
              }
              reader.readAsDataURL(file);
          }
      });
  }
  
  // Validasi Form
  (function () {
    'use strict'
    
    // Validasi Form Profil
    var formProfil = document.getElementById('formProfil');
    if(formProfil) {
        formProfil.addEventListener('submit', function (event) {
            if (!formProfil.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            formProfil.classList.add('was-validated');
        }, false);
    }

    // Validasi Form Password
    var formPass = document.getElementById('formGantiPassword');
    if(formPass) {
        var passwordLama = document.getElementById('password_lama');
        var passwordBaru = document.getElementById('password_baru');
        var konfirmasi = document.getElementById('konfirmasi_password');
        var konfirmasiFeedback = document.getElementById('konfirmasiFeedback');
        passwordLama.addEventListener('change', function() {
            const val = this.value;
            if(val === "") return;
            fetch('ajax_check_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'password_lama=' + encodeURIComponent(val)
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'invalid') {
                    passwordLama.setCustomValidity("Wrong");
                    passwordLama.nextElementSibling.textContent = "Kata sandi lama salah.";
                } else {
                    passwordLama.setCustomValidity("");
                }
                passwordLama.classList.add('is-invalid'); 
                if(data.status === 'valid') passwordLama.classList.remove('is-invalid');
            })
            .catch(error => console.error('Error:', error));
        });
        function validateMatch() {
            if (passwordBaru.value !== konfirmasi.value) {
                konfirmasi.setCustomValidity("Mismatch");
                konfirmasiFeedback.textContent = "Password tidak cocok!";
            } else {
                konfirmasi.setCustomValidity("");
                konfirmasiFeedback.textContent = "Konfirmasi wajib diisi.";
            }
        }
        passwordBaru.addEventListener('input', validateMatch);
        konfirmasi.addEventListener('input', validateMatch);
        formPass.addEventListener('submit', function (event) {
            validateMatch();
            if (!formPass.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            formPass.classList.add('was-validated');
        }, false);
    }
  })();

  //  Alert
  const alertData = <?= json_encode($alert); ?>;
  if (alertData) {
    Swal.fire({
        title: alertData.icon === 'success' ? "Berhasil!" : "Gagal!",
        text: alertData.message,
        icon: alertData.icon,
        confirmButtonColor: '#4e73df',
        timer: 3000
    });
  }
</script>
</body>
</html>