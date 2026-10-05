-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2026 at 04:56 PM
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
-- Database: `security_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_profile`
--

CREATE TABLE `admin_profile` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `role` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `bio` text DEFAULT NULL,
  `member_since` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blacklisted_sites`
--

CREATE TABLE `blacklisted_sites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `domain` varchar(255) NOT NULL,
  `reason` varchar(100) DEFAULT 'Phishing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blacklisted_sites`
--

INSERT INTO `blacklisted_sites` (`id`, `user_id`, `domain`, `reason`, `created_at`) VALUES
(36, 3, 'ilovepdf.com', 'برمجيات خبيثة (Malware)', '2026-06-14 15:13:44'),
(40, 8, 'facebook-lite.ar.uptodown.com', 'سبام وإعلانات مزعجة', '2026-06-29 18:04:14'),
(44, 9, 'ilovepdf.com', 'سبام وإعلانات مزعجة', '2026-07-02 14:24:57'),
(45, 9, 'skywork.ai', 'تتبع وتجسس', '2026-07-02 14:30:03'),
(46, 9, 'web.facebook.com', 'برمجيات خبيثة (Malware)', '2026-07-02 14:30:55'),
(47, 10, 'facebook-lite.ar.uptodown.com', 'تتبع وتجسس', '2026-07-02 14:38:03'),
(48, 10, 'ar.uptodown.com', 'برمجيات خبيثة (Malware)', '2026-07-02 14:39:45');

-- --------------------------------------------------------

--
-- Table structure for table `scanned_files`
--

CREATE TABLE `scanned_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `threat_type` varchar(100) DEFAULT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `file_url` text DEFAULT NULL,
  `status` enum('safe','virus') DEFAULT 'safe',
  `virus_type` varchar(100) DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scanned_files`
--

INSERT INTO `scanned_files` (`id`, `user_id`, `file_name`, `threat_type`, `file_size`, `file_url`, `status`, `virus_type`, `scanned_at`, `created_at`) VALUES
(937, 8, 'facebook-lite-513-0-0-0-56 (6).apk', NULL, '3.21 MB', 'https://dw.uptodown.com/dwn/Ab30LyZXrd4dQDZQFGQNlrGuTYiHfnN7dpuKO_uVYnOaPPnMjL6znMkbLuHnsNtfqYpRvvEzjcbWjKgcvBmBHrGbWMN2zspwUXjRHurUmJFh64jTIed-WdZ_bLdluXjI/Rk7AWhpz8PrdUngGRgZR4yfqG7PEpgeJFckPiaUxzG5KqxqXP9T7No9sZDkP1v4pNVm3oaI_vNdi4oso7De71UnZ1rrpS4ZogWF3cVRIGITp1nuJayJR84R-lco99xOR/7NkUFDMQ3l7lhzLVjAAziPRSxO3KiVwWdtKI27Fljje4qXEsb1wl17ekEuDBRuATHkZdPn9SKjkEOw96Hb7og7Fg62rZbX5M4bwENckLiG4=/', 'safe', 'ملف آمن', '2026-06-29 18:01:54', '2026-06-29 18:01:54'),
(938, 6, 'facebook-lite-519-0-0-2-105.apk', NULL, '3.26 MB', 'https://dw.uptodown.com/dwn/3SLGLYWgccOn4P4TGHd2b-a7MFSt0rTqahhM7vvRS5OT_71NtPvf5bxIgN3uBih90Gu9b_66_6jVoF3_eV47eEmLDxBHKh69yumDfnsJRPGSKSFJK7sIt8Z7r3Oy4PXc/1U2QNVmc9_RubOCQIE3n48ZBHxDDU7B577Blz7h__fgp-yvMwoKq1vE6qmO7kjOY1yH0eK0uU6y94obeYenh1O8H5KqVmpKRxVYuH-cZTaWieN-8UidUkH7uIg7L1t5V/muexWtSY0Ua7QZojiuNIXhgsaIuuddHg9FpFXGkiPYcyW9lH6ABEed6MvtG2O1xxvgJCVFvbkCvfBaqVHdXijcwm6LU52wxB3SRXilDXZ-E=/', 'safe', 'ملف آمن', '2026-07-02 13:45:54', '2026-07-02 13:45:54'),
(939, 9, 'facebook-lite-519-0-0-2-105 (1).apk', NULL, '3.26 MB', 'https://dw.uptodown.com/dwn/3SLGLYWgccOn4P4TGHd2b-a7MFSt0rTqahhM7vvRS5OT_71NtPvf5bxIgN3uBih90Gu9b_66_6jVoF3_eV47eEmLDxBHKh69yumDfnsJRPGSKSFJK7sIt8Z7r3Oy4PXc/1U2QNVmc9_RubOCQIE3n48ZBHxDDU7B577Blz7h__fgp-yvMwoKq1vE6qmO7kjOY1yH0eK0uU6y94obeYenh1O8H5KqVmpKRxVYuH-cZTaWieN-8UidUkH7uIg7L1t5V/muexWtSY0Ua7QZojiuNIXhgsaIuuddHg9FpFXGkiPYcyW9lH6ABEed6MvtG2O1xxvgJCVFvbkCvfBaqVHdXijcwm6LU52wxB3SRXilDXZ-E=/', 'safe', 'ملف آمن', '2026-07-02 14:25:36', '2026-07-02 14:25:36');

