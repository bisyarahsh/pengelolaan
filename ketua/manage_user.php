<?php
include 'php/koneksi.php';
// Memulai sesi
session_start();
// Cek apakah user sudah login
if ($_SESSION['status'] != "login") {
    // Jika belum login, arahkan ke halaman login
    header("location:../login/login.php");
    exit;
}
// Cek apakah role user bukan 'ketua'
if ($_SESSION['role'] != "Ketua") {
    // Jika bukan ketua, tolak akses dan arahkan kembali
    header("location:../login/login.php");
    exit;
}

// ==============================================================================
// 1. Ambil unit_id Ketua yang sedang login
// Asumsi: $_SESSION['id_user'] tersedia saat status = "login".
$current_user_id = $_SESSION['id_user'] ?? 0;
$unit_id_ketua = 0; // Default ke 0 (tidak akan menampilkan hasil) jika gagal ambil ID

// Query untuk mendapatkan unit_id Ketua
$unit_sql = "SELECT unit_id FROM users WHERE id_user = " . intval($current_user_id);
$unit_result = $koneksi->query($unit_sql);

if ($unit_result && $unit_result->num_rows > 0) {
    $unit_row = $unit_result->fetch_assoc();
    $unit_id_ketua = intval($unit_row['unit_id']);
}

// 2. Modifikasi Query Utama: Hanya tampilkan 'peserta' dari unit Ketua
$sql = "SELECT u.id_user, u.nim, u.nama_lengkap, u.email, u.role, u.unit_id, o.nama_unit 
        FROM users u
        LEFT JOIN unit o ON u.unit_id = o.id_unit
        WHERE u.role = 'peserta' AND u.unit_id = {$unit_id_ketua}
        ORDER BY u.nim ASC";
// ==============================================================================

$result = $koneksi->query($sql);
$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Ambil semua data unit untuk dropdown di modal (Ini dibiarkan menampilkan semua unit)
$org_sql = "SELECT id_unit, nama_unit FROM unit ORDER BY nama_unit ASC";
$org_result = $koneksi->query($org_sql);
$unit_list = [];
if ($org_result->num_rows > 0) {
    while($row = $org_result->fetch_assoc()) {
        $unit_list[] = $row;
    }
}

$koneksi->close(); 

// Ambil flash message dari session jika ada
$alert = $_SESSION['alert'] ?? null;
unset($_SESSION['alert']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.4/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/admin.css">
    <title>Pengguna | Ketua - Rapatin</title>
    <link rel="shortcut icon" href="../assets/logo/logo.png">
</head>
<body>
    
    <section id="sidebar">
        <a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
        <a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
        <ul class="side-menu" data-aos="fade-right">
            <li><a href="agenda.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
            <li><a href="riwayat.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
            <li><a href="manage_user.php" class="active"><i class="fa-solid fa-user icon"></i> Pengguna</a></li>
            <li><a href="pengaturan.php"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Logout</a></li>
        </ul>
    </section>
    <section id="content">
        <nav class="atas">
            <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar' ></i>
        </nav>
        <main>
            <div data-aos="fade-down" class="rapat bg-light">
                <div class="tableheader">
                    <h2>Pengguna</h2>
                    <button type="button" class="btn btn-primary tambah" data-bs-toggle="modal" data-bs-target="#tambahModal">Tambah</button>
                </div>
                <table id="tabel-rapat" class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Unit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($users as $user): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($user['nim']) ?></td>
                            <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['nama_unit'] ?? '-') ?></td>
                            <td>
                                <button 
                                    type="button" 
                                    class="btn btn-primary aksi edit-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal"
                                    data-id="<?= $user['id_user'] ?>"
                                    data-nim="<?= $user['nim'] ?>"
                                    data-nama="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                    data-role="<?= $user['role'] ?>"
                                    data-unit="<?= $user['unit_id'] ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-danger aksi delete-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deletemodal"
                                    data-id="<?= $user['id_user'] ?>"
                                    data-nama="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            
                <div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
                    <div class="modal-dialog ">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="tambahModalLabel">Tambah Pengguna</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="php/ketua_add_user.php">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="mb-2" for="nim">NIM</label>
                                        <input class="form-control" type="number" name="nim" id="nim" placeholder="Masukkan NIM..." required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="mb-2" for="nama_lengkap">Nama</label>
                                        <input class="form-control" type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Masukkan Nama..." required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="mb-2" for="password">Kata Sandi (Kosongkan untuk menggunakan NIM sebagai kata sandi bawaan )</label>
                                        <input class="form-control" type="password" name="password" id="password" placeholder="Kata sandi : NIM..." >
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="mb-2" for="email">Email</label>
                                        <input class="form-control" type="email" name="email" id="email" placeholder="contoh@gmail.com" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="tambah_pengguna" class="btn btn-primary">Tambah</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog ">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit Pengguna</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="php/ketua_edit_user.php">
                            <div class="modal-body">
                                <input type="hidden" name="edit_id_user" id="edit_id_user"> 
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_nim">NIM</label>
                                    <input class="form-control" type="number" name="edit_nim" id="edit_nim" placeholder="Masukkan NIM..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_nama_lengkap">Nama</label>
                                    <input class="form-control" type="text" name="edit_nama_lengkap" id="edit_nama_lengkap" placeholder="Masukkan Nama..." required>
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_password">Kata Sandi (Kosongkan jika tidak ingin diubah)</label>
                                    <input class="form-control" type="password" name="edit_password" id="edit_password" placeholder="Masukkan Kata Sandi Baru...">
                                </div>
                                <div class="mb-3">
                                    <label class="mb-2" for="edit_email">Email</label>
                                    <input class="form-control" type="email" name="edit_email" id="edit_email" placeholder="contoh@gmail.com" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="edit_pengguna" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="deletemodal" tabindex="-1" aria-labelledby="deletemodalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="php/ketua_delete_user.php">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deletemodalLabel">Hapus Pengguna</h5>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="hapus_id_user" id="hapus_id_user"> 
                                    <p class="h5">Apakah anda yakin ingin menghapus pengguna <span id="hapus_nama_pengguna"></span>?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="hapus_pengguna" class="btn btn-danger">Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
