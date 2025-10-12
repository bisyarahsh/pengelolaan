document.addEventListener('DOMContentLoaded', () => {
    // ... (kode JavaScript sebelumnya tetap ada di sini) ...

    const addUserBtn = document.getElementById('add-user-btn');
    const userModal = document.getElementById('user-modal');
    const closeBtn = userModal.querySelector('.close-btn');
    const addUserForm = document.getElementById('add-user-form');

    // Fungsi untuk menampilkan modal
    if (addUserBtn) {
        addUserBtn.addEventListener('click', () => {
            userModal.classList.add('open');
        });
    }

    // Fungsi untuk menutup modal
    const closeModal = () => {
        userModal.classList.remove('open');
        addUserForm.reset(); // Reset form saat ditutup
    };

    // Tutup saat tombol 'X' ditekan
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Tutup saat mengklik di luar modal (overlay)
    if (userModal) {
        userModal.addEventListener('click', (e) => {
            if (e.target === userModal) {
                closeModal();
            }
        });
    }

    // Handle pengiriman form (Contoh sederhana tanpa backend)
    if (addUserForm) {
        addUserForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Dapatkan data form
            const userData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                role: document.getElementById('role').value
                // Password tidak ditampilkan di sini
            };

            alert(`Pengguna Baru Ditambahkan:\nNama: ${userData.name}\nEmail: ${userData.email}\nRole: ${userData.role}\n\n(Logika penyimpanan ke database belum diimplementasikan)`);

            // Tutup modal setelah submit
            closeModal();
        });
    }
});

