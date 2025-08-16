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
-- Struktur dari tabel `data_kapal`
--

CREATE TABLE `data_kapal` (
  `MMSI` int(10) NOT NULL,
  `nama_kapal` varchar(50) NOT NULL,
  `callsign` varchar(20) NOT NULL,
  `jenis_kapal` enum('TANKER','LNG/LPG CARRIER','CARGO VESSEL','CONTAINER VESSEL','BULK CARRIER','RORO','PASSANGER VESSEL','LIVESTOCK CARRIER','TUG/TOW','GOVERNMENT VESSEL','FISHING VESSEL','OTHERS') NOT NULL,
  `GT` decimal(10,2) DEFAULT NULL,
  `LOA` decimal(10,2) DEFAULT NULL,
  `beam` decimal(10,2) DEFAULT NULL,
  `draft` decimal(10,2) DEFAULT NULL,
  `zona` varchar(5) DEFAULT NULL,
  `id_petugas` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_kapal`
--

INSERT INTO `data_kapal` (`MMSI`, `nama_kapal`, `callsign`, `jenis_kapal`, `GT`, `LOA`, `beam`, `draft`, `zona`, `id_petugas`) VALUES
(1231241, 'ABM JINJU', 'YCKK2', 'TUG/TOW', 3600.00, 130.00, 20.00, 8.00, 'C', '001'),
(5212345, 'WAN HAI 309', 'YCIN2', 'TANKER', 4.00, 4.00, 4.00, 4.00, 'C', '001'),
(52314421, 'SMS VOSPER', 'PMJV12', 'TUG/TOW', 2321.00, 50.00, 15.00, 12.00, 'C', '003'),
(210511027, 'ABM INTAN', 'YDHY2', 'TUG/TOW', 7500.00, 13.00, 12.00, 5.00, 'C', '001');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_kapal`
--
ALTER TABLE `data_kapal`
  ADD PRIMARY KEY (`MMSI`),
  ADD KEY `fk_petugas_kapal` (`id_petugas`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `data_kapal`
--
ALTER TABLE `data_kapal`
  ADD CONSTRAINT `fk_petugas_kapal` FOREIGN KEY (`id_petugas`) REFERENCES `daftar_petugas` (`id_petugas`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
