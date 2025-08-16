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
-- Struktur dari tabel `data_monitor`
--

CREATE TABLE `data_monitor` (
  `id_monitor` int(11) NOT NULL,
  `id_petugas` varchar(100) NOT NULL,
  `waktu` datetime DEFAULT current_timestamp(),
  `id_agen` varchar(20) DEFAULT NULL,
  `MMSI` int(11) NOT NULL,
  `pelabuhan_asal` varchar(50) NOT NULL,
  `pelabuhan_tujuan` varchar(50) NOT NULL,
  `ETA` datetime DEFAULT NULL,
  `jenis_muatan` varchar(50) NOT NULL,
  `jumlah_muatan` varchar(25) NOT NULL,
  `jumlah_kru` int(11) NOT NULL,
  `keterangan` enum('In','Out','Passing') DEFAULT 'Passing',
  `info_penting` varchar(200) DEFAULT NULL,
  `pelabuhan` enum('Cirebon','Patimban','Indramayu') DEFAULT 'Cirebon',
  `ATA` datetime DEFAULT NULL,
  `waktu_keberangkatan` datetime DEFAULT NULL,
  `maksud_kedatangan` varchar(100) DEFAULT NULL,
  `draft_depan` decimal(10,2) DEFAULT NULL,
  `draft_belakang` decimal(10,2) DEFAULT NULL,
  `nama_kapten` varchar(25) DEFAULT NULL,
  `latitude` varchar(10) DEFAULT NULL,
  `longitude` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `data_monitor`
--

INSERT INTO `data_monitor` (`id_monitor`, `id_petugas`, `waktu`, `id_agen`, `MMSI`, `pelabuhan_asal`, `pelabuhan_tujuan`, `ETA`, `jenis_muatan`, `jumlah_muatan`, `jumlah_kru`, `keterangan`, `info_penting`, `pelabuhan`, `ATA`, `waktu_keberangkatan`, `maksud_kedatangan`, `draft_depan`, `draft_belakang`, `nama_kapten`, `latitude`, `longitude`) VALUES
(1, '003', '2025-08-03 23:27:33', 'AGEN-001', 52314421, 'CIREBON', 'MAKASSAR', '2025-06-21 23:25:00', 'BATU BARA', '50 Ton', 5, 'Out', NULL, 'Cirebon', NULL, '2025-06-20 23:30:00', NULL, 12.00, 14.00, 'SUDRAJAT', NULL, NULL),
(3, '004', '2025-07-04 10:15:14', NULL, 1231241, 'TANA PASER', 'MAKASSAR', '2025-07-07 10:13:00', 'BATU BARA', '1500 TON', 7, 'Passing', 'Kru kapal ada yang sakit', 'Cirebon', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', 5.00, 6.00, 'CAHYONO SUMITRO', NULL, NULL),
(4, '001', '2025-07-29 20:33:51', 'AGEN-002', 5212345, 'jakarta', 'Cirebon', '2025-07-14 21:37:04', 'batubara', '100 ton', 10, 'In', 'terdapat kebocoran di lambung kapal', 'Cirebon', NULL, NULL, 'bongkar muatan', 5.00, 4.00, 'rizky resi julian', '-2.123214', '4.112324');

--
-- Trigger `data_monitor`
--
DELIMITER $$
CREATE TRIGGER `set_keterangan_data_monitor` BEFORE INSERT ON `data_monitor` FOR EACH ROW BEGIN
    IF NEW.id_agen IS NOT NULL THEN
        -- Jika id_agen ada, ambil keterangan dari tabel agen
        SET NEW.keterangan = (SELECT keterangan FROM data_agen WHERE id_agen = NEW.id_agen);
    ELSE
        -- Jika id_agen kosong, gunakan keterangan default 'passing'
        SET NEW.keterangan = 'passing';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `set_mmsi_data_monitor` BEFORE INSERT ON `data_monitor` FOR EACH ROW BEGIN
    IF NEW.id_agen IS NOT NULL THEN
        -- Jika id_agen ada, cari MMSI dari tabel agen
        SET NEW.MMSI = (SELECT MMSI FROM data_agen WHERE id_agen = NEW.id_agen);
    END IF;
    -- Jika id_agen NULL, biarkan MMSI yang diinput tetap
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_keterangan_data_monitor` BEFORE UPDATE ON `data_monitor` FOR EACH ROW BEGIN
    IF NEW.id_agen IS NOT NULL THEN
        SET NEW.keterangan = (SELECT keterangan FROM data_agen WHERE id_agen = NEW.id_agen);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_mmsi_data_monitor` BEFORE UPDATE ON `data_monitor` FOR EACH ROW BEGIN
    IF NEW.id_agen IS NOT NULL THEN
        SET NEW.MMSI = (SELECT MMSI FROM data_agen WHERE id_agen = NEW.id_agen);
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `data_monitor`
--
ALTER TABLE `data_monitor`
  ADD PRIMARY KEY (`id_monitor`),
  ADD UNIQUE KEY `id_agen` (`id_agen`),
  ADD KEY `id_petugas` (`id_petugas`),
  ADD KEY `MMSI` (`MMSI`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `data_monitor`
--
ALTER TABLE `data_monitor`
  ADD CONSTRAINT `data_monitor_ibfk_1` FOREIGN KEY (`id_agen`) REFERENCES `data_agen` (`id_agen`),
  ADD CONSTRAINT `data_monitor_ibfk_2` FOREIGN KEY (`MMSI`) REFERENCES `data_kapal` (`MMSI`),
  ADD CONSTRAINT `data_monitor_ibfk_3` FOREIGN KEY (`id_petugas`) REFERENCES `daftar_petugas` (`id_petugas`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
