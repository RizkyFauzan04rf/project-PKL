-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Okt 2026 pada 15.15
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi_tkj`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi_harian`
--

CREATE TABLE `absensi_harian` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `waktu_absen` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi_harian`
--

INSERT INTO `absensi_harian` (`id`, `kelas_id`, `tanggal`, `dibuat_oleh`, `waktu_absen`) VALUES
(1, 1, '2026-09-17', 5, '2026-09-17 23:43:55'),
(2, 1, '2026-09-22', 5, '2026-09-22 23:17:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `absensi_mapel`
--

CREATE TABLE `absensi_mapel` (
  `id` int(11) NOT NULL,
  `mengajar_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_ke` int(11) NOT NULL,
  `dibuat_oleh` int(11) NOT NULL,
  `waktu_absen` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `absensi_mapel`
--

INSERT INTO `absensi_mapel` (`id`, `mengajar_id`, `tanggal`, `jam_ke`, `dibuat_oleh`, `waktu_absen`) VALUES
(1, 1, '2026-09-22', 1, 3, '2026-09-22 23:15:34'),
(2, 1, '2026-09-22', 2, 3, '2026-09-22 23:15:54'),
(3, 1, '2026-09-22', 3, 3, '2026-09-22 23:18:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_absensi_harian`
--

