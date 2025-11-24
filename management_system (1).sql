-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 08:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_stock`
--

CREATE TABLE `daily_stock` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_stock`
--

INSERT INTO `daily_stock` (`id`, `product_id`, `quantity`, `created_at`) VALUES
(1, 3, 0, '2025-11-19 14:57:06'),
(2, 3, 300, '2025-11-19 15:18:25');

-- --------------------------------------------------------

--
-- Table structure for table `franchisees`
--

CREATE TABLE `franchisees` (
  `id` int(11) NOT NULL,
  `franchisee_name` varchar(200) DEFAULT NULL,
  `area` varchar(200) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `franchisees`
--

INSERT INTO `franchisees` (`id`, `franchisee_name`, `area`, `type`, `created_at`) VALUES
(1, 'terst', 'asdasd', 'franchisee', '2025-11-19 04:36:48'),
(2, 'gino', 'sta.cruz', 'franchisee', '2025-11-19 04:39:54'),
(3, 'mark', 'malibiran', 'franchisee', '2025-11-19 07:38:45'),
(5, 'asdlkjflksdjahjfkf', 'asdfasdf', 'franchisee', '2025-11-19 10:55:53'),
(6, 'beth', 'pampang', 'franchisee', '2025-11-19 11:00:07'),
(7, 'Kuya G', 'Florida Main Branch', 'dealer', '2025-11-23 06:15:06');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `franchise_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending',
  `batch_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `franchise_id`, `product_id`, `quantity`, `created_at`, `status`, `batch_id`) VALUES
(58, 6, 9, 1, '2025-11-23 03:12:17', 'Pending', NULL),
(59, 7, 8, 5, '2025-11-23 03:13:17', 'Pending', NULL),
(60, 7, 4, 3, '2025-11-23 03:13:17', 'Pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(200) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `unit`, `quantity`, `created_at`) VALUES
(2, 'dwy', 'Drums', '', 0, '2025-11-19 04:20:00'),
(3, 'Dwg', 'Drums', '', 0, '2025-11-19 05:04:25'),
(4, 'Perfume De Amor', 'Spray', '', 25, '2025-11-19 05:04:38'),
(7, '950 ml', 'Bottles', '', 0, '2025-11-19 11:25:37'),
(8, 'fabcol', '500ml spray', '', 0, '2025-11-23 05:24:01'),
(9, '950 ml', 'Carbouy', '', 0, '2025-11-23 05:24:57');

-- --------------------------------------------------------

--
-- Table structure for table `pullout`
--

CREATE TABLE `pullout` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `franchisee_name` varchar(255) DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL,
  `product_description` text DEFAULT NULL,
  `pullout_date` date DEFAULT NULL,
  `status` enum('Given','Not Given') DEFAULT 'Not Given'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pullout`
--

INSERT INTO `pullout` (`id`, `product_id`, `quantity`, `reason`, `created_by`, `created_at`, `franchisee_name`, `area`, `product_description`, `pullout_date`, `status`) VALUES
(2, 0, 0, 'jelly', 1, '2025-11-19 19:42:19', 'gino', 'sta.cruz', 'dwgs', '2025-11-08', 'Given');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `created_at`) VALUES
(1, 'Rollie Vitug', 'jhairo', '$2y$10$5Az5nz92dS8zHR5NOQRWm.5F89i3YHZK//JqO3IRQVDSJOdUbAv4W', '2025-11-19 04:11:55'),
(2, 'Rollie Vitug', 'test1', '$2y$10$sLYWc7kbHmnxB6ToREPJren49hsMVXRG59JXZTEo/jahf1sfFjAuG', '2025-11-19 14:47:48'),
(3, 'dary ondez', 'dary', '$2y$10$18jfBOwZFHhDC0qNS0xrmuX3u1UPid6M7fHe4g7sg6j2MjpqCnmJG', '2025-11-19 14:53:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_stock`
--
ALTER TABLE `daily_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `franchisees`
--
ALTER TABLE `franchisees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pullout`
--
ALTER TABLE `pullout`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daily_stock`
--
ALTER TABLE `daily_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `franchisees`
--
ALTER TABLE `franchisees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pullout`
--
ALTER TABLE `pullout`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
