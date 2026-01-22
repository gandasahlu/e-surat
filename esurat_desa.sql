-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 14, 2026 at 07:15 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `esurat_desa`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama`, `created_at`) VALUES
(1, 'admin', '$2y$10$Y4qiFp5Byf5aLfpUYknh/.mzTol53/uUCumrcjug.pGpN1t0jweLq', 'Administrator Desa', '2025-12-02 03:13:12'),
(2, 'admin1', 'admin1', 'ucokkk', '2025-12-03 22:16:14');

-- --------------------------------------------------------

--
-- Table structure for table `dokumen_persyaratan`
--

CREATE TABLE `dokumen_persyaratan` (
  `id` int(11) NOT NULL,
  `pengajuan_id` int(11) NOT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `path_file` varchar(500) DEFAULT NULL,
  `tipe_file` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_surat`
--

CREATE TABLE `jenis_surat` (
  `id` int(11) NOT NULL,
  `kode_surat` varchar(10) NOT NULL,
  `nama_surat` varchar(100) NOT NULL,
  `template` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jenis_surat`
--

INSERT INTO `jenis_surat` (`id`, `kode_surat`, `nama_surat`, `template`, `created_at`) VALUES
(1, 'S-001', 'Surat Keterangan Domisili', NULL, '2025-12-02 03:13:12'),
(2, 'S-002', 'Surat Keterangan Tidak Mampu (SKTM)', NULL, '2025-12-02 03:13:12'),
(3, 'S-003', 'Surat Keterangan Usaha', NULL, '2025-12-02 03:13:12'),
(4, 'S-004', 'Surat Keterangan Kelahiran', NULL, '2025-12-02 03:13:12'),
(5, 'S-005', 'Surat Keterangan Kematian', NULL, '2025-12-02 03:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `kepala_desa`
--

CREATE TABLE `kepala_desa` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT 'Kepala Desa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kepala_desa`
--

INSERT INTO `kepala_desa` (`id`, `username`, `password`, `nama`, `jabatan`, `created_at`) VALUES
(1, 'kepaladesa', '$2y$10$jrYIwTgJ.wh11Xm8rkJlKuyIUwFbuKNM.qGQB5kwtjnfn8TAQHiIO', 'Bapak Kepala Desa', 'Kepala Desa', '2025-12-02 03:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penduduk`
--

CREATE TABLE `penduduk` (
  `id` int(11) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `no_telp` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penduduk`
--

INSERT INTO `penduduk` (`id`, `nik`, `nama`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_telp`, `password`, `created_at`) VALUES
(2, '1234567890123456', 'Budi Santoso', 'Jakarta', '1990-01-15', 'Jl. Merdeka No. 123', '081234567890', '$2y$10$AmXOPRZlyowMJutp/FRTcOnscJylVJ6sCfWQXOIlqeOMcKl7s0OvO', '2025-12-03 22:31:34'),
(3, '1111111111111111', 'ganda', 'Medan', '2005-05-10', '12 medan', '082276465022', '$2y$10$gBwFN/jKufuNKzfsL80/.e7bFiEO1jIwI8aK6vjmMdXEITlMzg1qi', '2025-12-16 13:18:52'),
(5, '1209142403040004', 'Sigit Pramono', 'Asahan', '2004-03-24', 'Persatuan Dusun 3', '082275613581', '$2y$10$giUXFSq.AqN1TffmhQAKzOQ1CkJ99HxudXILOgzh3JMmQMvXZTLBO', '2025-12-17 04:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id` int(11) NOT NULL,
  `warga_id` int(11) NOT NULL,
  `jenis_surat_id` int(11) NOT NULL,
  `data_formulir` text DEFAULT NULL,
  `status` enum('diajukan','diproses','ditolak','selesai') DEFAULT 'diajukan',
  `nomor_surat` varchar(50) DEFAULT NULL,
  `tanggal_pengajuan` datetime DEFAULT current_timestamp(),
  `tanggal_selesai` datetime DEFAULT NULL,
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_surat`
--

CREATE TABLE `pengajuan_surat` (
  `id` int(11) NOT NULL,
  `penduduk_id` int(11) NOT NULL,
  `jenis_surat_id` int(11) NOT NULL,
  `keperluan` text NOT NULL,
  `berkas_pendukung` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','diproses','disetujui','ditolak','siap_ambil') DEFAULT 'menunggu',
  `catatan_admin` text DEFAULT NULL,
  `nomor_surat` varchar(50) DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_verifikasi` timestamp NULL DEFAULT NULL,
  `tanggal_ttd` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan_surat`
--

INSERT INTO `pengajuan_surat` (`id`, `penduduk_id`, `jenis_surat_id`, `keperluan`, `berkas_pendukung`, `status`, `catatan_admin`, `nomor_surat`, `tanggal_pengajuan`, `tanggal_verifikasi`, `tanggal_ttd`) VALUES
(1, 2, 2, 'gamampu boss', '6930c1037d3ff_Screenshot 2025-11-13 110819.png', 'diproses', '\r\n', NULL, '2025-12-03 23:00:19', '2025-12-03 23:06:24', NULL),
(2, 2, 1, 'perlu', NULL, 'diproses', '', NULL, '2025-12-04 02:11:33', '2025-12-16 13:30:57', NULL),
(3, 2, 1, 'baru', NULL, 'siap_ambil', ' | TTD Kepala Desa: ', '001/S-001/KD/XII/2025', '2025-12-11 02:27:03', '2025-12-11 02:27:47', '2025-12-16 15:10:02'),
(4, 3, 2, 'untuk kuliah', NULL, 'siap_ambil', ' | TTD Kepala Desa: ', '001/SKD/DSAEKNABARA/25', '2025-12-16 16:01:51', '2025-12-16 16:02:46', '2025-12-16 16:04:19'),
(5, 3, 4, 'baru', '6942487319168_64c88b6d70726885f76c421c4549207b.jpg', 'ditolak', ' | Ditolak oleh Kepala Desa: ', NULL, '2025-12-17 06:06:43', '2025-12-17 06:19:58', '2025-12-17 06:36:38'),
(6, 3, 3, 'untuk surat usaha saya', NULL, 'siap_ambil', ' | TTD Kepala Desa: ', '001/S-001/SKU/XII/2025', '2025-12-17 06:19:11', '2025-12-17 06:20:06', '2025-12-17 06:36:24'),
(7, 3, 2, 'beasiswa', NULL, 'siap_ambil', ' | TTD Kepala Desa: ', '004/S-002/KD/XII/2025', '2025-12-18 03:08:49', '2025-12-18 03:10:51', '2025-12-18 03:11:42');

-- --------------------------------------------------------

--
-- Table structure for table `surat_masuk`
--

CREATE TABLE `surat_masuk` (
  `id` int(11) NOT NULL,
  `nomor_surat` varchar(100) DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `pengirim` varchar(200) DEFAULT NULL,
  `perihal` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `disposisi_ke` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('warga','operator','kepala_desa','admin') DEFAULT 'warga',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nik`, `nama`, `password`, `email`, `telepon`, `alamat`, `role`, `created_at`) VALUES
(1, '1234567890123456', 'Administrator', '$2y$10$YourHashedPasswordHere', NULL, NULL, NULL, 'admin', '2025-12-10 02:44:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `dokumen_persyaratan`
--
ALTER TABLE `dokumen_persyaratan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengajuan_id` (`pengajuan_id`);

--
-- Indexes for table `jenis_surat`
--
ALTER TABLE `jenis_surat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_surat` (`kode_surat`);

--
-- Indexes for table `kepala_desa`
--
ALTER TABLE `kepala_desa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `penduduk`
--
ALTER TABLE `penduduk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indexes for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warga_id` (`warga_id`),
  ADD KEY `jenis_surat_id` (`jenis_surat_id`);

--
-- Indexes for table `pengajuan_surat`
--
ALTER TABLE `pengajuan_surat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penduduk_id` (`penduduk_id`),
  ADD KEY `jenis_surat_id` (`jenis_surat_id`);

--
-- Indexes for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `disposisi_ke` (`disposisi_ke`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1235;

--
-- AUTO_INCREMENT for table `dokumen_persyaratan`
--
ALTER TABLE `dokumen_persyaratan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_surat`
--
ALTER TABLE `jenis_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `kepala_desa`
--
ALTER TABLE `kepala_desa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penduduk`
--
ALTER TABLE `penduduk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_surat`
--
ALTER TABLE `pengajuan_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokumen_persyaratan`
--
ALTER TABLE `dokumen_persyaratan`
  ADD CONSTRAINT `dokumen_persyaratan_ibfk_1` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `pengajuan_ibfk_1` FOREIGN KEY (`warga_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pengajuan_ibfk_2` FOREIGN KEY (`jenis_surat_id`) REFERENCES `jenis_surat` (`id`);

--
-- Constraints for table `pengajuan_surat`
--
ALTER TABLE `pengajuan_surat`
  ADD CONSTRAINT `pengajuan_surat_ibfk_1` FOREIGN KEY (`penduduk_id`) REFERENCES `penduduk` (`id`),
  ADD CONSTRAINT `pengajuan_surat_ibfk_2` FOREIGN KEY (`jenis_surat_id`) REFERENCES `jenis_surat` (`id`);

--
-- Constraints for table `surat_masuk`
--
ALTER TABLE `surat_masuk`
  ADD CONSTRAINT `surat_masuk_ibfk_1` FOREIGN KEY (`disposisi_ke`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
