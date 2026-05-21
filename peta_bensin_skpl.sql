-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 08:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `peta_bensin_skpl`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `nama`, `password`) VALUES
(1, 'admin', 'Admin Utama', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `antrian`
--

CREATE TABLE `antrian` (
  `id_antrian` int(11) NOT NULL,
  `id_spbu` int(11) NOT NULL,
  `jumlah_antrian` int(11) DEFAULT 0,
  `waktu_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `antrian`
--

INSERT INTO `antrian` (`id_antrian`, `id_spbu`, `jumlah_antrian`, `waktu_update`) VALUES
(1, 1, 7, '2026-05-20 11:33:15'),
(2, 2, 0, '2026-05-20 11:28:16'),
(3, 3, 2, '2026-05-20 11:28:16'),
(4, 4, 1, '2026-05-20 11:28:16'),
(5, 5, 0, '2026-05-20 11:28:16');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_pencarian`
--

CREATE TABLE `riwayat_pencarian` (
  `id_riwayat` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `lokasi_awal` varchar(255) NOT NULL,
  `total_jarak` double NOT NULL,
  `waktu_estimasi` double NOT NULL,
  `spbu_tujuan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_pencarian`
--

INSERT INTO `riwayat_pencarian` (`id_riwayat`, `tanggal`, `lokasi_awal`, `total_jarak`, `waktu_estimasi`, `spbu_tujuan`) VALUES
(1, '2026-05-20 11:33:05', 'Titik User', 3.5, 15.5, 'SPBU D'),
(2, '2026-05-20 11:33:08', 'Titik User', 3.5, 15.5, 'SPBU D'),
(3, '2026-05-20 11:33:17', 'Titik User', 3.5, 15.5, 'SPBU D');

-- --------------------------------------------------------

--
-- Table structure for table `spbu`
--

CREATE TABLE `spbu` (
  `id_spbu` int(11) NOT NULL,
  `nama_spbu` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `pos_x` double NOT NULL,
  `pos_y` double NOT NULL,
  `status` enum('Buka','Tutup') DEFAULT 'Buka'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spbu`
--

INSERT INTO `spbu` (`id_spbu`, `nama_spbu`, `alamat`, `pos_x`, `pos_y`, `status`) VALUES
(1, 'SPBU A', 'Jl. Pahlawan', 35, 25, 'Buka'),
(2, 'SPBU B', 'Jl. Diponegoro', 60, 35, 'Tutup'),
(3, 'SPBU C', 'Jl. Sumatra', 35, 75, 'Buka'),
(4, 'SPBU D', 'Jl. Surodinawan', 80, 50, 'Buka'),
(5, 'SPBU E', 'Jl. Ngergong', 85, 75, 'Tutup');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `antrian`
--
ALTER TABLE `antrian`
  ADD PRIMARY KEY (`id_antrian`),
  ADD UNIQUE KEY `id_spbu` (`id_spbu`);

--
-- Indexes for table `riwayat_pencarian`
--
ALTER TABLE `riwayat_pencarian`
  ADD PRIMARY KEY (`id_riwayat`);

--
-- Indexes for table `spbu`
--
ALTER TABLE `spbu`
  ADD PRIMARY KEY (`id_spbu`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `antrian`
--
ALTER TABLE `antrian`
  MODIFY `id_antrian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `riwayat_pencarian`
--
ALTER TABLE `riwayat_pencarian`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `spbu`
--
ALTER TABLE `spbu`
  MODIFY `id_spbu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrian`
--
ALTER TABLE `antrian`
  ADD CONSTRAINT `antrian_ibfk_1` FOREIGN KEY (`id_spbu`) REFERENCES `spbu` (`id_spbu`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
