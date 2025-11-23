document.getElementById('loginForm').addEventListener('submit', function(e) {
    // 1. Mencegah submit form secara default (PENTING!)
    e.preventDefault();

    // 2. Mengambil data dari form
    // FormData secara otomatis mengumpulkan semua data input form
    const formData = new FormData(this);

    // 3. Mengirim data menggunakan Fetch API (cara modern dari AJAX)
    fetch('login.php', {
        method: 'POST', // Metode yang digunakan (harus sesuai dengan PHP)
        body: formData  // Data yang dikirim
    })
    .then(response => response.json()) // 4. Menunggu respons dan memprosesnya sebagai JSON
    .then(data => {
        // 5. Menganalisis data (status) yang diterima dari PHP
        if (data.status === 'success') {
            
            // 6. JIKA LOGIN BERHASIL (status: success)
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Anda akan diarahkan ke halaman utama.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Arahkan user sesuai level
                if (data.level === 'admin') {
                    window.location.href = '../../admin/agenda.php';
                } else if (data.level === 'user') {
                    window.location.href = '../../peserta/dashboard.php';
                }
            });

        } else if (data.status === 'error') {
            
            // 7. JIKA LOGIN GAGAL (status: error)
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal!',
                // Tampilkan pesan kesalahan yang dikirim dari PHP
                text: data.message || 'Username atau Password salah!', 
            });
            
            // Opsional: Kosongkan input password setelah gagal
            document.getElementById('password').value = '';
        }
    })
    .catch(error => {
        // 8. Menangani kesalahan jaringan atau server
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Terjadi kesalahan pada koneksi server!',
        });
    });
});