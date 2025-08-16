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
-- Struktur dari tabel `data_agen`
--

CREATE TABLE `data_agen` (
  `id_agen` varchar(20) NOT NULL,
  `nama_agen` varchar(50) NOT NULL,
  `MMSI` int(10) DEFAULT NULL,
  `nama_kapten` varchar(50) NOT NULL,
  `keterangan` enum('In','Out','Passing') DEFAULT 'Passing',
  `nama_file` varchar(255) DEFAULT NULL,
  `id_petugas` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_agen`
--

INSERT INTO `data_agen` (`id_agen`, `nama_agen`, `MMSI`, `nama_kapten`, `keterangan`, `nama_file`, `id_petugas`) VALUES
('AGEN-001', 'PT AHMAD SENTOSA', 52314421, 'BUDI GUNAWAN', 'Out', 'PKK_6866c4df0e542.pdf', '003'),
('AGEN-002', 'PT AHMAD SENTOSA', 5212345, 'AGUS SUCIPTO', 'In', 'PKK_6866c4df0e542.pdf', '001'),
('AGEN-003', 'PT AHMAD SENTOSA', 210511027, 'BUDI CAHYONO', 'Out', 'PKK_6866c4df0e542.pdf', '001'),
('AGEN-004', 'PT. PRIMA LESTARI SEGARA ABADI', 1231241, 'CAHYONO SUMITRO', 'In', 'PKK_6867417d75893.pdf', '001');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_agen`
--
ALTER TABLE `data_agen`
  ADD PRIMARY KEY (`id_agen`),
  ADD KEY `MMSI` (`MMSI`),
  ADD KEY `fk_petugas` (`id_petugas`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `data_agen`
--
ALTER TABLE `data_agen`
  ADD CONSTRAINT `data_agen_ibfk_1` FOREIGN KEY (`MMSI`) REFERENCES `data_kapal` (`MMSI`),
  ADD CONSTRAINT `fk_petugas` FOREIGN KEY (`id_petugas`) REFERENCES `daftar_petugas` (`id_petugas`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