CREATE TABLE `detail_absensi_harian` (
  `id` int(11) NOT NULL,
  `absensi_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `status` enum('Hadir','Sakit','Izin','Alpa') NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_absensi_harian`
--

INSERT INTO `detail_absensi_harian` (`id`, `absensi_id`, `siswa_id`, `status`, `keterangan`) VALUES
(1, 1, 1, 'Hadir', NULL),
(2, 1, 2, 'Hadir', NULL),
(3, 1, 3, 'Hadir', NULL),
(4, 1, 4, 'Hadir', NULL),
(5, 1, 5, 'Hadir', NULL),
(6, 1, 6, 'Hadir', NULL),
(7, 1, 7, 'Hadir', NULL),
(8, 1, 8, 'Hadir', NULL),
(9, 1, 9, 'Hadir', NULL),
(10, 1, 11, 'Hadir', NULL),
(11, 1, 10, 'Hadir', NULL),
(12, 1, 12, 'Hadir', NULL),
(13, 1, 13, 'Hadir', NULL),
(14, 1, 14, 'Hadir', NULL),
(15, 1, 15, 'Hadir', NULL),
(16, 1, 16, 'Hadir', NULL),
(17, 1, 17, 'Hadir', NULL),
(18, 1, 22, 'Hadir', NULL),
(19, 1, 18, 'Hadir', NULL),
(20, 1, 23, 'Hadir', NULL),
(21, 1, 19, 'Hadir', NULL),
(22, 1, 20, 'Hadir', NULL),
(23, 1, 21, 'Hadir', NULL),
(24, 1, 24, 'Hadir', NULL),
(25, 1, 32, 'Hadir', NULL),
(26, 1, 25, 'Hadir', NULL),
(27, 1, 26, 'Hadir', NULL),
(28, 1, 27, 'Hadir', NULL),
(29, 1, 28, 'Hadir', NULL),
(30, 1, 31, 'Hadir', NULL),
(31, 1, 29, 'Hadir', NULL),
(32, 1, 30, 'Hadir', NULL),
(33, 2, 1, 'Hadir', NULL),
(34, 2, 2, 'Hadir', NULL),
(35, 2, 3, 'Hadir', NULL),
(36, 2, 4, 'Hadir', NULL),
(37, 2, 5, 'Hadir', NULL),
(38, 2, 6, 'Hadir', NULL),
(39, 2, 7, 'Hadir', NULL),
(40, 2, 8, 'Hadir', NULL),
(41, 2, 9, 'Hadir', NULL),
(42, 2, 11, 'Hadir', NULL),
(43, 2, 10, 'Hadir', NULL),
(44, 2, 12, 'Hadir', NULL),
(45, 2, 13, 'Hadir', NULL),
(46, 2, 14, 'Hadir', NULL),
(47, 2, 15, 'Hadir', NULL),
(48, 2, 16, 'Hadir', NULL),
(49, 2, 17, 'Hadir', NULL),
(50, 2, 22, 'Hadir', NULL),
(51, 2, 18, 'Hadir', NULL),
(52, 2, 23, 'Hadir', NULL),
(53, 2, 19, 'Hadir', NULL),
(54, 2, 20, 'Hadir', NULL),
(55, 2, 21, 'Hadir', NULL),
(56, 2, 24, 'Hadir', NULL),
(57, 2, 32, 'Hadir', NULL),
(58, 2, 25, 'Hadir', NULL),
(59, 2, 26, 'Hadir', NULL),
(60, 2, 27, 'Hadir', NULL),
(61, 2, 28, 'Hadir', NULL),
(62, 2, 31, 'Hadir', NULL),
(63, 2, 29, 'Hadir', NULL),
(64, 2, 30, 'Hadir', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_absensi_mapel`
--

CREATE TABLE `detail_absensi_mapel` (
  `id` int(11) NOT NULL,
  `absensi_mapel_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `status` enum('Hadir','Sakit','Izin','Alpa','Cabut') NOT NULL,
  `sumber` enum('otomatis','guru') DEFAULT 'otomatis',
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_absensi_mapel`
--

INSERT INTO `detail_absensi_mapel` (`id`, `absensi_mapel_id`, `siswa_id`, `status`, `sumber`, `keterangan`) VALUES
(1, 1, 1, 'Hadir', 'guru', ''),
(2, 1, 2, 'Hadir', 'guru', ''),
(3, 1, 3, 'Hadir', 'guru', ''),
(4, 1, 4, 'Hadir', 'guru', ''),
(5, 1, 5, 'Hadir', 'guru', ''),
(6, 1, 6, 'Hadir', 'guru', ''),
(7, 1, 7, 'Hadir', 'guru', ''),
(8, 1, 8, 'Hadir', 'guru', ''),
(9, 1, 9, 'Hadir', 'guru', ''),
(10, 1, 11, 'Hadir', 'guru', ''),
(11, 1, 10, 'Hadir', 'guru', ''),
(12, 1, 12, 'Hadir', 'guru', ''),
(13, 1, 13, 'Hadir', 'guru', ''),
(14, 1, 14, 'Hadir', 'guru', ''),
(15, 1, 15, 'Hadir', 'guru', ''),
(16, 1, 16, 'Hadir', 'guru', ''),
(17, 1, 17, 'Hadir', 'guru', ''),
(18, 1, 22, 'Hadir', 'guru', ''),
(19, 1, 18, 'Hadir', 'guru', ''),
(20, 1, 23, 'Hadir', 'guru', ''),
(21, 1, 19, 'Hadir', 'guru', ''),
(22, 1, 20, 'Hadir', 'guru', ''),
(23, 1, 21, 'Hadir', 'guru', ''),
(24, 1, 24, 'Hadir', 'guru', ''),
(25, 1, 32, 'Hadir', 'guru', ''),
(26, 1, 25, 'Hadir', 'guru', ''),
(27, 1, 26, 'Hadir', 'guru', ''),
(28, 1, 27, 'Hadir', 'guru', ''),
(29, 1, 28, 'Hadir', 'guru', ''),
(30, 1, 31, 'Hadir', 'guru', ''),
(31, 1, 29, 'Hadir', 'guru', ''),
(32, 1, 30, 'Hadir', 'guru', ''),
(33, 2, 1, 'Hadir', 'guru', ''),
(34, 2, 2, 'Hadir', 'guru', ''),
(35, 2, 3, 'Hadir', 'guru', ''),
(36, 2, 4, 'Hadir', 'guru', ''),
(37, 2, 5, 'Hadir', 'guru', ''),
(38, 2, 6, 'Hadir', 'guru', ''),
(39, 2, 7, 'Hadir', 'guru', ''),
(40, 2, 8, 'Hadir', 'guru', ''),
(41, 2, 9, 'Hadir', 'guru', ''),
(42, 2, 11, 'Hadir', 'guru', ''),
(43, 2, 10, 'Hadir', 'guru', ''),
(44, 2, 12, 'Hadir', 'guru', ''),
(45, 2, 13, 'Hadir', 'guru', ''),
(46, 2, 14, 'Hadir', 'guru', ''),
(47, 2, 15, 'Hadir', 'guru', ''),
(48, 2, 16, 'Hadir', 'guru', ''),
(49, 2, 17, 'Hadir', 'guru', ''),
(50, 2, 22, 'Hadir', 'guru', ''),
(51, 2, 18, 'Hadir', 'guru', ''),
(52, 2, 23, 'Hadir', 'guru', ''),
(53, 2, 19, 'Hadir', 'guru', ''),
(54, 2, 20, 'Hadir', 'guru', ''),
(55, 2, 21, 'Hadir', 'guru', ''),
(56, 2, 24, 'Hadir', 'guru', ''),
(57, 2, 32, 'Hadir', 'guru', ''),
(58, 2, 25, 'Hadir', 'guru', ''),
(59, 2, 26, 'Hadir', 'guru', ''),
(60, 2, 27, 'Hadir', 'guru', ''),
(61, 2, 28, 'Hadir', 'guru', ''),
(62, 2, 31, 'Hadir', 'guru', ''),
(63, 2, 29, 'Hadir', 'guru', ''),
(64, 2, 30, 'Hadir', 'guru', ''),
(65, 3, 1, 'Hadir', 'guru', ''),
(66, 3, 2, 'Hadir', 'guru', ''),
(67, 3, 3, 'Cabut', 'guru', 'sabut'),
(68, 3, 4, 'Hadir', 'guru', ''),
(69, 3, 5, 'Hadir', 'guru', ''),
(70, 3, 6, 'Hadir', 'guru', ''),
(71, 3, 7, 'Hadir', 'guru', ''),
(72, 3, 8, 'Hadir', 'guru', ''),
(73, 3, 9, 'Hadir', 'guru', ''),
(74, 3, 11, 'Hadir', 'guru', ''),
(75, 3, 10, 'Hadir', 'guru', ''),
(76, 3, 12, 'Hadir', 'guru', ''),
(77, 3, 13, 'Hadir', 'guru', ''),
(78, 3, 14, 'Hadir', 'guru', ''),
(79, 3, 15, 'Hadir', 'guru', ''),
(80, 3, 16, 'Hadir', 'guru', ''),
(81, 3, 17, 'Hadir', 'guru', ''),
(82, 3, 22, 'Hadir', 'guru', ''),
(83, 3, 18, 'Hadir', 'guru', ''),
(84, 3, 23, 'Hadir', 'guru', ''),
(85, 3, 19, 'Hadir', 'guru', ''),
(86, 3, 20, 'Hadir', 'guru', ''),
(87, 3, 21, 'Hadir', 'guru', ''),
(88, 3, 24, 'Hadir', 'guru', ''),
(89, 3, 32, 'Hadir', 'guru', ''),
(90, 3, 25, 'Hadir', 'guru', ''),
(91, 3, 26, 'Hadir', 'guru', ''),
(92, 3, 27, 'Hadir', 'guru', ''),
(93, 3, 28, 'Hadir', 'guru', ''),
(94, 3, 31, 'Hadir', 'guru', ''),
(95, 3, 29, 'Hadir', 'guru', ''),
(96, 3, 30, 'Hadir', 'guru', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guru`
--

INSERT INTO `guru` (`id`, `user_id`, `nip`, `nama`) VALUES
(1, 2, NULL, 'Siti Rahmah, S.Kom'),
(2, 3, NULL, 'Rizky Fauzan'),
(3, 4, NULL, 'Siti Rohani, S.Pd'),
(4, 7, NULL, 'Arbain, S.Pd'),
(5, 8, NULL, 'Dedi Heriyanto, S.Pd'),
(6, 9, NULL, 'Eka Rahman, S.Pd'),
(7, 10, NULL, 'Elfidah. S.Pd'),
(8, 11, NULL, 'Heri Sukanta, S.Pd'),
(9, 12, NULL, 'Irwansyah, S.Kom'),
(10, 13, NULL, 'M. Imanuddin, S.Pdi'),
(11, 14, NULL, 'Nurhaya, S.kom'),
(12, 15, NULL, 'Nurul Hasanah, S.Pd'),
(13, 16, NULL, 'Nuwairi Fadli, S.Pd'),
(14, 17, NULL, 'Rafai Matodang, S.Pd'),
(15, 18, NULL, 'Rubini, S.Pd'),
(16, 19, NULL, 'Suparman, S.Pd'),
(17, 20, NULL, 'Yulia Hagianda, S.Pd'),
(18, 21, NULL, 'Yuri Kharisma, S.T');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `tingkat` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `tingkat`) VALUES
(1, 'XI-A TA.2026/2027', 'XI'),
(2, 'XI-B TA.2026/2027', 'XI'),
(3, 'XI-C TA.2026/2027', 'XI'),
(4, 'XI-D TA.2026/2027', 'XI');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mata_pelajaran`
--

CREATE TABLE `mata_pelajaran` (
  `id` int(11) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`id`, `nama_mapel`) VALUES
(1, 'Teknik Komputer dan Jaringan'),
(2, 'Matematika'),
(3, 'Bahasa Indonesia'),
(4, 'Pendidikan Agama Islam'),
(5, 'Pendidikan  Kewarganegaraan'),
(6, 'Bahasa Inggris'),
(7, 'Produk Kreatif dan Kewirausahaan'),
(8, 'Pendidikan Jasmani, Olahraga, dan kesehatan'),
(9, 'Sejarah'),
(10, 'Teknik Jaringan Akses Telekomunikasi'),
(11, 'Administrasi Sistem Jaringan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `mengajar`
--

CREATE TABLE `mengajar` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mengajar`
--

INSERT INTO `mengajar` (`id`, `guru_id`, `kelas_id`, `mapel_id`, `tahun_ajaran_id`) VALUES
(1, 2, 1, 1, 1),
(2, 3, 1, 2, 1),
(3, 5, 1, 5, 1),
(4, 5, 2, 5, 1),
(5, 5, 3, 5, 1),
(6, 5, 4, 5, 1),
(7, 3, 2, 2, 1),
(8, 3, 3, 2, 1),
(9, 3, 4, 2, 1),
(10, 2, 2, 1, 1),
(11, 2, 3, 1, 1),
(12, 2, 4, 1, 1),
(13, 6, 1, 3, 1),
(14, 6, 2, 3, 1),
(15, 6, 3, 3, 1),
(16, 6, 4, 3, 1),
(17, 8, 1, 8, 1),
(18, 8, 2, 8, 1),
(19, 8, 3, 8, 1),
(20, 8, 4, 8, 1),
(21, 10, 1, 4, 1),
(22, 10, 2, 4, 1),
(23, 10, 3, 4, 1),
(24, 10, 4, 4, 1),
(25, 13, 1, 9, 1),
(26, 13, 2, 9, 1),
(27, 13, 3, 9, 1),
(28, 13, 4, 9, 1),
(29, 14, 1, 6, 1),
(30, 14, 2, 6, 1),
(31, 14, 3, 6, 1),
(32, 14, 4, 6, 1),
(33, 1, 1, 10, 1),
(34, 1, 2, 10, 1),
(35, 1, 3, 10, 1),
(36, 1, 4, 10, 1),
(37, 3, 1, 7, 1),
(38, 3, 2, 7, 1),
(39, 3, 3, 7, 1),
(40, 3, 4, 7, 1),
(41, 2, 1, 11, 1),
(42, 2, 2, 11, 1),
(43, 2, 3, 11, 1),
(44, 2, 4, 11, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sekretaris`
--

CREATE TABLE `sekretaris` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sekretaris`
--

INSERT INTO `sekretaris` (`id`, `user_id`, `kelas_id`) VALUES
(1, 5, 1),
(2, 22, 4),
(3, 23, 2),
(4, 24, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama`, `kelas_id`, `jenis_kelamin`, `status`) VALUES
(1, '26001', 'ADE LARASATI', 1, 'P', 'aktif'),
(2, '26002', 'ADELIA RAMADANI', 1, 'P', 'aktif'),
(3, '26003', 'AFDAN SYAPUTRA', 1, 'L', 'aktif'),
(4, '26004', 'AHMAD IQBAL', 1, 'L', 'aktif'),
(5, '26005', 'ANISA FATMALA', 1, 'P', 'aktif'),
(6, '26006', 'ARSITA ANJANI', 1, 'P', 'aktif'),
(7, '26007', 'AULIA ZAHRA', 1, 'P', 'aktif'),
(8, '26008', 'AZENG PRATIWI', 1, 'P', 'aktif'),
(9, '26009', 'DAVIS AFDILAL AKBAR', 1, 'L', 'aktif'),
(10, '26010', 'DINDA VEENAYA R.', 1, 'P', 'aktif'),
(11, '26011', 'DINDA JELITA A.', 1, 'P', 'aktif'),
(12, '26012', 'FADIL IRAWAN', 1, 'L', 'aktif'),
(13, '26013', 'FADIL RAMADAN', 1, 'L', 'aktif'),
(14, '26014', 'HAFIZA UMAIRA', 1, 'P', 'aktif'),
(15, '26015', 'INDRA', 1, 'L', 'aktif'),
(16, '26016', 'KEYLA LENITA', 1, 'P', 'aktif'),
(17, '26017', 'M. AFDIL LIANUR R.C', 1, 'L', 'aktif'),
(18, '26018', 'M. FERDI', 1, 'L', 'aktif'),
(19, '26019', 'M. SATRIA', 1, 'L', 'aktif'),
(20, '26020', 'MAHDALENA TARIGAN', 1, 'P', 'aktif'),
(21, '26021', 'MAIZA RENA PUTRI', 1, 'P', 'aktif'),
(22, '26022', 'M. DIKA PRATAMA', 1, 'L', 'aktif'),
(23, '26023', 'M. SAFRIL', 1, 'L', 'aktif'),
(24, '26024', 'NAZWA', 1, 'P', 'aktif'),
(25, '26025', 'PUTRI DARA', 1, 'P', 'aktif'),
(26, '26026', 'RAFA SYAHPUTRA', 1, 'L', 'aktif'),
(27, '26027', 'RENDY PRATAMA', 1, 'L', 'aktif'),
(28, '26028', 'RIZA PRADANA', 1, 'L', 'aktif'),
(29, '26029', 'SATRIA', 1, 'L', 'aktif'),
(30, '26030', 'WINDI AULIA PUTRI', 1, 'P', 'aktif'),
(31, '26031', 'SAHPITRA', 1, 'L', 'aktif'),
(32, '26032', 'NILA AGUSTIN', 1, 'P', 'aktif'),
(33, '27001', 'ADELIA ABEL', 2, 'P', 'aktif'),
(34, '27002', 'ADITIA RAHARDI P', 2, 'L', 'aktif'),
(35, '27003', 'AKBAR', 2, 'L', 'aktif'),
(36, '27004', 'AWLIYA FITRA', 2, 'P', 'aktif'),
(37, '27005', 'AZ-ZAHRA NURCITRA', 2, 'P', 'aktif'),
(38, '27006', 'DEA ALFIRA', 2, 'P', 'aktif'),
(39, '27007', 'DIAN', 2, 'L', 'aktif'),
(40, '27008', 'DIAN VANESA', 2, 'P', 'aktif'),
(41, '27009', 'EVA TRI WULANDARI', 2, 'P', 'aktif'),
(42, '27010', 'HANUM JULIANTI', 2, 'P', 'aktif'),
(43, '27011', 'ILHAM', 2, 'L', 'aktif'),
(44, '27012', 'KEVIN ARDANA', 2, 'L', 'aktif'),
(45, '27013', 'M. KELVIN', 2, 'L', 'aktif'),
(46, '27014', 'M. WISNU GALEHU', 2, 'L', 'aktif'),
(47, '27015', 'M. ALVINSYAH', 2, 'L', 'aktif'),
(48, '27016', 'M. ERWINSYAH PUTRA', 2, 'L', 'aktif'),
(49, '27017', 'M. SYAHRI', 2, 'L', 'aktif'),
(50, '27018', 'MAULIYA AHMAD R', 2, 'L', 'aktif'),
(51, '27019', 'HARI SANTOSO', 2, 'L', 'aktif'),
(52, '27020', 'JUMADIL', 2, 'L', 'aktif'),
(53, '27021', 'FRIMA RAFAEL GINTING', 2, 'L', 'aktif'),
(54, '27022', 'NURHAFIZAH', 2, 'P', 'aktif'),
(55, '27023', 'NUR IRFA KUMALA S', 2, 'P', 'aktif'),
(56, '27024', 'NURYAN SYAHPUTRA', 2, 'L', 'aktif'),
(57, '27025', 'RAFFA ALVHAR PUTRA', 2, 'L', 'aktif'),
(58, '27026', 'RENDI PRADANA', 2, 'L', 'aktif'),
(59, '27027', 'REVANSAYAH', 2, 'L', 'aktif'),
(60, '27028', 'REVYCA DEWI', 2, 'P', 'aktif'),
(61, '27029', 'RINI KARTIKA', 2, 'P', 'aktif'),
(62, '27030', 'RISKY KURNIAWAN', 2, 'L', 'aktif'),
(63, '27031', 'RISKY FADILAH', 2, 'L', 'aktif'),
(64, '27032', 'SURYADI LUBIS', 2, 'L', 'aktif'),
(65, '27033', 'WITRI WULANDARI', 2, 'P', 'aktif'),
(66, '27034', 'ZAHRATUS SITA', 2, 'P', 'aktif'),
(67, '27035', 'SALSABILA', 2, 'P', 'aktif'),
(96, '28001', 'ADINDA NIRMALA', 3, 'P', 'aktif'),
(97, '28002', 'AGIL MEILANI', 3, 'P', 'aktif'),
(98, '28003', 'ALISA ZAHRA', 3, 'P', 'aktif'),
(99, '28004', 'ANGGA RAMADHAN LUBIS', 3, 'L', 'aktif'),
(100, '28005', 'AUREL KIRANA DEWI', 3, 'P', 'aktif'),
(101, '28006', 'DAVI AMRI', 3, 'L', 'aktif'),
(102, '28007', 'DWI ARTIKA', 3, 'P', 'aktif'),
(103, '28008', 'ELI DAHLENA', 3, 'P', 'aktif'),
(104, '28009', 'FAIZ ALFINO', 3, 'L', 'aktif'),
(105, '28010', 'JULIANI SYAH PUTRI', 3, 'P', 'aktif'),
(106, '28011', 'KIRANA LESTARI', 3, 'P', 'aktif'),
(107, '28012', 'LAILA SYAGIF', 3, 'P', 'aktif'),
(108, '28013', 'MELANI PUTRI', 3, 'P', 'aktif'),
(109, '28014', 'MUHAMMAD DIMAS', 3, 'L', 'aktif'),
(110, '28015', 'MUHAMMAD FAZRI', 3, 'L', 'aktif'),
(111, '28016', 'MUHAMMAD LEGOWO', 3, 'L', 'aktif'),
(112, '28017', 'MUHAMMAD RAFLI HAMDANI', 3, 'L', 'aktif'),
(113, '28018', 'NABILA ARIN', 3, 'P', 'aktif'),
(114, '28019', 'NUR SAFINA ZAHRA', 3, 'P', 'aktif'),
(115, '28020', 'PURNOMO HADI', 3, 'L', 'aktif'),
(116, '28021', 'PUTRA AKBAR', 3, 'L', 'aktif'),
(117, '28022', 'RADA HANAFI', 3, 'L', 'aktif'),
(118, '28023', 'RAFKA PONARI', 3, 'L', 'aktif'),
(119, '28024', 'RAHMA CANTIKA', 3, 'P', 'aktif'),
(120, '28025', 'RAUL ADI SYAH PUTRA', 3, 'L', 'aktif'),
(121, '28026', 'REHAN', 3, 'L', 'aktif'),
(122, '28027', 'RISKI AL FEBRI', 3, 'L', 'aktif'),
(123, '28028', 'RANGGA UJI WAKA', 3, 'L', 'aktif'),
(124, '28029', 'SITI FATIMAH ZARA', 3, 'P', 'aktif'),
(125, '28030', 'SYAFIK NAHENDRA S', 3, 'L', 'aktif'),
(126, '28031', 'TOMMY KELVIN VALENDRA', 3, 'L', 'aktif'),
(127, '29001', 'ABDUL RAJAK', 4, 'L', 'aktif'),
(128, '29002', 'AGUS ARDIANSYAH', 4, 'L', 'aktif'),
(129, '29003', 'APRILIAN WAHYUDHA', 4, 'L', 'aktif'),
(130, '29004', 'AMELIA SYAHFITRI', 4, 'P', 'aktif'),
(131, '29005', 'ANGGUN APRILIA', 4, 'P', 'aktif'),
(132, '29006', 'CLARA ADELIA ARBY', 4, 'P', 'aktif'),
(133, '29007', 'DAVIN DWI RAMADHAN', 4, 'L', 'aktif'),
(134, '29008', 'DAVA AL RASID', 4, 'L', 'aktif'),
(135, '29009', 'DINDA JUWITA', 4, 'P', 'aktif'),
(136, '29010', 'ELSA TRIANA', 4, 'P', 'aktif'),
(137, '29011', 'FIKRI YARIS PRATAMA', 4, 'L', 'aktif'),
(138, '29012', 'FINA PUTRI', 4, 'P', 'aktif'),
(139, '29013', 'HERDIANYSAH EFENDI A', 4, 'L', 'aktif'),
(140, '29014', 'HIKMATUL AULIA', 4, 'P', 'aktif'),
(141, '29015', 'KHAIRIL RISKI', 4, 'L', 'aktif'),
(142, '29016', 'KHAIRUNISA', 4, 'P', 'aktif'),
(143, '29017', 'M. KHAIRUL', 4, 'L', 'aktif'),
(144, '29018', 'M. NIRWAN', 4, 'L', 'aktif'),
(145, '29019', 'M. RIDWAN', 4, 'L', 'aktif'),
(146, '29020', 'M. IQBAL AL HADI', 4, 'L', 'aktif'),
(147, '29021', 'M. KHAIRUL TAUFIQ', 4, 'L', 'aktif'),
(148, '29022', 'M. REO NAZMI', 4, 'L', 'aktif'),
(149, '29023', 'M. SABRIANDI', 4, 'L', 'aktif'),
(150, '29024', 'M. SAFRIL', 4, 'L', 'aktif'),
(151, '29025', 'MARISA ALIA', 4, 'P', 'aktif'),
(152, '29026', 'RADITYA ALFARIZI', 4, 'L', 'aktif'),
(153, '29027', 'RAVA FAHRIZAL', 4, 'L', 'aktif'),
(154, '29028', 'RAVI ADITYA', 4, 'L', 'aktif'),
(155, '29029', 'RICO PRASETYA', 4, 'L', 'aktif'),
(156, '29030', 'SAHMI WAFRI', 4, 'L', 'aktif'),
(157, '29031', 'SITI SALMAH', 4, 'P', 'aktif'),
(158, '29032', 'SUCI MONICA', 4, 'P', 'aktif'),
(159, '29033', 'TRIO PRATAMA', 4, 'L', 'aktif'),
(160, '29034', 'YUDHA PRATAMA', 4, 'L', 'aktif'),
(161, '29035', 'ZAHIYRA SALSA BILA', 4, 'P', 'aktif'),
(162, '29036', 'CINDY AULIA', 4, 'P', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id` int(11) NOT NULL,
  `tahun` varchar(20) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Tidak Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id`, `tahun`, `semester`, `status`) VALUES
(1, '2026/2027', 'Ganjil', 'Aktif'),
(2, '2026/2027', 'Genap', 'Tidak Aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','sekretaris') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$dibzsMcVTVvhyCZkqsHROu5i7M0r1H5jzyZsPXF3uqG9XkZOkkeM2', 'admin', '2026-09-12 17:09:10'),
(2, 'Siti Rahmah, S.Kom', 'rahmah', '$2y$10$VAyvRqGCS0Jg2GatMekJGeDD247vftdHt3CWpMPqBQOArVtFiocIy', 'guru', '2026-09-17 16:18:41'),
(3, 'Rizky Fauzan', 'fauzan', '$2y$10$dUM.wR9H0NSyHLmT0H1yEek9vfjalrATVvXexYXWiBVj8BvoQOFbC', 'guru', '2026-09-17 16:18:41'),
(4, 'Siti Rohani, S.Pd', 'siti', '$2y$10$8khVhLNL5GB0b/ptO2wr1OzGE/lu1OinUgvuG/Us47VWxCvLUpPBi', 'guru', '2026-09-17 16:18:41'),
(5, 'AZENG PRATIWI', 'azeng.pratiwi', '$2y$10$hav.sd16OT69GhekDbbFaOzU68g8AsR8EktFoV/H0NUHHmePkXNYW', 'sekretaris', '2026-09-17 16:18:41'),
(7, 'Arbain, S.Pd', 'arbain', '$2y$10$R1uQevMPrl8ncC4GhzMNoOYkt0Q4TRwgun9beMjGV3eVojkQ6BK0e', 'guru', '2026-09-22 16:38:26'),
(8, 'Dedi Heriyanto, S.Pd', 'dedi', '$2y$10$U/iHRncM0RIjjISqN2RfIeFlKpX3RG01CcJQlSgoXAvX3uYOIxJKC', 'guru', '2026-09-22 16:41:46'),
(9, 'Eka Rahman, S.Pd', 'eka', '$2y$10$Wc89dJFb2.4u/cXnXeyW.OCRhucZagjJska.O/DW7hQiHXptyOjgq', 'guru', '2026-09-22 16:42:22'),
(10, 'Elfidah. S.Pd', 'elfidah', '$2y$10$AiBlvsnmFNx6eErau.wW3.fLFGxZjMWOWEMXkISa47oP3tuIy1aoW', 'guru', '2026-09-22 16:42:55'),
(11, 'Heri Sukanta, S.Pd', 'sukanta', '$2y$10$jlyVK/l8jj1RyB1znbDzzOTsKhR/9JtRy4RPZvGmkL8aC9tdB3fjW', 'guru', '2026-09-22 16:44:24'),
(12, 'Irwansyah, S.Kom', 'iwan', '$2y$10$xeNkDDgBTiS6E0EHku/WYeLvNhLgxAigJvvYf8ohm11AkNyZIE04O', 'guru', '2026-09-22 16:44:45'),
(13, 'M. Imanuddin, S.Pdi', 'iman', '$2y$10$WLmTiDyp20FRGP3Cva7mtueRnzNRFENdFENxGLEp88XQby6O5kfWa', 'guru', '2026-09-22 16:45:12'),
(14, 'Nurhaya, S.kom', 'aya', '$2y$10$WgkxAGmqhmZqgpHo0E.tn./89xhUZSdu6YRAE0KzPUjVsESGnHmU6', 'guru', '2026-09-22 16:45:35'),
(15, 'Nurul Hasanah, S.Pd', 'nurul', '$2y$10$/l.JlMWLB1Xsfl6XfMpUgexE2UwecMmM8U2GAHEF3ElCjub5QR/0q', 'guru', '2026-09-22 16:46:08'),
(16, 'Nuwairi Fadli, S.Pd', 'heri', '$2y$10$2Jb1xMGK8sD2rFpIqgJJqedEHoKA.qOPnSP3sfbenYiyLM9m0RdMe', 'guru', '2026-09-22 16:47:37'),
(17, 'Rafai Matodang, S.Pd', 'rafai', '$2y$10$/XLpbI97gxvxC1H51beYHOnZ8P/7i7VPw1wWESVAylFB0wbA9OY2e', 'guru', '2026-09-22 16:48:02'),
(18, 'Rubini, S.Pd', 'rubini', '$2y$10$zLYE4Fcla3hyewKG17rju.FtAvxvCM8vEwIqjEJaqyBQDWK0hJqw6', 'guru', '2026-09-22 16:48:26'),
(19, 'Suparman, S.Pd', 'suparman', '$2y$10$RF01m8vw/bkzhZ2MftDW4OkVsgJTMDNS8m9qH9mL1ib4aIMcuCthG', 'guru', '2026-09-22 16:49:57'),
(20, 'Yulia Hagianda, S.Pd', 'yuli', '$2y$10$.VTqAPMd4HBbMTelGWQaeOxsNgU6D5pEKLHk5iHiU3uGI0DvK7O1O', 'guru', '2026-09-22 16:50:20'),
(21, 'Yuri Kharisma, S.T', 'yuri', '$2y$10$c6yEZbfv2A0XMMigvKUoGe5.drJXIfkGVRqtg8nu2xVuKmyZz7.te', 'guru', '2026-09-22 16:51:01'),
(22, 'SUCI MONICA', 'monica', '$2y$10$39nUqEX.HTjVXV5uwj9TVuVyYbdgVVR/EUxmCvrAGul.zIqWTEZSC', 'sekretaris', '2026-09-23 16:23:02'),
(23, 'DEA ALFIRA', 'dea', '$2y$10$3r6aKpO.RqLT.9Zw3r52OuBZ2Z3xYIvJIyiCIfMomqaVsJmcq5.8q', 'sekretaris', '2026-09-23 16:24:09'),
(24, 'RAHMA CANTIKA', 'cantika', '$2y$10$ex2SZMTsHfbyDwCVS9p9nu.JORwCjxUVH98HGV0ANcYqBgIR4qqta', 'sekretaris', '2026-09-23 16:25:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','guru','sekretaris') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role`) VALUES
(1, 1, 'admin'),
(3, 2, 'guru'),
(2, 3, 'guru'),
(4, 4, 'guru'),
(5, 5, 'sekretaris'),
(6, 7, 'guru'),
(7, 8, 'guru'),
(8, 9, 'guru'),
(9, 10, 'guru'),
(10, 11, 'guru'),
(11, 12, 'guru'),
(12, 13, 'guru'),
(13, 14, 'guru'),
(14, 15, 'guru'),
(15, 16, 'guru'),
(16, 17, 'guru'),
(17, 18, 'guru'),
(18, 19, 'guru'),
(19, 20, 'guru'),
(20, 21, 'guru'),
(21, 22, 'sekretaris'),
(22, 23, 'sekretaris'),
(23, 24, 'sekretaris');

-- --------------------------------------------------------

--
-- Struktur dari tabel `wali_kelas`
--

CREATE TABLE `wali_kelas` (
  `id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `tahun_ajaran_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `wali_kelas`
--

INSERT INTO `wali_kelas` (`id`, `guru_id`, `kelas_id`, `tahun_ajaran_id`) VALUES
(1, 1, 1, 1),
(2, 6, 2, 1),
(3, 3, 3, 1),
(4, 13, 4, 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `absensi_harian`
--
ALTER TABLE `absensi_harian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kelas_id` (`kelas_id`,`tanggal`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indeks untuk tabel `absensi_mapel`
--
ALTER TABLE `absensi_mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mengajar_id` (`mengajar_id`,`tanggal`,`jam_ke`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indeks untuk tabel `detail_absensi_harian`
--
ALTER TABLE `detail_absensi_harian`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `absensi_id` (`absensi_id`,`siswa_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `detail_absensi_mapel`
--
ALTER TABLE `detail_absensi_mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `absensi_mapel_id` (`absensi_mapel_id`,`siswa_id`),
  ADD KEY `siswa_id` (`siswa_id`);

--
-- Indeks untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `mengajar`
--
ALTER TABLE `mengajar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `mapel_id` (`mapel_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- Indeks untuk tabel `sekretaris`
--
ALTER TABLE `sekretaris`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indeks untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indeks untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role`);

--
-- Indeks untuk tabel `wali_kelas`
--
ALTER TABLE `wali_kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guru_id` (`guru_id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `tahun_ajaran_id` (`tahun_ajaran_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `absensi_harian`
--
ALTER TABLE `absensi_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `absensi_mapel`
--
ALTER TABLE `absensi_mapel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `detail_absensi_harian`
--
ALTER TABLE `detail_absensi_harian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT untuk tabel `detail_absensi_mapel`
--
ALTER TABLE `detail_absensi_mapel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT untuk tabel `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `mata_pelajaran`
--
ALTER TABLE `mata_pelajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `mengajar`
--
ALTER TABLE `mengajar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `sekretaris`
--
ALTER TABLE `sekretaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT untuk tabel `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `wali_kelas`
--
ALTER TABLE `wali_kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `absensi_harian`
--
ALTER TABLE `absensi_harian`
  ADD CONSTRAINT `absensi_harian_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_harian_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `absensi_mapel`
--
ALTER TABLE `absensi_mapel`
  ADD CONSTRAINT `absensi_mapel_ibfk_1` FOREIGN KEY (`mengajar_id`) REFERENCES `mengajar` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_mapel_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_absensi_harian`
--
ALTER TABLE `detail_absensi_harian`
  ADD CONSTRAINT `detail_absensi_harian_ibfk_1` FOREIGN KEY (`absensi_id`) REFERENCES `absensi_harian` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_absensi_harian_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_absensi_mapel`
--
ALTER TABLE `detail_absensi_mapel`
  ADD CONSTRAINT `detail_absensi_mapel_ibfk_1` FOREIGN KEY (`absensi_mapel_id`) REFERENCES `absensi_mapel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_absensi_mapel_ibfk_2` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `mengajar`
--
ALTER TABLE `mengajar`
  ADD CONSTRAINT `mengajar_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mengajar_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mengajar_ibfk_3` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mengajar_ibfk_4` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sekretaris`
--
ALTER TABLE `sekretaris`
  ADD CONSTRAINT `sekretaris_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sekretaris_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`);

--
-- Ketidakleluasaan untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `wali_kelas`
--
ALTER TABLE `wali_kelas`
  ADD CONSTRAINT `wali_kelas_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wali_kelas_ibfk_2` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wali_kelas_ibfk_3` FOREIGN KEY (`tahun_ajaran_id`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
