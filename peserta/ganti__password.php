<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.css" />
    <link rel="stylesheet" href="../assets/peserta.css" />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <title>Rapatin</title>
  </head>
  <body>
    <!-- SIDEBAR -->
    <section id="sidebar">
      <a href="../landing/index.php" data-aos="fade-down" class="logo ps-3"><i class="ps-5"></i> Rapatin</a> <a href="../landing/index.php" class="logo-mini fw-bold"> R</a>
         <ul class="side-menu" data-aos="fade-right">
        <li>
          <a href="dashboard.php"><i class="fa-solid fa-calendar-days icon"></i> Agenda Rapat</a>
        </li>
        <li>
          <a href="history.php"><i class="fa-solid fa-clock-rotate-left icon"></i> Riwayat Rapat</a>
        </li>
        <li>
          <a href="ganti__password.php" class="active"><i class="fa-solid fa-gear icon"></i> Ganti Password</a>
        </li>
        <li>
          <a href="logout.php"><i class="fa-solid fa-right-from-bracket icon"></i> Logout</a>
        </li>
      </ul>
    </section>
    <!-- SIDEBAR -->
    <!-- SIDEBAR -->

    <!-- Content -->
    <section id="content">
      <!-- Toggle Sidebar -->
      <nav class="atas">
        <i data-aos="fade-right" class="fa-solid fa-bars toggle-sidebar"></i>
      </nav>
      <!-- End Toggle Sidebar -->

      <!-- Main -->
      <main>
         <div data-aos="fade-down" class="pengaturan bg-light mx-auto">
          <div class="tableheader">
            <h2>Ganti Password</h2>
          </div>
          <form action="">
            <div class="mb-3">
              <label class="mb-2" for="password">Password Lama</label>
              <input class="form-control" type="password" name="name" id="name" placeholder="Masukkan Password Lama Anda..." />
            </div>
            <div class="mb-3">
              <label class="mb-2" for="password">Password Baru</label>
              <input class="form-control" type="password" name="name" id="name" placeholder="Masukkan Password Baru Anda..." />
            </div>
            <div class="mb-3">
              <label class="mb-2" for="password">Confirm Password</label>
              <input class="form-control" type="password" name="name" id="name" placeholder="Masukkan Password..." />
            </div>
            <button type="button" class="btn btn-primary d-block ms-auto px-3">Simpan</button>
          </form>
        </div>
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
