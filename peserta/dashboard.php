<?php
include '../php/koneksi.php';
// Memulai sesi
session_start();
if ($_SESSION['status'] != "login") {
    // Jika belum login, arahkan ke halaman login
    header("location:../login/login.php");
    exit;
}
// Cek apakah role user bukan 'Peserta'
if ($_SESSION['role'] != "Peserta") {
    // Jika bukan peserta, tolak akses dan arahkan kembali
    header("location:../login/login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css"
    />
    <link
      rel="stylesheet"
      href="https://cdn.datatables.net/responsive/2.3.4/css/responsive.bootstrap5.min.css"
    />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/peserta.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <title>Rapatin</title>
  </head>
  <body>
    <!-- SIDEBAR -->
    <section id="sidebar">
		<a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class='ps-5'></i> Rapatin</a>
		<a href="../landing/index.php" data-aos="fade-down" class="logo-mini fw-bold"> R</a>
		<ul class="side-menu" data-aos="fade-right">
			<li><a href="dashboard.php" class="active"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a></li>
			<li><a href="history.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a></li>
			<li><a href="ganti__password.php"><i class="fa-solid fa-gear icon"></i> Ganti Kata Sandi</a></li>
			<li><a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Keluar</a></li>
		</ul>
	</section>
	<!-- SIDEBAR -->
  

    <!-- Content -->
    <section id="content">
      <!-- Toggle Sidebar -->
      <nav class="atas">
        <i data-aos="fade-right" class='fa-solid fa-bars toggle-sidebar' ></i>
      </nav>
      <!-- End Toggle Sidebar -->

      <!-- Main -->
      <main>
        <div data-aos="fade-down" class="rapat bg-light">
          <div class="tableheader">
            <h2>Agenda Rapat</h2>
          </div>
          <table id="example" class="table table-striped">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal Rapat</th>
                <th>Jam Rapat</th>
                <th>Judul Rapat</th>
                <th>Keterangan</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-success rounded py-1 mb-0 mx-auto text-light">Selesai</p>
				</td>
              </tr>

              <tr>
                <td>2</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-danger rounded py-1 mb-0 mx-auto text-light">Dibatalkan</p>
				</td>
              </tr>
              <tr>
                <td>3</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-success rounded py-1 mb-0 mx-auto text-light">Selesai</p></td>
              </tr>
              <tr>
                <td>4</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>5</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>6</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>7</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>8</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			<tr>
                <td>9</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>10</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>11</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>12</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>13</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

			  <tr>
                <td>1</td>
                <td>25-09-2025</td>
                <td>10:00 WIB</td>
                <td>HMTI Fair</td>
                <td>Membahas Terkait Kepanitiaan HMTI Fair 2025</td>
                <td class="text-center">
					<p class="bg-warning rounded py-1 mb-0 mx-auto text-light">Menunggu</p>
				</td>
              </tr>

            </tbody>
          </table>
      </main>
      <!-- End Main -->
    </section>
    <!-- End Content -->

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/peserta.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </body>
</html>
