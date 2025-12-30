# Website Pengelolaan Rapat ( RAPATIN )
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![JavaScript](https://img.shields.io/badge/Frontend-JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![Bootstrap](https://img.shields.io/badge/UI-Bootstrap%205-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

Aplikasi ini adalah sistem berbasis web yang dirancang untuk membantu proses pengelolaan data administratif. Dibangun menggunakan PHP Native, sistem ini memiliki fitur manajemen data pengguna dan unit, serta kemampuan untuk menghasilkan laporan.

## 🌐 Live Demo

Coba aplikasi langsung di sini:
👉 **[rapatinif1c2.gt.tc](http://rapatinif1c2.gt.tc)**

## 🔑 Akun Demo (Default Login)

Untuk mencoba aplikasi, gunakan akun berikut sesuai peran:

| Peran | Email | Password |
| :--- | :--- | :--- |
| **Tata Usaha** | admin@gmail.com | admin |
| **Ketua Prodi** | adrian@gmail.com | adrian |
| **Dosen/Labor** | apri@gmail.com | apri123 |

> **Catatan Keamanan:** Segera ganti password setelah login pertama kali demi keamanan data.

---

## 🚀 Fitur Utama

* **Manajemen Data Rapat (Agenda):**
    * Penjadwalan rapat baru (Waktu, Tempat, Peserta).
    * **Upload Notulen:** Fitur untuk mengunggah hasil rapat (file docx/pdf).
* **Manajemen Data Pengguna:** Fitur untuk menambah, mengedit, dan menghapus (CRUD) data pengguna. 
* **Manajemen Unit:** Pengelolaan data unit kerja atau kategori terkait. 
* **Ekspor Dokumen PDF:** Kemampuan mencetak laporan atau data ke dalam format PDF menggunakan library **DomPDF**. 
* **Notifikasi Email:** Integrasi dengan **PHPMailer** untuk pengiriman notifikasi via email. 
* **Halaman Landing:** Halaman muka untuk akses publik atau login. 

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP
* **Database:** MySQL
* **Frontend:** HTML, CSS, JS
* **Framework:**
    * Bootstrap 5 - Untuk CSS dan JS.
    * Fontawesome - Untuk Icons.
    * SwwetAlert - Untuk popup notifikasi.
    * AOS - Untuk efek animasi website saat di-scroll.
    * Flatpickr - Untuk memilih tanggal, waktu, atau rentang tanggal dalam format Indonesia.
* **Library Pihak Ketiga:**
    * [PHPMailer](https://github.com/PHPMailer/PHPMailer) - Untuk pengiriman email. 
    * [DomPDF](https://github.com/dompdf/dompdf) - Untuk generate file PDF. 

## 📂 Struktur Folder

* `/assets` - Berisi file untuk script JS, style CSS, serta untuk foto profil dan logo.
* `/lib` - Menyimpan library eksternal (DomPDF, PHPMailer).
* `/php` - Berisi logika backend seperti `delete_user.php`, `delete_unit.php`, dll.
* `/notulen_files` - Berisi file untuk notulen pada rapat yang telah dibuat.
* `/login` - Berisi file halaman login untuk menghubungkan pengguna ke folder sesuai dengan rolenya.
* `/admin` - Berisi file halaman dengan role Tata Usaha.
* `/ketua` - Berisi file halaman dengan role Ketua Prodi.
* `/peserta` - Berisi file halaman dengan role Dosen/Labor.

## ⚙️ Instalasi dan Penggunaan

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di komputer lokal Anda:

1.  **Clone Repositori**
    ```bash
    git clone https://github.com/bisyarahsh/pengelolaan
    ```

2.  **Persiapan Database**
    * Buat database baru di MySQL (misalnya: `db_rapat`).
    * Impor file database yang bernama `db_rapat.sql`.

3.  **Konfigurasi Sistem**
    * **Database:**
        Buka file `php/koneksi.php` (dan `ketua/php/koneksi.php` jika terpisah) lalu sesuaikan:
        ```php
        $host = "localhost";
        $user = "root";
        $pass = "";
        $db   = "db_rapat";
        ```
    * **Email (Wajib untuk Notifikasi):**
        Agar fitur pengiriman email undangan berjalan, buka file `php/send_notification.php`. Cari bagian konfigurasi SMTP dan masukkan email & App Password Anda:
        ```php
        $mail->Username   = 'email_anda@gmail.com';
        $mail->Password   = 'app_password_google_anda';
        ```

4.  **Jalankan Proyek**
    * Pindahkan folder proyek ke direktori server lokal (misalnya `htdocs` untuk XAMPP atau `www` untuk Laragon).
    * Buka browser dan akses: `http://localhost/pengelolaan`

---
## ⚠️ Catatan Penting
* Pastikan folder `notulen_files` memiliki izin akses **Write (755 atau 777)** agar sistem bisa menyimpan file notulen yang diunggah.
* Pastikan folder `lib` sudah terunduh dengan lengkap agar fitur PDF dan Email berfungsi dengan baik.

---
## 👨‍💻 Tim Pengembang

Dibuat oleh Mahasiswa Teknik Informatika Politeknik Negeri Batam:
* **3312501067 - Apri Catur Pramudiansyah** - *Ketua*
* **3312501064 - Adrian Septiaji** -
* **3312501065 - Syarifah Bisyarah Shahab** -
* **3312501066 - M. Fauzi Azhari** -
