-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 15 سبتمبر 2026 الساعة 15:08
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dlgc_assistance`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(60) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','employee') NOT NULL DEFAULT 'employee',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admins`
--

INSERT INTO `admins` (`id`, `username`, `display_name`, `password_hash`, `role`, `is_active`, `created_at`) VALUES
(1, 'faroukkendil', 'farouk', '$2y$10$Iu8HIBs7fnsSJAS5prciROgW0jVtUr6WSadShr5cWBDxLorakh.TS', 'super_admin', 1, '2026-09-14 16:36:54'),
(2, 'aminedz86', 'AMINE', '$2y$10$kSFoSx0D1H2bSaGGUbUZFeiFhgIv7pODIlVejbxtT7CZ3bbgHwmcS', 'employee', 1, '2026-09-15 09:08:28');

-- --------------------------------------------------------

--
-- بنية الجدول `assistance_requests`
--

CREATE TABLE `assistance_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_code` varchar(30) DEFAULT NULL,
  `client_code` varchar(50) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `status` enum('nouveau','en_cours','resolu') NOT NULL DEFAULT 'nouveau',
  `assigned_admin_id` int(10) UNSIGNED DEFAULT NULL,
  `resolved_by_admin_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `started_at` datetime DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `assistance_requests`
--

INSERT INTO `assistance_requests` (`id`, `ticket_code`, `client_code`, `company_name`, `phone`, `subject`, `description`, `status`, `assigned_admin_id`, `resolved_by_admin_id`, `created_at`, `started_at`, `resolved_at`) VALUES
(1, 'AST-2026-000001', 'CLT-TEST', 'Test Company', '0555000000', 'Test Issue', 'This is a test description for assistance.', 'resolu', 1, 1, '2026-09-14 15:57:04', '2026-09-14 17:57:00', '2026-09-14 17:57:03'),
(2, 'AST-2026-000002', 'CLT-TEST', 'Test Company', '0555000000', 'Test Issue', 'This is a test description for assistance.', 'resolu', 1, 1, '2026-09-14 15:58:48', '2026-09-14 17:38:27', '2026-09-14 17:38:31'),
(3, 'AST-2026-000003', 'CLT-TEST2', 'Test Company 2', '0666000000', 'Second Issue', 'Second description test', 'resolu', 1, 1, '2026-09-14 15:58:48', '2026-09-14 17:38:44', '2026-09-14 17:38:48'),
(4, 'AST-2026-000004', NULL, 'dlgc', '0556996553', 'snap plus', 'dkjhaskjhkajfdkladkfj', 'resolu', 1, 1, '2026-09-14 16:34:50', '2026-09-14 17:37:42', '2026-09-14 17:37:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admins_username` (`username`);

--
-- Indexes for table `assistance_requests`
--
ALTER TABLE `assistance_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assistance_ticket_code` (`ticket_code`),
  ADD KEY `idx_assistance_status_created` (`status`,`created_at`),
  ADD KEY `idx_assistance_client_code` (`client_code`),
  ADD KEY `idx_assistance_company_name` (`company_name`),
  ADD KEY `fk_assistance_assigned_admin` (`assigned_admin_id`),
  ADD KEY `fk_assistance_resolved_admin` (`resolved_by_admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assistance_requests`
--
ALTER TABLE `assistance_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `assistance_requests`
--
ALTER TABLE `assistance_requests`
  ADD CONSTRAINT `fk_assistance_assigned_admin` FOREIGN KEY (`assigned_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assistance_resolved_admin` FOREIGN KEY (`resolved_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