-- --------------------------------------------------------

--
-- Table structure for table `scanned_sites`
--

CREATE TABLE `scanned_sites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `site_url` varchar(255) NOT NULL,
  `check_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scanned_sites`
--

INSERT INTO `scanned_sites` (`id`, `user_id`, `site_url`, `check_date`) VALUES
(690, NULL, 'localhost', '2026-07-02 14:03:17'),
(691, NULL, 'localhost', '2026-07-02 14:03:37'),
(692, NULL, 'localhost', '2026-07-02 14:03:49'),
(693, NULL, 'localhost', '2026-07-02 14:03:49'),
(694, NULL, 'ilovepdf.com', '2026-07-02 14:04:08'),
(695, NULL, 'localhost', '2026-07-02 14:04:16'),
(696, NULL, 'localhost', '2026-07-02 14:04:18'),
(697, NULL, 'localhost', '2026-07-02 14:04:23'),
(698, 6, 'ilovepdf.com', '2026-07-02 14:18:34'),
(699, NULL, 'localhost', '2026-07-02 14:18:34'),
(700, NULL, 'localhost', '2026-07-02 14:18:34'),
(701, NULL, 'ilovepdf.com', '2026-07-02 14:18:54'),
(702, NULL, 'localhost', '2026-07-02 14:19:07'),
(703, NULL, 'localhost', '2026-07-02 14:19:07'),
(704, NULL, 'localhost', '2026-07-02 14:19:12'),
(705, NULL, 'ilovepdf.com', '2026-07-02 14:19:25'),
(706, NULL, 'ilovepdf.com', '2026-07-02 14:19:26'),
(707, NULL, 'skywork.ai', '2026-07-02 14:21:18'),
(708, NULL, 'localhost', '2026-07-02 14:22:20'),
(709, NULL, 'localhost', '2026-07-02 14:22:22'),
(710, NULL, 'localhost', '2026-07-02 14:22:41'),
(711, NULL, 'localhost', '2026-07-02 14:24:15'),
(712, NULL, 'localhost', '2026-07-02 14:24:16'),
(713, NULL, 'localhost', '2026-07-02 14:24:29'),
(714, 9, 'ilovepdf.com', '2026-07-02 14:24:57'),
(715, NULL, 'localhost', '2026-07-02 14:24:57'),
(716, NULL, 'localhost', '2026-07-02 14:24:57'),
(717, NULL, 'ilovepdf.com', '2026-07-02 14:25:01'),
(718, NULL, 'ilovepdf.com', '2026-07-02 14:25:03'),
(719, NULL, 'ilovepdf.com', '2026-07-02 14:25:05'),
(720, NULL, 'localhost', '2026-07-02 14:25:22'),
(721, NULL, 'dw.uptodown.com', '2026-07-02 14:25:31'),
(722, NULL, 'localhost', '2026-07-02 14:25:44'),
(723, NULL, 'localhost', '2026-07-02 14:25:51'),
(724, NULL, 'localhost', '2026-07-02 14:26:03'),
(725, NULL, 'localhost', '2026-07-02 14:26:22'),
(726, NULL, 'localhost', '2026-07-02 14:26:45'),
(727, NULL, 'localhost', '2026-07-02 14:26:49'),
(728, NULL, 'localhost', '2026-07-02 14:26:57'),
(729, NULL, 'localhost', '2026-07-02 14:27:06'),
(730, NULL, 'asasasasaaa.net', '2026-07-02 14:27:52'),
(731, NULL, 'asasasasaaa.net', '2026-07-02 14:28:04'),
(732, NULL, 'asasasasaaa.net', '2026-07-02 14:28:05'),
(733, NULL, 'asasasasaaa.net', '2026-07-02 14:29:13'),
(734, NULL, 'asasasasaaa.net', '2026-07-02 14:29:15'),
(735, NULL, 'localhost', '2026-07-02 14:29:27'),
(736, NULL, 'localhost', '2026-07-02 14:30:03'),
(737, 9, 'skywork.ai', '2026-07-02 14:30:03'),
(738, NULL, 'localhost', '2026-07-02 14:30:03'),
(739, NULL, 'skywork.ai', '2026-07-02 14:30:19'),
(740, NULL, 'localhost', '2026-07-02 14:30:55'),
(741, 9, 'web.facebook.com', '2026-07-02 14:30:55'),
(742, NULL, 'localhost', '2026-07-02 14:30:55'),
(743, NULL, 'web.facebook.com', '2026-07-02 14:31:06'),
(744, NULL, 'localhost', '2026-07-02 14:31:17'),
(745, NULL, 'localhost', '2026-07-02 14:31:57'),
(746, NULL, 'localhost', '2026-07-02 14:34:15'),
(747, NULL, 'localhost', '2026-07-02 14:34:17'),
(748, NULL, 'localhost', '2026-07-02 14:34:35'),
(749, NULL, 'asaaaaaaa.com', '2026-07-02 14:35:24'),
(750, NULL, 'ilovepdf.com', '2026-07-02 14:35:50'),
(751, NULL, 'localhost', '2026-07-02 14:38:03'),
(752, 10, 'facebook-lite.ar.uptodown.com', '2026-07-02 14:38:03'),
(753, NULL, 'localhost', '2026-07-02 14:38:03'),
(754, NULL, 'facebook-lite.ar.uptodown.com', '2026-07-02 14:38:09'),
(755, NULL, 'google.com', '2026-07-02 14:38:27'),
(756, NULL, 'ar.uptodown.com', '2026-07-02 14:38:33'),
(757, NULL, 'facebook-lite.ar.uptodown.com', '2026-07-02 14:39:09'),
(758, NULL, 'localhost', '2026-07-02 14:39:45'),
(759, 10, 'ar.uptodown.com', '2026-07-02 14:39:45'),
(760, NULL, 'localhost', '2026-07-02 14:39:45'),
(761, NULL, 'ar.uptodown.com', '2026-07-02 14:39:50'),
(762, NULL, 'google.com', '2026-07-02 14:40:03'),
(763, NULL, 'ar.uptodown.com', '2026-07-02 14:40:05'),
(764, NULL, 'localhost', '2026-07-02 14:55:43'),
(765, NULL, 'localhost', '2026-07-02 14:56:29'),
(766, NULL, 'localhost', '2026-07-02 14:56:31'),
(767, NULL, 'localhost', '2026-07-02 14:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'Super Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `password`, `phone`, `job_title`, `location`, `role`, `created_at`) VALUES
(2, 'عوض', 'عوضكم', 'aowd@gmail.com', '$2y$10$ohjHL1OU36L0h3RyFHcA..r9M/CkKeHI5ErWUtT0.f0TaZGik1V6W', '01000000000', 'it', 'بحري', 'Super Admin', '2026-06-14 13:00:10'),
(6, 'انس', 'انس احمد', 'anas@gmail.com', '$2y$10$Ij6f3PImjmyJmwYSDzrK2ebT4beIssc5M/hROn0qcf9xyft4a5JBy', '0904690171', 'it', 'امبدة', 'Super Admin', '2026-06-14 16:40:28'),
(7, 'انس', 'انس احمد صلاح', 'anas20@gmail.com', '$2y$10$tSLYbRq1snOlsrsQixoftu3uGDsvw2AX3m9Nit3hvY2ZmFomFscTu', '999509162', 'it', 'بحري', 'Super Admin', '2026-06-29 17:47:54'),
(9, 'فاطمه', 'فاطمه ابراهيم', 'fa@gmsil.com', '$2y$10$cFn8pTtD7e3.HgWySe47ueUpNBf4pKn/9aqsDEPRGiTLVjOzSSIWq', '111111111', 'it', 'امبدة', 'Super Admin', '2026-07-02 14:24:14'),
(10, 'naila', 'naila mohmmed', 'naila@gmail.com', '$2y$10$ZuGJK4FSyuoJ8tu4d05KIO6RjJtk.4uefoQBgjlG3tUryoHV/Lt.G', '1234567890', 'it', 'بحري', 'Super Admin', '2026-07-02 14:34:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blacklisted_sites`
--
ALTER TABLE `blacklisted_sites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scanned_files`
--
ALTER TABLE `scanned_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scanned_sites`
--
ALTER TABLE `scanned_sites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_profile`
--
ALTER TABLE `admin_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blacklisted_sites`
--
ALTER TABLE `blacklisted_sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `scanned_files`
--
ALTER TABLE `scanned_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=940;

--
-- AUTO_INCREMENT for table `scanned_sites`
--
ALTER TABLE `scanned_sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=768;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
