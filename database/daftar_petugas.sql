-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Agu 2025 pada 07.45
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `update_ai`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `daftar_petugas`
--

CREATE TABLE `daftar_petugas` (
  `id_petugas` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `shift` enum('Pagi','Siang','Malam') NOT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `daftar_petugas`
--

INSERT INTO `daftar_petugas` (`id_petugas`, `username`, `nama`, `shift`, `tanggal`) VALUES
('001', 'admin1', 'M. RIFAI MARASABESSY', 'Siang', '2025-08-09'),
('002', 'admin2', 'RAMSI NUKKUHALY', 'Malam', '2025-08-01'),
('003', 'admin3', 'NOVAL FADILAH', 'Malam', '2025-08-01'),
('004', 'admin4', 'RIANA YULSISCA SORONTO', 'Pagi', '2025-07-04'),
('005', 'admin5', 'GIRI SATRIA', 'Siang', '2025-06-09'),
('006', 'admin6', 'SUDIMAN', 'Malam', '2024-12-11');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `daftar_petugas`
--
ALTER TABLE `daftar_petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `daftar_petugas`
--
ALTER TABLE `daftar_petugas`
  ADD CONSTRAINT `data_petugas_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
