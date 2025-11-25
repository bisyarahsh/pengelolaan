-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 25, 2025 at 02:21 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_rapat`
--

-- --------------------------------------------------------

--
-- Table structure for table `agenda_rapat`
--

CREATE TABLE `agenda_rapat` (
  `id_rapat` int NOT NULL,
  `tanggal_rapat` date NOT NULL,
  `jam_rapat` time NOT NULL,
  `judul_rapat` varchar(255) NOT NULL,
  `ruang_rapat` varchar(100) DEFAULT NULL,
  `keterangan` text,
  `notulen_file` varchar(255) DEFAULT NULL,
  `id_organisasi` int NOT NULL,
  `id_pembuat` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `agenda_rapat`
--

INSERT INTO `agenda_rapat` (`id_rapat`, `tanggal_rapat`, `jam_rapat`, `judul_rapat`, `ruang_rapat`, `keterangan`, `notulen_file`, `id_organisasi`, `id_pembuat`) VALUES
(11, '2025-11-24', '16:56:00', 'Magang BLUG', 'TA 12.4', 'Pemilihan ketua pelaksana serta anggota lainnya', 'notulen_2025-11-24_1763985500.pdf', 5, 2),
(18, '2025-11-28', '18:00:00', 'HMTI Fair', 'Student Center Lt2', 'Membahas tentang kepanitiaan HMTI Fair dan membagi job desk masing masing panita', '', 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `organisasi`
--

CREATE TABLE `organisasi` (
  `id_organisasi` int NOT NULL,
  `nama_organisasi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `organisasi`
--

INSERT INTO `organisasi` (`id_organisasi`, `nama_organisasi`) VALUES
(3, 'BEM'),
(5, 'BLUG'),
(13, 'BRAIL'),
(2, 'DPM'),
(8, 'ENERGI'),
(11, 'HME'),
(10, 'HMM'),
(12, 'HMMB'),
(4, 'HMTI'),
(7, 'KUAS'),
(6, 'REKAM');

-- --------------------------------------------------------

--
-- Table structure for table `peserta_rapat`
--

CREATE TABLE `peserta_rapat` (
  `id_peserta_rapat` int NOT NULL,
  `id_rapat` int NOT NULL,
  `id_user` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peserta_rapat`
--

INSERT INTO `peserta_rapat` (`id_peserta_rapat`, `id_rapat`, `id_user`) VALUES
(32, 11, 5),
(33, 11, 8),
(34, 11, 9),
(49, 18, 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Ketua','Peserta') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `organisasi_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nim`, `nama_lengkap`, `email`, `password`, `role`, `organisasi_id`) VALUES
(2, '3312501064', 'Adrian Septiaji', 'adrian@gmail.com', '$2y$10$SKhMwa3U.40fjGtHwYUZ7OAP9d/1AAAWW.caPqY.27wNk5QjXXhAu', 'Ketua', 3),
(5, '3312501067', 'Apri Catur Pramudiansyah', 'apri@gmail.com', '$2y$10$cDuPK6UW2odz65xOaqVSGueAueyrOIBe90UrZyi4TamjgWJdUlPEC', 'Peserta', 3),
(7, '3312501065', 'Syarifah Bisyarah Shahab', 'syarah@gmail.com', '$2y$10$mpVO55Qyig9aqXFiTaFGIuTbEvRtVDRKnCyh0oi5o4sCKK7/cW6gu', 'Ketua', 4),
(8, '3312501066', 'M. Fauzi Azhari', 'arifozil182@gmail.com', '$2y$10$FMKimLuJ6/fR0LHwMPskHeK6wmK524LBd4k/Z85aX7ePuEI7ao3/O', 'Peserta', 4),
(9, '3312501063', 'Naila Alzakiiyah', 'naila@gmail.com', '$2y$10$tVVRs14GsHTOHp3e6/sAmOtwH3F0Wfeu9zBUipIRamlcnhPVaGKie', 'Peserta', 10);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agenda_rapat`
--
ALTER TABLE `agenda_rapat`
  ADD PRIMARY KEY (`id_rapat`),
  ADD KEY `id_organisasi` (`id_organisasi`),
  ADD KEY `id_pembuat` (`id_pembuat`);

--
-- Indexes for table `organisasi`
--
ALTER TABLE `organisasi`
  ADD PRIMARY KEY (`id_organisasi`),
  ADD UNIQUE KEY `organisasi` (`nama_organisasi`);

--
-- Indexes for table `peserta_rapat`
--
ALTER TABLE `peserta_rapat`
  ADD PRIMARY KEY (`id_peserta_rapat`),
  ADD UNIQUE KEY `unique_peserta` (`id_rapat`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `organisasi_id` (`organisasi_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agenda_rapat`
--
ALTER TABLE `agenda_rapat`
  MODIFY `id_rapat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `organisasi`
--
ALTER TABLE `organisasi`
  MODIFY `id_organisasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `peserta_rapat`
--
ALTER TABLE `peserta_rapat`
  MODIFY `id_peserta_rapat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agenda_rapat`
--
ALTER TABLE `agenda_rapat`
  ADD CONSTRAINT `agenda_rapat_ibfk_1` FOREIGN KEY (`id_organisasi`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE CASCADE,
  ADD CONSTRAINT `agenda_rapat_ibfk_2` FOREIGN KEY (`id_pembuat`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `peserta_rapat`
--
ALTER TABLE `peserta_rapat`
  ADD CONSTRAINT `peserta_rapat_ibfk_1` FOREIGN KEY (`id_rapat`) REFERENCES `agenda_rapat` (`id_rapat`) ON DELETE CASCADE,
  ADD CONSTRAINT `peserta_rapat_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`organisasi_id`) REFERENCES `organisasi` (`id_organisasi`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
