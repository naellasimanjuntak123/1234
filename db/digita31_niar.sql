-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 04:17 PM
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
-- Database: `digita31_niar`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(5, 4, 3, 2, '2026-06-04 14:07:47'),
(8, 7, 3, 1, '2026-06-05 06:47:23');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `order_id`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 1, 'Pesanan baru #1 telah dibuat. Total: Rp 35.000', 1, '2026-05-30 06:53:29'),
(2, 3, 1, 'Status pesanan #1 diubah menjadi PENDING', 1, '2026-05-30 06:55:21'),
(3, 3, 1, 'Status pesanan #1 diubah menjadi CANCELLED', 1, '2026-05-30 06:55:34'),
(4, 3, 1, 'Status pesanan #1 diubah menjadi CANCELLED', 1, '2026-05-30 06:56:04'),
(5, 3, 1, 'Status pesanan #1 diubah menjadi PROCESSING', 1, '2026-05-30 06:56:10'),
(6, 3, 1, 'Status pesanan #1 diubah menjadi COMPLETED', 1, '2026-05-30 06:56:37'),
(7, 4, 2, 'Pesanan baru #2 telah dibuat. Total: Rp 180.000', 1, '2026-06-04 11:36:25'),
(8, 4, 3, 'Pesanan baru #3 telah dibuat. Total: Rp 40.000', 1, '2026-06-04 11:38:06'),
(9, 4, 4, 'Pesanan baru #4 telah dibuat. Total: Rp 35.000', 1, '2026-06-04 12:10:12'),
(10, 4, 4, 'Bukti pembayaran pesanan #4 telah dikirim dan sedang diverifikasi.', 1, '2026-06-04 12:11:29'),
(11, 5, 5, 'Pesanan baru #5 telah dibuat. Total: Rp 45.000', 0, '2026-06-05 03:44:11'),
(12, 7, 6, 'Pesanan baru #6 telah dibuat. Total: Rp 30.000', 0, '2026-06-05 04:02:20'),
(14, 7, 7, 'Pesanan baru #7 telah dibuat. Total: Rp 47.000', 0, '2026-06-05 08:12:47'),
(15, 7, 6, 'Status pesanan #6 diubah menjadi Dibayar', 0, '2026-06-05 08:49:53'),
(16, 7, 6, 'Status pesanan #6 diubah menjadi Diproses', 0, '2026-06-05 08:58:40'),
(17, 7, 6, 'Status pesanan #6 diubah menjadi Dikirim', 0, '2026-06-05 09:02:41'),
(18, 7, 7, 'Status pesanan #7 diubah menjadi Selesai', 0, '2026-06-05 09:05:42'),
(35, 4, 4, 'Status pesanan #4 diubah menjadi Dibayar', 0, '2026-06-05 13:28:50');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `payment_method` enum('bank_transfer','cod','e_wallet') DEFAULT 'bank_transfer',
  `shipping_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `shipping_service` varchar(20) DEFAULT 'gojek',
  `shipping_cost` int(11) DEFAULT 15000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_method`, `shipping_address`, `notes`, `created_at`, `updated_at`, `shipping_service`, `shipping_cost`) VALUES
