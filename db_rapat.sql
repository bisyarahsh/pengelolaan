-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 17, 2025 at 02:29 PM
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
(28, '2025-11-30', '00:38:00', 'asgsajgal', 'aslgalga', 'agkagla', '', 8, 11, 'aktif'),
(29, '2025-11-27', '11:30:00', 'ajsgh', 'kjhsgkaa', '', '', 3, 11, 'aktif'),
(30, '2025-11-20', '11:52:00', 'alksaja', 'lkajslshjas', 'alsgalgka', '', 3, 2, 'aktif'),
(31, '2025-11-30', '02:50:00', 'aslgk', 'lalkghl', 'asgagjhsgashgka', '', 3, 2, 'aktif'),
(35, '2025-12-14', '19:15:00', 'asgahasgh', 'asgha', 'asgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajgasgaaskagajg', 'notulen_2025-12-17_1765715668.docx', 3, 11, 'aktif'),
(37, '2025-12-15', '19:35:00', 'aosfja', 'oasgoajgo', 'aslgalga', '', 3, 2, 'aktif'),
(40, '2025-12-18', '16:50:00', 'aghak', 'asjhska', 'a,mnakga', '', 3, 11, 'aktif'),
(41, '2025-12-09', '18:30:00', 'askhak', 'aksjhaka', 'akjshkgahk', '', 20, 11, 'dibatalkan'),
(42, '2025-12-17', '19:00:00', 'Rapat Koordinasi', 'kasgka', 'asgsaa', '', 2, 11, 'aktif'),
(43, '2025-12-21', '16:00:00', 'test', 'test', 'asfsa', '', 3, 11, 'aktif'),
(44, '2025-12-18', '20:10:00', 'askjhk', 'askak', 'asf', '', 2, 11, 'aktif'),
(46, '2025-12-19', '12:00:00', 'prikitiw', 'asfaf', 'asga', '', 3, 2, 'dibatalkan');

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
(108, 29, 5),
(110, 29, 8),
(118, 30, 5),
(121, 31, 5),
(122, 31, 17),
(177, 35, 5),
(178, 35, 17),
(162, 37, 17),
(185, 40, 5),
(187, 40, 8),
(186, 40, 17),
(191, 41, 5),
(193, 41, 8),
(192, 41, 17),
(206, 42, 5),
(208, 42, 8),
(207, 42, 17),
(197, 43, 5),
(199, 43, 8),
(198, 43, 17),
(200, 44, 5),
(202, 44, 8),
(201, 44, 17),
(211, 46, 5),
(212, 46, 17);

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
  `unit_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nim`, `nama_lengkap`, `email`, `password`, `role`, `unit_id`) VALUES
(2, '3312501064', 'Adrian Septiaji', 'adrian@gmail.com', '$2y$10$SKhMwa3U.40fjGtHwYUZ7OAP9d/1AAAWW.caPqY.27wNk5QjXXhAu', 'Ketua', 3),
(5, '3312501067', 'Apri Catur Pramudiansyah', 'apri.cp.syah@gmail.com', '$2y$10$NDpdRcNFwMy88AKgFzknLuan7v.Tpc6CoYYnMPoamVYGHO1nN9/DK', 'Peserta', 3),
(7, '3312501065', 'Syarifah Bisyarah Shahab', 'syarah@gmail.com', '$2y$10$mpVO55Qyig9aqXFiTaFGIuTbEvRtVDRKnCyh0oi5o4sCKK7/cW6gu', 'Ketua', 20),
(8, '3312501066', 'M. Fauzi Azhari', 'arifozil182@gmail.com', '$2y$10$FMKimLuJ6/fR0LHwMPskHeK6wmK524LBd4k/Z85aX7ePuEI7ao3/O', 'Peserta', 11),
(11, '9999999999', 'Admin Sistem', 'admin@gmail.com', '$2y$10$/lusFAKszzG5iSRdB6pwVOMilajoMoHqJ53eSBNmRF22A..WhOWS6', 'Admin', NULL),
(17, '3312501063', 'Dwi Agung Wiliyanto', 'agung@gmail.com', '$2y$10$ZLOa5f4eKVQYGIyELoQ.eu.MpenzW2eS2YdrP0sVQpWfQ5okgfKDi', 'Peserta', 3);

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
  MODIFY `id_rapat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `peserta_rapat`
--
ALTER TABLE `peserta_rapat`
  MODIFY `id_peserta_rapat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id_unit` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
