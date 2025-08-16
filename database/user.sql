-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Agu 2025 pada 07.44
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
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `blocked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`username`, `password`, `login_attempts`, `blocked_until`) VALUES
('admin1', '$2y$10$DIwXaFq0tiyifDNpAKFWdeUfae8atfAMuWAXsNZ.8G5J1a8LXfahy', 0, NULL),
('admin2', '$2y$10$DIwXaFq0tiyifDNpAKFWdeUfae8atfAMuWAXsNZ.8G5J1a8LXfahy', 0, NULL),
('admin3', '$2y$10$DIwXaFq0tiyifDNpAKFWdeUfae8atfAMuWAXsNZ.8G5J1a8LXfahy', 0, NULL),
('admin4', '$2y$10$DIwXaFq0tiyifDNpAKFWdeUfae8atfAMuWAXsNZ.8G5J1a8LXfahy', 0, NULL),
('admin5', '$2y$10$DIwXaFq0tiyifDNpAKFWdeUfae8atfAMuWAXsNZ.8G5J1a8LXfahy', 0, NULL),
('admin6', '$2y$10$DIwXaFq0tiyifDNpAKFWdeUfae8atfAMuWAXsNZ.8G5J1a8LXfahy', 0, NULL),
('admin7', '$2y$10$DIwXaFq0tiyifDNpAKFWdeUfae8atfAMuWAXsNZ.8G5J1a8LXfahy', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