if (alertData && alertData.message) {
    Swal.fire({
        // Gunakan 'Perhatian!' untuk icon warning (duplikasi) atau 'Selamat!' untuk success
        title: alertData.icon === 'success' ? "Selamat!" : "Perhatian!", 
        text: alertData.message,
        icon: alertData.icon, // Membaca 'success' atau 'warning' dari PHP
        timer: 3000,
        showConfirmButton: false
    });
}

// 2. Logika Pengisian Modal Edit & Hapus (JQuery)
$(document).ready(function() {
    // Mengisi data modal Edit
    $('.edit-btn').on('click', function() {
        const id = $(this).data('id');
        const nim = $(this).data('nim');
        const nama = $(this).data('nama');
        const email = $(this).data('email');
        const role = $(this).data('role');
        const unit_id = $(this).data('unit');
        
        $('#edit_id_user').val(id); 
        $('#edit_nim').val(nim);
        $('#edit_nama_lengkap').val(nama);
        $('#edit_email').val(email);
        $('#edit_role').val(role); // Pilih opsi Peran
        $('#edit_unit_id').val(unit_id); // Pilih opsi unit
        
        // Kosongkan field password untuk keamanan
        $('#edit_password').val('');
    });

    // Mengisi data modal Hapus
    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        
        $('#hapus_id_user').val(id); 
        $('#hapus_nama_pengguna').text(nama);
    });

    // MODIFIKASI: Logika untuk default password = NIM pada modal Tambah Pengguna
    $('#tambahModal form').on('submit', function(e) {
        const nim = $('#nim').val();
        const passwordField = $('#password');
        
        // Cek jika field password kosong
        if (passwordField.val() === '') {
            // Pastikan NIM terisi
            if (nim !== '') {
                // Jika kosong, set value password menjadi NIM sebelum dikirim
                passwordField.val(nim);
            } else {
                // Jika NIM juga kosong, batalkan submit
                e.preventDefault();
                Swal.fire({
                    title: "Perhatian!",
                    text: "NIM tidak boleh kosong. Harap isi NIM terlebih dahulu.",
                    icon: "warning",
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }
        // Jika password diisi manual, biarkan saja dan lanjutkan submit.
        // Jika password diisi NIM otomatis, lanjutkan submit.
    });

    // Inisialisasi DataTables
    $(document).ready(function() {
	    $('#tabel-rapat').DataTable({
	        "language": {
	            "emptyTable": "Tidak ada pengguna",
	            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengguna",
	            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 pengguna",
	            "infoFiltered": "(difilter dari total _MAX_ pengguna)",
	            "lengthMenu": "Tampilkan _MENU_ pengguna",
	            "search": "Cari:",
	            "zeroRecords": "Tidak ditemukan pengguna yang cocok"
	        }
	    });
    });
});
</script>
</body>
</html>