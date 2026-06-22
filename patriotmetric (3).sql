-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 18, 2026 at 02:07 PM
-- Server version: 8.0.46-0ubuntu0.22.04.2
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `patriotmetric`
--

--
-- Dumping data for table `agama`
--

INSERT INTO `agama` (`id`, `identitas_id`, `agama`, `jumlah`, `created_at`, `updated_at`) VALUES
(1, 1, 'islam', 4000, NULL, NULL),
(2, 1, 'kristen', 1000, NULL, NULL),
(3, 1, 'katolik', 500, NULL, NULL),
(4, 1, 'hindu', 0, NULL, NULL),
(5, 1, 'buddha', 200, NULL, NULL),
(6, 1, 'konghucu', 300, NULL, NULL);

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `institution_id`, `nama_pic`, `jabatan_pic`, `no_hp_pic`, `tahun_periode`, `status`, `user_id`, `reviewer_id`, `total_skor_sistem`, `total_skor_akhir`, `skor_rekap_json`, `created_at`, `updated_at`) VALUES
(1, 'f7884cfa-4757-4839-a4f3-2dce4508c3b5', 'Burhan', 'Wakil Ketua Pemeringkatan', '0851552560513', 2026, 'SUBMITTED', 4, NULL, '0.00', '0.00', NULL, '2026-06-18 09:48:48', '2026-06-18 10:35:07');

--
-- Dumping data for table `identitas`
--

INSERT INTO `identitas` (`id`, `Assessment_id`, `jml_mahasiswa`, `jml_dosen`, `jml_tendik`, `jml_prodi`, `jml_ukm`, `jml_ormawa`, `jml_fakultas`, `visi`, `misi`, `legal_documents`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 1, 6000, 2000, 200, 25, 25, 40, 15, 'Menjadi Universitas Unggul Berkarakter Bela Negara', '1\r\nMenyelenggarakan dan mengembangkan pendidikan berkarakter bela negara\r\n\r\n2\r\nMeningkatkan budaya riset dalam pengembangan bidang IPTEK yang berdayaguna untuk kesejahteraan masyarakat\r\n\r\n3\r\nMenyelenggarakan pengabdian kepada masyarakat  berbasis riset dan kearifan lokal\r\n\r\n4\r\nMenyelenggarakan tata kelola yang baik dan bersih dalam rangka mencapai akuntabilitas pengelolaan anggaran\r\n\r\n5\r\nMengembangkan kualitas sumber daya manusia unggul dalam sikap dan tata nilai, unjuk kerja, penguasaan pengetahuan, dan manajerial\r\n\r\n6\r\nMeningkatkan sistem pengelolaan sarana dan prasarana terpadu\r\n\r\n7\r\nMeningkatkan kerjasama institusional dengan stakeholders baik dalam dan luar negeri', '{\"logo_url\": \"/storage/verifikasi/universitas-pembangunan-nasional-veteran-jawa-timur-2026/1781751493_logo.webp\", \"profil_pt\": \"/storage/verifikasi/universitas-pembangunan-nasional-veteran-jawa-timur-2026/1781751493_katalog-anya-craft-1-compressed.pdf\", \"sk_pendirian\": \"/storage/verifikasi/universitas-pembangunan-nasional-veteran-jawa-timur-2026/1781751493_katalog-anya-craft-1-compressed.pdf\", \"surat_pernyataan\": \"/storage/verifikasi/universitas-pembangunan-nasional-veteran-jawa-timur-2026/1781751493_katalog-anya-craft-1-compressed.pdf\", \"struktur_organisasi\": \"/storage/verifikasi/universitas-pembangunan-nasional-veteran-jawa-timur-2026/1781751493_katalog-anya-craft-1-compressed.pdf\"}', 0, '2026-06-18 09:58:14', '2026-06-18 09:58:14');

--
-- Dumping data for table `institusis`
--

INSERT INTO `institusis` (`id`, `nama_institusi`, `logo_url`, `jenis_institusi`, `domain_email`, `created_at`, `updated_at`) VALUES
('f7884cfa-4757-4839-a4f3-2dce4508c3b5', 'Universitas Pembangunan Nasional \"Veteran\" Jawa Timur', '/storage/verifikasi/universitas-pembangunan-nasional-veteran-jawa-timur-2026/1781751493_logo.webp', 'PTN', 'upnjatim.ac.id', '2026-06-18 09:48:48', '2026-06-18 09:58:14');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `email_verified_at`, `password`, `role`, `name`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin@admin.com', '2026-06-18 09:47:29', '$2y$12$hQK5cPyAYqhhivZtm7trduwfOOJ74tdtsiozbQgEZfPexX10luxCO', 'ADMIN', NULL, 'ACTIVE', NULL, '2026-06-18 09:47:29', '2026-06-18 09:47:29'),
(2, 'upn@pic.com', NULL, '$2y$12$U.HgInPhe0e.Vf7G7Ze2K.SMp/nIsUlO3v/3KZoM/11cQ3RUauE2W', 'PESERTA', NULL, 'ACTIVE', NULL, '2026-06-18 09:47:29', '2026-06-18 09:47:29'),
(4, 'muhamad_aris.bd@upnjatim.ac.id', NULL, '$2y$12$sJJuKdflH/81CKCaf7hAmu4ZSWeGPGsFYHW0MG/3nTtY/mA9GqyLe', 'PESERTA', NULL, 'ACTIVE', NULL, '2026-06-18 09:48:48', '2026-06-18 09:49:28');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
