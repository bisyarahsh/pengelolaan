-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 07, 2026 at 12:13 PM
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
  `id_unit` int NOT NULL,
  `id_pembuat` int NOT NULL,
  `status` enum('aktif','dibatalkan') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `agenda_rapat`
--

INSERT INTO `agenda_rapat` (`id_rapat`, `tanggal_rapat`, `jam_rapat`, `judul_rapat`, `ruang_rapat`, `keterangan`, `notulen_file`, `id_unit`, `id_pembuat`, `status`) VALUES
(55, '2025-12-21', '12:00:00', 'Rapat Evaluasi', 'GU 706', 'bagus', '', 20, 11, 'aktif'),
(60, '2025-12-30', '12:00:00', 'asf', 'asga', 'asf', '', 3, 11, 'aktif'),
(61, '2026-01-24', '12:00:00', 'asglh', 'asgjk', 'akjs', '', 3, 2, 'aktif'),
(62, '2026-01-08', '12:00:00', 'akg', 'ag', 'asgaga', '', 3, 11, 'dibatalkan'),
(64, '2026-01-04', '12:00:00', 'test', 'test', 'test', '', 3, 11, 'aktif'),
(65, '2026-01-04', '12:00:00', 'test', 'test', 'tes', '', 2, 11, 'aktif');

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
(235, 55, 2),
(250, 60, 5),
(251, 61, 5),
(253, 61, 17),
(252, 61, 19),
(254, 62, 2),
(255, 64, 5),
(256, 64, 17),
(257, 65, 2);

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

CREATE TABLE `unit` (
  `id_unit` int NOT NULL,
  `nama_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`id_unit`, `nama_unit`) VALUES
(3, 'Animasi'),
(2, 'Rekayasa Keamanan Siber'),
(20, 'Teknik Informatika'),
(11, 'Teknologi Geomatika'),
(5, 'Teknologi Permainan'),
(8, 'Teknologi Rekayasa Multimedia'),
(13, 'Teknologi Rekayasa Perangkat Lunak');

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
  `role` enum('Ketua','Peserta','Admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `unit_id` int DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nim`, `nama_lengkap`, `email`, `password`, `role`, `unit_id`, `profile_pic`) VALUES
(2, '3312501064', 'Adrian Septiaji', 'adrian@gmail.com', '$2y$10$SKhMwa3U.40fjGtHwYUZ7OAP9d/1AAAWW.caPqY.27wNk5QjXXhAu', 'Ketua', 3, NULL),
(5, '3312501067', 'Apri Catur Pramudiansyah', 'apri.cp.syah@gmail.com', '$2y$10$8M7GSH.WXvYqcGfWBwBUpepeS5qnkmm1ndeo3vJ.mMnY1.k7vlgTS', 'Peserta', 3, '1767011793_695275d1901d0.png'),
(7, '3312501065', 'Syarifah Bisyarah Shahab', 'syarah@gmail.com', '$2y$10$mpVO55Qyig9aqXFiTaFGIuTbEvRtVDRKnCyh0oi5o4sCKK7/cW6gu', 'Ketua', 20, NULL),
(8, '3312501066', 'M. Fauzi Azhari', 'arifozil182@gmail.com', '$2y$10$FMKimLuJ6/fR0LHwMPskHeK6wmK524LBd4k/Z85aX7ePuEI7ao3/O', 'Peserta', 11, NULL),
(11, '9999999999', 'Tata Usaha', 'admin@gmail.com', '$2y$10$ELhdFk9HCSOcGrPgcHMrCOf3QQ80UmM39xksWlL.ehfktLLKTcpn6', 'Admin', NULL, NULL),
(17, '3312501063', 'Dwi Agung Wiliyanto', 'agung@gmail.com', '$2y$10$ZLOa5f4eKVQYGIyELoQ.eu.MpenzW2eS2YdrP0sVQpWfQ5okgfKDi', 'Peserta', 3, NULL),
(19, '3312501071', 'Ayu Diana Tasya', 'ayu@gmail.com', '$2y$10$uVtTgDro/VVn.zuYQZd8quzN07rdh5wc9y0UbZGr.Yvfr9MNnMRRq', 'Ketua', 3, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agenda_rapat`
--
ALTER TABLE `agenda_rapat`
  ADD PRIMARY KEY (`id_rapat`),
  ADD KEY `id_organisasi` (`id_unit`),
  ADD KEY `id_pembuat` (`id_pembuat`);

--
-- Indexes for table `peserta_rapat`
--
ALTER TABLE `peserta_rapat`
  ADD PRIMARY KEY (`id_peserta_rapat`),
  ADD UNIQUE KEY `unique_peserta` (`id_rapat`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id_unit`),
  ADD UNIQUE KEY `organisasi` (`nama_unit`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `organisasi_id` (`unit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agenda_rapat`
--
ALTER TABLE `agenda_rapat`
  MODIFY `id_rapat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `peserta_rapat`
--
ALTER TABLE `peserta_rapat`
  MODIFY `id_peserta_rapat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id_unit` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agenda_rapat`
--
ALTER TABLE `agenda_rapat`
  ADD CONSTRAINT `agenda_rapat_ibfk_1` FOREIGN KEY (`id_unit`) REFERENCES `unit` (`id_unit`) ON DELETE CASCADE,
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
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `unit` (`id_unit`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
