-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 08:07 AM
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
-- Database: `news_clipping`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `deleted`) VALUES
(2, 'Water', 1),
(3, 'Health', 0),
(11, 'Sewage', 0),
(13, 'fo', 1),
(14, 'Forest', 0),
(15, 'Finance', 0),
(16, 'Soil', 0),
(17, 'Construction', 0);

-- --------------------------------------------------------

--
-- Table structure for table `newspapers`
--

CREATE TABLE `newspapers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newspapers`
--

INSERT INTO `newspapers` (`id`, `name`, `deleted`) VALUES
(1, 'Times of India', 0),
(2, 'Indian Express', 0),
(3, 'Lokmat', 0),
(4, 'Pudhari', 0),
(5, 'Sakal', 0),
(6, 'Hindustan Times', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `deleted`) VALUES
(1, 'Education', 0),
(2, 'Sewerage', 1),
(3, 'Building Permit', 0),
(4, 'Nashik', 0),
(5, 'Sewage', 0);

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_images`
--

CREATE TABLE `uploaded_images` (
  `id` int(11) NOT NULL,
  `department` varchar(255) NOT NULL,
  `newspaper` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `category` enum('Positive','Negative') NOT NULL,
  `tags` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT 'no_image.jpg',
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uploaded_images`
--

INSERT INTO `uploaded_images` (`id`, `department`, `newspaper`, `date`, `category`, `tags`, `image`, `uploaded_at`, `image_path`) VALUES
(9, '11', '3', '2025-04-02', 'Positive', '4,2', 'no_image.jpg', '2025-05-20 04:52:08', 'uploads/1748072821_img13.jpg'),
(10, '14', '2', '2025-05-02', 'Negative', '4', 'no_image.jpg', '2025-05-20 04:53:35', 'uploads/1748072749_img14.jpg'),
(11, '2', '2', '2025-02-05', 'Positive', '3,1,4', 'no_image.jpg', '2025-05-20 04:54:27', 'uploads/1748072794_img12.jpg'),
(12, '3', '2', '2025-05-17', 'Positive', '2,3', 'no_image.jpg', '2025-05-24 07:05:15', 'uploads/68316fab7f6253.66140551.jpg'),
(13, '11', '2', '2025-05-24', 'Positive', '1,2', 'no_image.jpg', '2025-05-24 07:05:55', 'uploads/68316fd3265546.99890349.jpg'),
(14, '3', '2', '2025-05-22', 'Positive', '1,3,4', 'no_image.jpg', '2025-05-24 07:06:21', 'uploads/68316fed574589.08895660.jpg'),
(15, '11', '2', '2025-05-17', 'Negative', '2', 'no_image.jpg', '2025-05-24 07:06:40', 'uploads/68317000843a58.65227909.jpg'),
(16, '2', '1', '2025-05-16', 'Positive', '1,2,3', 'no_image.jpg', '2025-05-24 07:08:22', 'uploads/683170669cf876.76350736.jpg'),
(17, '11', '1', '2025-05-17', 'Positive', '2,4', 'no_image.jpg', '2025-05-24 07:08:49', 'uploads/683170816fd0c6.51893634.jpg'),
(18, '2', '2', '2025-05-24', 'Positive', '1,2', 'no_image.jpg', '2025-05-24 07:32:08', 'uploads/683175f826f6d0.54775601.jpg'),
(19, '2', '1', '2025-05-15', 'Positive', '1,3', 'no_image.jpg', '2025-05-24 07:32:42', 'uploads/6831761a6ffcf5.76181252.jpg'),
(20, '11', '5', '2025-05-02', 'Negative', '1,2', 'no_image.jpg', '2025-05-24 07:41:03', 'uploads/6831780f50f3e3.23831895.jpg'),
(21, '3', '1', '2025-05-17', 'Positive', '1,2', 'no_image.jpg', '2025-05-25 12:03:25', 'uploads/6833070d03a1a0.45006766.jpg'),
(22, '16', '5', '2025-05-25', 'Negative', '2,3,4', 'no_image.jpg', '2025-05-25 12:15:49', 'uploads/683309f56a0f94.88897637.jpg'),
(23, '3', '4', '2025-05-23', 'Negative', '4,2', 'no_image.jpg', '2025-05-25 12:16:37', 'uploads/68330a25d9b401.25549101.jpg'),
(24, '11', '6', '2025-05-22', 'Positive', '1,4', 'no_image.jpg', '2025-05-25 12:39:07', 'uploads/68330f6bce99d5.15425297.jpg'),
(25, '11', '2', '2025-05-21', 'Negative', '2,4', 'no_image.jpg', '2025-05-26 10:15:45', 'uploads/68343f515e4297.00130228.jpg'),
(26, '2', '2', '2025-05-29', 'Positive', '1,2', 'no_image.jpg', '2025-05-29 06:37:15', 'uploads/6838009bad8546.47524099.jpg'),
(27, '15', '5', '2025-05-29', 'Positive', '1,2', 'no_image.jpg', '2025-05-29 06:37:35', 'uploads/683800af4e6304.89205931.jpg'),
(28, '11', '1', '2025-05-31', 'Positive', '3,1,4', 'no_image.jpg', '2025-05-31 16:46:46', 'uploads/683b327665bbb8.38682825.jpg'),
(29, '3', '5', '2025-06-04', 'Negative', '1,4', 'no_image.jpg', '2025-06-04 07:11:30', 'uploads/683ff1a2e5b824.71284379.jpg'),
(30, '15', '6', '2025-06-04', 'Positive', '2,4', 'no_image.jpg', '2025-06-04 07:12:12', 'uploads/683ff1cc30ca65.31464032.jpg'),
(32, '3', '4', '2025-06-04', 'Positive', '1', 'no_image.jpg', '2025-06-04 07:16:02', 'uploads/683ff2b2903456.19883427.jpg'),
(33, '11', '5', '2025-06-05', 'Positive', '1,4', 'no_image.jpg', '2025-06-05 03:30:55', 'uploads/68410f6fe85419.22269681.jpg'),
(34, '14', '6', '2025-06-05', 'Negative', '2,4', 'no_image.jpg', '2025-06-05 03:31:26', 'uploads/68410f8ebd0249.50136921.jpg'),
(35, '17', '4', '2025-06-05', 'Negative', '1,3,4', 'no_image.jpg', '2025-06-05 03:31:53', 'uploads/68410fa9750520.23836913.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `department` varchar(50) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `gender`, `department`, `mobile`, `email`, `password`, `created_at`) VALUES
(1, 'Sakshi Shirole', 'Female', '14', '9999999999', 'sakshibshirole@gmail.com', '$2y$10$1yUzHASXWRd8U3.PSu9Bl.NJ6G1i9qDcwDS2W6r5WXKgxQsG9VlB6', '2025-05-24 06:57:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `newspapers`
--
ALTER TABLE `newspapers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `uploaded_images`
--
ALTER TABLE `uploaded_images`
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
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `newspapers`
--
ALTER TABLE `newspapers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `uploaded_images`
--
ALTER TABLE `uploaded_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