(1, 3, 35000.00, 'completed', 'cod', 'hjdeyd', 'dhuyiue', '2026-05-30 06:53:29', '2026-05-30 06:56:37', 'gojek', 15000),
(2, 4, 180000.00, 'pending', 'bank_transfer', 'KP. Kampung Durian', 'Dikemas', '2026-06-04 11:36:25', '2026-06-04 11:36:25', 'gojek', 15000),
(3, 4, 40000.00, 'pending', 'cod', 'yyy', 'dibungkus', '2026-06-04 11:38:06', '2026-06-04 11:38:06', 'gojek', 15000),
(4, 4, 35000.00, 'paid', 'e_wallet', 'mangga 256', '', '2026-06-04 12:10:12', '2026-06-04 12:11:29', 'gojek', 15000),
(5, 5, 45000.00, 'pending', 'bank_transfer', 'Srengseng Sawah Rt12 Rw06', '', '2026-06-05 03:44:11', '2026-06-05 03:44:11', 'gojek', 15000),
(6, 7, 30000.00, 'shipped', 'e_wallet', 'jambu2', '', '2026-06-05 04:02:20', '2026-06-05 09:02:41', 'pickup', 0),
(7, 7, 47000.00, 'completed', 'cod', 'TB Simatupang', '-', '2026-06-05 08:12:47', '2026-06-05 09:05:42', 'jnt', 12000),
(8, 9, 57000.00, 'completed', 'cod', 'jambu 2', '-', '2026-06-05 09:07:39', '2026-06-05 09:17:32', 'jnt', 12000),
(9, 9, 95000.00, 'completed', 'e_wallet', 'jambu', '-', '2026-06-05 09:18:52', '2026-06-05 09:50:26', 'grab', 15000),
(10, 9, 105000.00, 'completed', 'e_wallet', 'bambu 3', 'bungkus', '2026-06-05 09:56:24', '2026-06-05 09:58:13', 'grab', 15000);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 35000.00),
(2, 2, 3, 6, 30000.00),
(3, 3, 4, 1, 40000.00),
(4, 4, 1, 1, 35000.00),
(5, 5, 3, 1, 30000.00),
(6, 6, 3, 1, 30000.00),
(7, 7, 1, 1, 35000.00),
(8, 8, 2, 1, 45000.00),
(9, 9, 4, 2, 40000.00),
(10, 10, 2, 2, 45000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `category`, `created_at`) VALUES
(1, 'Siomay Ikan', 'Siomay ikan berkualitas tinggi dengan daging pilihan', 35000.00, 97, 'assets/images/siomayikan.jpeg', 'Siomay', '2026-05-28 11:44:34'),
(2, 'Taekwan', 'Taekwan kuah dengan rasa lezat dan nikmat', 45000.00, 77, 'assets/images/taekwan.jpeg', 'Taekwan', '2026-05-28 11:44:34'),
(3, 'Tahu Bakso Daging', 'Bakso ikan teri segar dan gurih', 30000.00, 112, 'assets/images/tahubaksodaging.jpeg', 'Tahu Bakso', '2026-05-28 11:44:34'),
(4, 'Bakso Daging', 'Bakso daging dengan daging dan urat yang berasa', 40000.00, 87, 'assets/images/baksodaging.jpeg', 'Bakso', '2026-05-28 11:44:34');

-- --------------------------------------------------------

--
-- Table structure for table `promos`
--

CREATE TABLE `promos` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `order_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 3, 1, 5, 'Enak Sekali', '2026-05-30 06:58:15'),
(2, 2, 9, 8, 4, '', '2026-06-05 10:11:23'),
(3, 4, 9, 9, 4, 'Bagus', '2026-06-05 13:36:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `created_at`) VALUES
(1, 'Admin', 'admin@frozenfood.com', '$2a$12$uo554XOLBw9dNoxQSZTbP.ByDCpQkhSjP3IdLtOpKa0DH5lsfVzjG', 'admin', NULL, NULL, '2026-05-28 11:44:34'),
(2, 'kontolaceng', 'kontolaceng@gmail.com', '$2y$10$n7Xd2lunadqIRztBV0feq.pu5oNpTBnkOsLA2TinHwFs1yG.RtZK2', 'customer', NULL, NULL, '2026-05-28 11:55:00'),
(3, 'Nadia', 'nadiajenong05@gmail.com', '$2y$10$4K8qjMQ8WmChpyS759TPD.VXhFVLJ3D0hDJmZmefkyxblRVsZWsry', 'customer', NULL, NULL, '2026-05-30 02:06:36'),
(4, 'riski', 'admiin@frozenfood.com', '$2y$10$auJcyNfxqGITPt6ttMxkEegy2O7WZm4IMLeC5tww.vB6dSSW7UDJi', 'customer', NULL, NULL, '2026-06-04 11:35:06'),
(5, 'rehan', 'admiiin@frozenfood.com', '$2y$10$LUCuq4zvkI42a8vmbGLtnO1aUt3t2ra5Ik4rd21zh2qfP/NPhEDnS', 'customer', '08957666377', 'Srengseng Sawah Rt12 Rw06', '2026-06-05 03:13:43'),
(6, 'haikal', 'haik@gmail.com', '$2y$10$XlqWzakJPR3ohX3Wbi0k0e797XvxRx6HLOxXYPXwYZ1uk40ImMyGy', 'customer', '0897646', 'swadaya', '2026-06-05 03:58:22'),
(7, 'rafki', 'rafki1@gmail.com', '$2y$10$mJo4zr251cfaTQDUNLgwp.6Tr2Va.hzJNsSq8PtxXOU51LlTr3vAW', 'customer', '087597886', 'jambu2', '2026-06-05 04:00:09'),
(8, 'gilang', 'lang@gmail.com', '$2y$10$7GDDGGvukCeHU9OMVn5KJuGKZ5H4gw07DrHSPEbXxZ2w.t6.t3fE2', 'customer', '', '', '2026-06-05 04:31:28'),
(9, 'alvata', 'alvata@gmail.com', '$2y$10$kZPy5anjur3BA8LeroS4UOzajMoa4vQ76XUzil5XqN6ujhTPUMMHC', 'customer', '', '', '2026-06-05 09:06:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
