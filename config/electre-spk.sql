-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 06:41 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `electre-spk`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_agregat`
--

CREATE TABLE `tbl_agregat` (
  `id` int(11) NOT NULL,
  `alternatif_i` int(11) NOT NULL,
  `alternatif_j` int(11) NOT NULL,
  `nilai_e` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_agregat`
--

INSERT INTO `tbl_agregat` (`id`, `alternatif_i`, `alternatif_j`, `nilai_e`) VALUES
(1, 1, 3, 0),
(2, 1, 4, 1),
(3, 1, 5, 1),
(4, 1, 6, 1),
(5, 1, 7, 0),
(6, 1, 8, 0),
(7, 1, 9, 1),
(8, 1, 10, 0),
(9, 1, 11, 1),
(10, 3, 1, 1),
(11, 3, 4, 1),
(12, 3, 5, 1),
(13, 3, 6, 1),
(14, 3, 7, 0),
(15, 3, 8, 0),
(16, 3, 9, 1),
(17, 3, 10, 0),
(18, 3, 11, 1),
(19, 4, 1, 0),
(20, 4, 3, 0),
(21, 4, 5, 0),
(22, 4, 6, 0),
(23, 4, 7, 0),
(24, 4, 8, 0),
(25, 4, 9, 1),
(26, 4, 10, 0),
(27, 4, 11, 1),
(28, 5, 1, 0),
(29, 5, 3, 0),
(30, 5, 4, 1),
(31, 5, 6, 0),
(32, 5, 7, 0),
(33, 5, 8, 0),
(34, 5, 9, 1),
(35, 5, 10, 0),
(36, 5, 11, 1),
(37, 6, 1, 0),
(38, 6, 3, 0),
(39, 6, 4, 1),
(40, 6, 5, 0),
(41, 6, 7, 0),
(42, 6, 8, 0),
(43, 6, 9, 1),
(44, 6, 10, 1),
(45, 6, 11, 1),
(46, 7, 1, 0),
(47, 7, 3, 0),
(48, 7, 4, 1),
(49, 7, 5, 0),
(50, 7, 6, 1),
(51, 7, 8, 1),
(52, 7, 9, 1),
(53, 7, 10, 1),
(54, 7, 11, 1),
(55, 8, 1, 0),
(56, 8, 3, 0),
(57, 8, 4, 1),
(58, 8, 5, 0),
(59, 8, 6, 0),
(60, 8, 7, 0),
(61, 8, 9, 1),
(62, 8, 10, 1),
(63, 8, 11, 1),
(64, 9, 1, 0),
(65, 9, 3, 0),
(66, 9, 4, 0),
(67, 9, 5, 0),
(68, 9, 6, 0),
(69, 9, 7, 0),
(70, 9, 8, 0),
(71, 9, 10, 0),
(72, 9, 11, 0),
(73, 10, 1, 0),
(74, 10, 3, 0),
(75, 10, 4, 0),
(76, 10, 5, 0),
(77, 10, 6, 0),
(78, 10, 7, 0),
(79, 10, 8, 0),
(80, 10, 9, 0),
(81, 10, 11, 0),
(82, 11, 1, 0),
(83, 11, 3, 0),
(84, 11, 4, 1),
(85, 11, 5, 0),
(86, 11, 6, 0),
(87, 11, 7, 0),
(88, 11, 8, 0),
(89, 11, 9, 1),
(90, 11, 10, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_alternatif`
--

CREATE TABLE `tbl_alternatif` (
  `id` int(11) NOT NULL,
  `nama_daerah` varchar(100) NOT NULL,
  `provinsi` enum('Jawa Timur','Jawa Tengah') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_alternatif`
--

INSERT INTO `tbl_alternatif` (`id`, `nama_daerah`, `provinsi`) VALUES
(1, 'Kota Malang', 'Jawa Timur'),
(3, 'Kota Semarang', 'Jawa Tengah'),
(4, 'Kabupaten Bojonegoro', 'Jawa Timur'),
(5, 'Kota Surabaya', 'Jawa Timur'),
(6, 'Kabupaten Gresik', 'Jawa Timur'),
(7, 'Kabupaten Klaten', 'Jawa Tengah'),
(8, 'Kabupaten Demak', 'Jawa Tengah'),
(9, 'Kabupaten Bondowoso', 'Jawa Timur'),
(10, 'Kabupaten Pemalang', 'Jawa Tengah'),
(11, 'Kabupaten Magelang', 'Jawa Tengah');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_concordance`
--

CREATE TABLE `tbl_concordance` (
  `id` int(11) NOT NULL,
  `alternatif_i` int(11) NOT NULL,
  `alternatif_j` int(11) NOT NULL,
  `nilai_concordance` decimal(15,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_concordance`
--

INSERT INTO `tbl_concordance` (`id`, `alternatif_i`, `alternatif_j`, `nilai_concordance`) VALUES
(1, 1, 3, '0.8600'),
(2, 1, 4, '0.7000'),
(3, 1, 5, '1.0000'),
(4, 1, 6, '0.7000'),
(5, 1, 7, '0.5600'),
(6, 1, 8, '0.7000'),
(7, 1, 9, '0.7000'),
(8, 1, 10, '0.7000'),
(9, 1, 11, '0.7000'),
(10, 3, 1, '0.9000'),
(11, 3, 4, '0.7000'),
(12, 3, 5, '0.9000'),
(13, 3, 6, '0.7000'),
(14, 3, 7, '0.7000'),
(15, 3, 8, '0.7000'),
(16, 3, 9, '0.7000'),
(17, 3, 10, '0.7000'),
(18, 3, 11, '0.7000'),
(19, 4, 1, '0.3000'),
(20, 4, 3, '0.3000'),
(21, 4, 5, '0.3000'),
(22, 4, 6, '0.4400'),
(23, 4, 7, '0.3000'),
(24, 4, 8, '0.6000'),
(25, 4, 9, '1.0000'),
(26, 4, 10, '0.7000'),
(27, 4, 11, '1.0000'),
(28, 5, 1, '0.8000'),
(29, 5, 3, '0.6600'),
(30, 5, 4, '0.7000'),
(31, 5, 6, '0.7000'),
(32, 5, 7, '0.5600'),
(33, 5, 8, '0.7000'),
(34, 5, 9, '0.7000'),
(35, 5, 10, '0.7000'),
(36, 5, 11, '0.7000'),
(37, 6, 1, '0.3000'),
(38, 6, 3, '0.3000'),
(39, 6, 4, '0.7000'),
(40, 6, 5, '0.3000'),
(41, 6, 7, '0.5600'),
(42, 6, 8, '0.5600'),
(43, 6, 9, '0.7000'),
(44, 6, 10, '0.7000'),
(45, 6, 11, '0.7000'),
(46, 7, 1, '0.4400'),
(47, 7, 3, '0.4400'),
(48, 7, 4, '1.0000'),
(49, 7, 5, '0.4400'),
(50, 7, 6, '0.6400'),
(51, 7, 8, '1.0000'),
(52, 7, 9, '1.0000'),
(53, 7, 10, '0.7000'),
(54, 7, 11, '1.0000'),
(55, 8, 1, '0.4400'),
(56, 8, 3, '0.3000'),
(57, 8, 4, '1.0000'),
(58, 8, 5, '0.4400'),
(59, 8, 6, '0.4400'),
(60, 8, 7, '0.5600'),
(61, 8, 9, '1.0000'),
(62, 8, 10, '0.7000'),
(63, 8, 11, '1.0000'),
(64, 9, 1, '0.3000'),
(65, 9, 3, '0.3000'),
(66, 9, 4, '0.8600'),
(67, 9, 5, '0.3000'),
(68, 9, 6, '0.3000'),
(69, 9, 7, '0.3000'),
(70, 9, 8, '0.6000'),
(71, 9, 10, '0.7000'),
(72, 9, 11, '0.8600'),
(73, 10, 1, '0.3000'),
(74, 10, 3, '0.3000'),
(75, 10, 4, '0.4000'),
(76, 10, 5, '0.3000'),
(77, 10, 6, '0.3000'),
(78, 10, 7, '0.3000'),
(79, 10, 8, '0.4000'),
(80, 10, 9, '0.5400'),
(81, 10, 11, '0.4000'),
(82, 11, 1, '0.3000'),
(83, 11, 3, '0.3000'),
(84, 11, 4, '1.0000'),
(85, 11, 5, '0.3000'),
(86, 11, 6, '0.4400'),
(87, 11, 7, '0.3000'),
(88, 11, 8, '0.6000'),
(89, 11, 9, '1.0000'),
(90, 11, 10, '0.7000');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_discordance`
--

CREATE TABLE `tbl_discordance` (
  `id` int(11) NOT NULL,
  `alternatif_i` int(11) NOT NULL,
  `alternatif_j` int(11) NOT NULL,
  `nilai_discordance` decimal(15,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_discordance`
--

INSERT INTO `tbl_discordance` (`id`, `alternatif_i`, `alternatif_j`, `nilai_discordance`) VALUES
(1, 1, 3, '1.0000'),
(2, 1, 4, '0.7181'),
(3, 1, 5, '0.0000'),
(4, 1, 6, '0.6370'),
(5, 1, 7, '1.0000'),
(6, 1, 8, '0.8494'),
(7, 1, 9, '0.7181'),
(8, 1, 10, '0.8078'),
(9, 1, 11, '0.7181'),
(10, 3, 1, '0.3717'),
(11, 3, 4, '0.7181'),
(12, 3, 5, '0.3717'),
(13, 3, 6, '0.5759'),
(14, 3, 7, '1.0000'),
(15, 3, 8, '0.8494'),
(16, 3, 9, '0.7181'),
(17, 3, 10, '0.8078'),
(18, 3, 11, '0.7181'),
(19, 4, 1, '1.0000'),
(20, 4, 3, '1.0000'),
(21, 4, 5, '1.0000'),
(22, 4, 6, '1.0000'),
(23, 4, 7, '1.0000'),
(24, 4, 8, '1.0000'),
(25, 4, 9, '0.0000'),
(26, 4, 10, '1.0000'),
(27, 4, 11, '0.0000'),
(28, 5, 1, '1.0000'),
(29, 5, 3, '1.0000'),
(30, 5, 4, '0.7181'),
(31, 5, 6, '1.0000'),
(32, 5, 7, '1.0000'),
(33, 5, 8, '1.0000'),
(34, 5, 9, '0.7181'),
(35, 5, 10, '0.8078'),
(36, 5, 11, '0.7181'),
(37, 6, 1, '1.0000'),
(38, 6, 3, '1.0000'),
(39, 6, 4, '0.5386'),
(40, 6, 5, '0.9284'),
(41, 6, 7, '1.0000'),
(42, 6, 8, '1.0000'),
(43, 6, 9, '0.5386'),
(44, 6, 10, '0.7181'),
(45, 6, 11, '0.5386'),
(46, 7, 1, '0.9284'),
(47, 7, 3, '0.9284'),
(48, 7, 4, '0.0000'),
(49, 7, 5, '0.9284'),
(50, 7, 6, '0.5346'),
(51, 7, 8, '0.0000'),
(52, 7, 9, '0.0000'),
(53, 7, 10, '0.4319'),
(54, 7, 11, '0.0000'),
(55, 8, 1, '1.0000'),
(56, 8, 3, '1.0000'),
(57, 8, 4, '0.0000'),
(58, 8, 5, '0.9284'),
(59, 8, 6, '0.9284'),
(60, 8, 7, '1.0000'),
(61, 8, 9, '0.0000'),
(62, 8, 10, '0.5386'),
(63, 8, 11, '0.0000'),
(64, 9, 1, '1.0000'),
(65, 9, 3, '1.0000'),
(66, 9, 4, '1.0000'),
(67, 9, 5, '1.0000'),
(68, 9, 6, '1.0000'),
(69, 9, 7, '1.0000'),
(70, 9, 8, '1.0000'),
(71, 9, 10, '1.0000'),
(72, 9, 11, '1.0000'),
(73, 10, 1, '1.0000'),
(74, 10, 3, '1.0000'),
(75, 10, 4, '0.9284'),
(76, 10, 5, '1.0000'),
(77, 10, 6, '1.0000'),
(78, 10, 7, '1.0000'),
(79, 10, 8, '1.0000'),
(80, 10, 9, '0.9284'),
(81, 10, 11, '0.9284'),
(82, 11, 1, '1.0000'),
(83, 11, 3, '1.0000'),
(84, 11, 4, '0.0000'),
(85, 11, 5, '1.0000'),
(86, 11, 6, '1.0000'),
(87, 11, 7, '1.0000'),
(88, 11, 8, '1.0000'),
(89, 11, 9, '0.0000'),
(90, 11, 10, '1.0000');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_dominan_concordance`
--

CREATE TABLE `tbl_dominan_concordance` (
  `id` int(11) NOT NULL,
  `alternatif_i` int(11) NOT NULL,
  `alternatif_j` int(11) NOT NULL,
  `nilai_f` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_dominan_concordance`
--

INSERT INTO `tbl_dominan_concordance` (`id`, `alternatif_i`, `alternatif_j`, `nilai_f`) VALUES
(1, 1, 3, 1),
(2, 1, 4, 1),
(3, 1, 5, 1),
(4, 1, 6, 1),
(5, 1, 7, 0),
(6, 1, 8, 1),
(7, 1, 9, 1),
(8, 1, 10, 1),
(9, 1, 11, 1),
(10, 3, 1, 1),
(11, 3, 4, 1),
(12, 3, 5, 1),
(13, 3, 6, 1),
(14, 3, 7, 1),
(15, 3, 8, 1),
(16, 3, 9, 1),
(17, 3, 10, 1),
(18, 3, 11, 1),
(19, 4, 1, 0),
(20, 4, 3, 0),
(21, 4, 5, 0),
(22, 4, 6, 0),
(23, 4, 7, 0),
(24, 4, 8, 0),
(25, 4, 9, 1),
(26, 4, 10, 1),
(27, 4, 11, 1),
(28, 5, 1, 1),
(29, 5, 3, 1),
(30, 5, 4, 1),
(31, 5, 6, 1),
(32, 5, 7, 0),
(33, 5, 8, 1),
(34, 5, 9, 1),
(35, 5, 10, 1),
(36, 5, 11, 1),
(37, 6, 1, 0),
(38, 6, 3, 0),
(39, 6, 4, 1),
(40, 6, 5, 0),
(41, 6, 7, 0),
(42, 6, 8, 0),
(43, 6, 9, 1),
(44, 6, 10, 1),
(45, 6, 11, 1),
(46, 7, 1, 0),
(47, 7, 3, 0),
(48, 7, 4, 1),
(49, 7, 5, 0),
(50, 7, 6, 1),
(51, 7, 8, 1),
(52, 7, 9, 1),
(53, 7, 10, 1),
(54, 7, 11, 1),
(55, 8, 1, 0),
(56, 8, 3, 0),
(57, 8, 4, 1),
(58, 8, 5, 0),
(59, 8, 6, 0),
(60, 8, 7, 0),
(61, 8, 9, 1),
(62, 8, 10, 1),
(63, 8, 11, 1),
(64, 9, 1, 0),
(65, 9, 3, 0),
(66, 9, 4, 1),
(67, 9, 5, 0),
(68, 9, 6, 0),
(69, 9, 7, 0),
(70, 9, 8, 0),
(71, 9, 10, 1),
(72, 9, 11, 1),
(73, 10, 1, 0),
(74, 10, 3, 0),
(75, 10, 4, 0),
(76, 10, 5, 0),
(77, 10, 6, 0),
(78, 10, 7, 0),
(79, 10, 8, 0),
(80, 10, 9, 0),
(81, 10, 11, 0),
(82, 11, 1, 0),
(83, 11, 3, 0),
(84, 11, 4, 1),
(85, 11, 5, 0),
(86, 11, 6, 0),
(87, 11, 7, 0),
(88, 11, 8, 0),
(89, 11, 9, 1),
(90, 11, 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_dominan_discordance`
--

CREATE TABLE `tbl_dominan_discordance` (
  `id` int(11) NOT NULL,
  `alternatif_i` int(11) NOT NULL,
  `alternatif_j` int(11) NOT NULL,
  `nilai_g` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_dominan_discordance`
--

INSERT INTO `tbl_dominan_discordance` (`id`, `alternatif_i`, `alternatif_j`, `nilai_g`) VALUES
(1, 1, 3, 0),
(2, 1, 4, 1),
(3, 1, 5, 1),
(4, 1, 6, 1),
(5, 1, 7, 0),
(6, 1, 8, 0),
(7, 1, 9, 1),
(8, 1, 10, 0),
(9, 1, 11, 1),
(10, 3, 1, 1),
(11, 3, 4, 1),
(12, 3, 5, 1),
(13, 3, 6, 1),
(14, 3, 7, 0),
(15, 3, 8, 0),
(16, 3, 9, 1),
(17, 3, 10, 0),
(18, 3, 11, 1),
(19, 4, 1, 0),
(20, 4, 3, 0),
(21, 4, 5, 0),
(22, 4, 6, 0),
(23, 4, 7, 0),
(24, 4, 8, 0),
(25, 4, 9, 1),
(26, 4, 10, 0),
(27, 4, 11, 1),
(28, 5, 1, 0),
(29, 5, 3, 0),
(30, 5, 4, 1),
(31, 5, 6, 0),
(32, 5, 7, 0),
(33, 5, 8, 0),
(34, 5, 9, 1),
(35, 5, 10, 0),
(36, 5, 11, 1),
(37, 6, 1, 0),
(38, 6, 3, 0),
(39, 6, 4, 1),
(40, 6, 5, 0),
(41, 6, 7, 0),
(42, 6, 8, 0),
(43, 6, 9, 1),
(44, 6, 10, 1),
(45, 6, 11, 1),
(46, 7, 1, 0),
(47, 7, 3, 0),
(48, 7, 4, 1),
(49, 7, 5, 0),
(50, 7, 6, 1),
(51, 7, 8, 1),
(52, 7, 9, 1),
(53, 7, 10, 1),
(54, 7, 11, 1),
(55, 8, 1, 0),
(56, 8, 3, 0),
(57, 8, 4, 1),
(58, 8, 5, 0),
(59, 8, 6, 0),
(60, 8, 7, 0),
(61, 8, 9, 1),
(62, 8, 10, 1),
(63, 8, 11, 1),
(64, 9, 1, 0),
(65, 9, 3, 0),
(66, 9, 4, 0),
(67, 9, 5, 0),
(68, 9, 6, 0),
(69, 9, 7, 0),
(70, 9, 8, 0),
(71, 9, 10, 0),
(72, 9, 11, 0),
(73, 10, 1, 0),
(74, 10, 3, 0),
(75, 10, 4, 0),
(76, 10, 5, 0),
(77, 10, 6, 0),
(78, 10, 7, 0),
(79, 10, 8, 0),
(80, 10, 9, 0),
(81, 10, 11, 0),
(82, 11, 1, 0),
(83, 11, 3, 0),
(84, 11, 4, 1),
(85, 11, 5, 0),
(86, 11, 6, 0),
(87, 11, 7, 0),
(88, 11, 8, 0),
(89, 11, 9, 1),
(90, 11, 10, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_hasil`
--

CREATE TABLE `tbl_hasil` (
  `id` int(11) NOT NULL,
  `alternatif_id` int(11) NOT NULL,
  `phi` decimal(15,4) DEFAULT NULL,
  `ranking` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_hasil`
--

INSERT INTO `tbl_hasil` (`id`, `alternatif_id`, `phi`, `ranking`, `created_at`) VALUES
(1, 3, '6.0000', 1, '2026-06-16 04:54:27'),
(2, 7, '6.0000', 2, '2026-06-16 04:54:27'),
(3, 1, '4.0000', 3, '2026-06-16 04:54:27'),
(4, 8, '3.0000', 4, '2026-06-16 04:54:27'),
(5, 5, '1.0000', 5, '2026-06-16 04:54:27'),
(6, 6, '1.0000', 6, '2026-06-16 04:54:27'),
(7, 10, '-3.0000', 7, '2026-06-16 04:54:27'),
(8, 4, '-5.0000', 8, '2026-06-16 04:54:27'),
(9, 11, '-5.0000', 9, '2026-06-16 04:54:27'),
(10, 9, '-8.0000', 10, '2026-06-16 04:54:27');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kriteria`
--

CREATE TABLE `tbl_kriteria` (
  `id` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama_kriteria` varchar(100) NOT NULL,
  `bobot` decimal(8,4) NOT NULL,
  `tipe` enum('Benefit','Cost') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kriteria`
--

INSERT INTO `tbl_kriteria` (`id`, `kode`, `nama_kriteria`, `bobot`, `tipe`) VALUES
(1, 'C1', 'UHH', '0.1400', 'Benefit'),
(2, 'C2', 'HLS', '0.2000', 'Benefit'),
(4, 'C3', 'RLS', '0.2600', 'Benefit'),
(5, 'C4', 'Pengeluaran', '0.1000', 'Benefit'),
(6, 'C5', 'Kemiskinan', '0.3000', 'Cost');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_nilai`
--

CREATE TABLE `tbl_nilai` (
  `id` int(11) NOT NULL,
  `alternatif_id` int(11) NOT NULL,
  `kriteria_id` int(11) NOT NULL,
  `nilai` decimal(15,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_nilai`
--

INSERT INTO `tbl_nilai` (`id`, `alternatif_id`, `kriteria_id`, `nilai`) VALUES
(1, 1, 1, '3.0000'),
(2, 1, 2, '5.0000'),
(3, 4, 1, '2.0000'),
(4, 4, 2, '2.0000'),
(5, 4, 4, '2.0000'),
(6, 4, 5, '1.0000'),
(7, 4, 6, '3.0000'),
(8, 9, 1, '1.0000'),
(9, 9, 2, '2.0000'),
(10, 9, 4, '2.0000'),
(11, 9, 5, '1.0000'),
(12, 9, 6, '3.0000'),
(13, 6, 1, '2.0000'),
(14, 6, 2, '3.0000'),
(15, 6, 4, '4.0000'),
(16, 6, 5, '3.0000'),
(17, 6, 6, '4.0000'),
(18, 1, 4, '5.0000'),
(19, 1, 5, '5.0000'),
(20, 1, 6, '5.0000'),
(21, 5, 1, '3.0000'),
(22, 5, 2, '4.0000'),
(23, 5, 4, '5.0000'),
(24, 5, 5, '5.0000'),
(25, 5, 6, '5.0000'),
(26, 8, 1, '3.0000'),
(27, 8, 2, '2.0000'),
(28, 8, 4, '3.0000'),
(29, 8, 5, '1.0000'),
(30, 8, 6, '3.0000'),
(31, 7, 1, '5.0000'),
(32, 7, 2, '3.0000'),
(33, 7, 4, '3.0000'),
(34, 7, 5, '2.0000'),
(35, 7, 6, '3.0000'),
(36, 11, 1, '2.0000'),
(37, 11, 2, '2.0000'),
(38, 11, 4, '2.0000'),
(39, 11, 5, '1.0000'),
(40, 11, 6, '3.0000'),
(41, 10, 1, '1.0000'),
(42, 10, 2, '1.0000'),
(43, 10, 4, '1.0000'),
(44, 10, 5, '1.0000'),
(45, 10, 6, '2.0000'),
(46, 3, 1, '5.0000'),
(47, 3, 2, '5.0000'),
(48, 3, 4, '5.0000'),
(49, 3, 5, '4.0000'),
(50, 3, 6, '5.0000');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_normalisasi`
--

CREATE TABLE `tbl_normalisasi` (
  `id` int(11) NOT NULL,
  `alternatif_id` int(11) NOT NULL,
  `kriteria_id` int(11) NOT NULL,
  `nilai_r` decimal(15,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_normalisasi`
--

INSERT INTO `tbl_normalisasi` (`id`, `alternatif_id`, `kriteria_id`, `nilai_r`) VALUES
(1, 1, 1, '0.3145'),
(2, 1, 2, '0.4975'),
(3, 1, 4, '0.4527'),
(4, 1, 5, '0.5455'),
(5, 1, 6, '0.4226'),
(6, 3, 1, '0.5241'),
(7, 3, 2, '0.4975'),
(8, 3, 4, '0.4527'),
(9, 3, 5, '0.4364'),
(10, 3, 6, '0.4226'),
(11, 4, 1, '0.2097'),
(12, 4, 2, '0.1990'),
(13, 4, 4, '0.1811'),
(14, 4, 5, '0.1091'),
(15, 4, 6, '0.2535'),
(16, 5, 1, '0.3145'),
(17, 5, 2, '0.3980'),
(18, 5, 4, '0.4527'),
(19, 5, 5, '0.5455'),
(20, 5, 6, '0.4226'),
(21, 6, 1, '0.2097'),
(22, 6, 2, '0.2985'),
(23, 6, 4, '0.3621'),
(24, 6, 5, '0.3273'),
(25, 6, 6, '0.3381'),
(26, 7, 1, '0.5241'),
(27, 7, 2, '0.2985'),
(28, 7, 4, '0.2716'),
(29, 7, 5, '0.2182'),
(30, 7, 6, '0.2535'),
(31, 8, 1, '0.3145'),
(32, 8, 2, '0.1990'),
(33, 8, 4, '0.2716'),
(34, 8, 5, '0.1091'),
(35, 8, 6, '0.2535'),
(36, 9, 1, '0.1048'),
(37, 9, 2, '0.1990'),
(38, 9, 4, '0.1811'),
(39, 9, 5, '0.1091'),
(40, 9, 6, '0.2535'),
(41, 10, 1, '0.1048'),
(42, 10, 2, '0.0995'),
(43, 10, 4, '0.0905'),
(44, 10, 5, '0.1091'),
(45, 10, 6, '0.1690'),
(46, 11, 1, '0.2097'),
(47, 11, 2, '0.1990'),
(48, 11, 4, '0.1811'),
(49, 11, 5, '0.1091'),
(50, 11, 6, '0.2535');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_terbobot`
--

CREATE TABLE `tbl_terbobot` (
  `id` int(11) NOT NULL,
  `alternatif_id` int(11) NOT NULL,
  `kriteria_id` int(11) NOT NULL,
  `nilai_v` decimal(15,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_terbobot`
--

INSERT INTO `tbl_terbobot` (`id`, `alternatif_id`, `kriteria_id`, `nilai_v`) VALUES
(1, 1, 1, '0.0440'),
(2, 1, 2, '0.0995'),
(3, 1, 4, '0.1177'),
(4, 1, 5, '0.0546'),
(5, 1, 6, '0.1268'),
(6, 3, 1, '0.0734'),
(7, 3, 2, '0.0995'),
(8, 3, 4, '0.1177'),
(9, 3, 5, '0.0436'),
(10, 3, 6, '0.1268'),
(11, 4, 1, '0.0294'),
(12, 4, 2, '0.0398'),
(13, 4, 4, '0.0471'),
(14, 4, 5, '0.0109'),
(15, 4, 6, '0.0761'),
(16, 5, 1, '0.0440'),
(17, 5, 2, '0.0796'),
(18, 5, 4, '0.1177'),
(19, 5, 5, '0.0546'),
(20, 5, 6, '0.1268'),
(21, 6, 1, '0.0294'),
(22, 6, 2, '0.0597'),
(23, 6, 4, '0.0942'),
(24, 6, 5, '0.0327'),
(25, 6, 6, '0.1014'),
(26, 7, 1, '0.0734'),
(27, 7, 2, '0.0597'),
(28, 7, 4, '0.0706'),
(29, 7, 5, '0.0218'),
(30, 7, 6, '0.0761'),
(31, 8, 1, '0.0440'),
(32, 8, 2, '0.0398'),
(33, 8, 4, '0.0706'),
(34, 8, 5, '0.0109'),
(35, 8, 6, '0.0761'),
(36, 9, 1, '0.0147'),
(37, 9, 2, '0.0398'),
(38, 9, 4, '0.0471'),
(39, 9, 5, '0.0109'),
(40, 9, 6, '0.0761'),
(41, 10, 1, '0.0147'),
(42, 10, 2, '0.0199'),
(43, 10, 4, '0.0235'),
(44, 10, 5, '0.0109'),
(45, 10, 6, '0.0507'),
(46, 11, 1, '0.0294'),
(47, 11, 2, '0.0398'),
(48, 11, 4, '0.0471'),
(49, 11, 5, '0.0109'),
(50, 11, 6, '0.0761');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$....', '2026-06-13 15:46:03'),
(2, 'etmin', '$2y$10$Xn5XL9V.nT1zGx7bukWBbe51Fj.KFKQ01WzzIRk1R4aKhTANwRPXy', '2026-06-13 15:55:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_agregat`
--
ALTER TABLE `tbl_agregat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_agregat_i` (`alternatif_i`),
  ADD KEY `fk_agregat_j` (`alternatif_j`);

--
-- Indexes for table `tbl_alternatif`
--
ALTER TABLE `tbl_alternatif`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_concordance`
--
ALTER TABLE `tbl_concordance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_concordance_i` (`alternatif_i`),
  ADD KEY `fk_concordance_j` (`alternatif_j`);

--
-- Indexes for table `tbl_discordance`
--
ALTER TABLE `tbl_discordance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_discordance_i` (`alternatif_i`),
  ADD KEY `fk_discordance_j` (`alternatif_j`);

--
-- Indexes for table `tbl_dominan_concordance`
--
ALTER TABLE `tbl_dominan_concordance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_domcon_i` (`alternatif_i`),
  ADD KEY `fk_domcon_j` (`alternatif_j`);

--
-- Indexes for table `tbl_dominan_discordance`
--
ALTER TABLE `tbl_dominan_discordance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_domdis_i` (`alternatif_i`),
  ADD KEY `fk_domdis_j` (`alternatif_j`);

--
-- Indexes for table `tbl_hasil`
--
ALTER TABLE `tbl_hasil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alternatif_id` (`alternatif_id`);

--
-- Indexes for table `tbl_kriteria`
--
ALTER TABLE `tbl_kriteria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_nilai`
--
ALTER TABLE `tbl_nilai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nilai_alternatif` (`alternatif_id`),
  ADD KEY `fk_nilai_kriteria` (`kriteria_id`);

--
-- Indexes for table `tbl_normalisasi`
--
ALTER TABLE `tbl_normalisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_normalisasi_alternatif` (`alternatif_id`),
  ADD KEY `fk_normalisasi_kriteria` (`kriteria_id`);

--
-- Indexes for table `tbl_terbobot`
--
ALTER TABLE `tbl_terbobot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_terbobot_alternatif` (`alternatif_id`),
  ADD KEY `fk_terbobot_kriteria` (`kriteria_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_agregat`
--
ALTER TABLE `tbl_agregat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `tbl_alternatif`
--
ALTER TABLE `tbl_alternatif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_concordance`
--
ALTER TABLE `tbl_concordance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `tbl_discordance`
--
ALTER TABLE `tbl_discordance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `tbl_dominan_concordance`
--
ALTER TABLE `tbl_dominan_concordance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `tbl_dominan_discordance`
--
ALTER TABLE `tbl_dominan_discordance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `tbl_hasil`
--
ALTER TABLE `tbl_hasil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_kriteria`
--
ALTER TABLE `tbl_kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_nilai`
--
ALTER TABLE `tbl_nilai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `tbl_normalisasi`
--
ALTER TABLE `tbl_normalisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `tbl_terbobot`
--
ALTER TABLE `tbl_terbobot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_agregat`
--
ALTER TABLE `tbl_agregat`
  ADD CONSTRAINT `fk_agregat_i` FOREIGN KEY (`alternatif_i`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_agregat_j` FOREIGN KEY (`alternatif_j`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_concordance`
--
ALTER TABLE `tbl_concordance`
  ADD CONSTRAINT `fk_concordance_i` FOREIGN KEY (`alternatif_i`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_concordance_j` FOREIGN KEY (`alternatif_j`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_discordance`
--
ALTER TABLE `tbl_discordance`
  ADD CONSTRAINT `fk_discordance_i` FOREIGN KEY (`alternatif_i`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_discordance_j` FOREIGN KEY (`alternatif_j`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_dominan_concordance`
--
ALTER TABLE `tbl_dominan_concordance`
  ADD CONSTRAINT `fk_domcon_i` FOREIGN KEY (`alternatif_i`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_domcon_j` FOREIGN KEY (`alternatif_j`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_dominan_discordance`
--
ALTER TABLE `tbl_dominan_discordance`
  ADD CONSTRAINT `fk_domdis_i` FOREIGN KEY (`alternatif_i`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_domdis_j` FOREIGN KEY (`alternatif_j`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_hasil`
--
ALTER TABLE `tbl_hasil`
  ADD CONSTRAINT `tbl_hasil_ibfk_1` FOREIGN KEY (`alternatif_id`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_nilai`
--
ALTER TABLE `tbl_nilai`
  ADD CONSTRAINT `fk_nilai_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_nilai_kriteria` FOREIGN KEY (`kriteria_id`) REFERENCES `tbl_kriteria` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_nilai_ibfk_1` FOREIGN KEY (`alternatif_id`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_nilai_ibfk_2` FOREIGN KEY (`kriteria_id`) REFERENCES `tbl_kriteria` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_normalisasi`
--
ALTER TABLE `tbl_normalisasi`
  ADD CONSTRAINT `fk_normalisasi_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_normalisasi_kriteria` FOREIGN KEY (`kriteria_id`) REFERENCES `tbl_kriteria` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_terbobot`
--
ALTER TABLE `tbl_terbobot`
  ADD CONSTRAINT `fk_terbobot_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `tbl_alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_terbobot_kriteria` FOREIGN KEY (`kriteria_id`) REFERENCES `tbl_kriteria` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
