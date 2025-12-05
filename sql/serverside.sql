-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2025 at 11:31 AM
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
-- Database: `serverside`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(4, 'Benches and Racks'),
(1, 'Cardio Equipment'),
(3, 'Free Weights'),
(5, 'Functional Training'),
(2, 'Strength Machines');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `equipment_id`, `comment_text`, `created_at`, `user_name`) VALUES
(3, 2, 'This rowing machine feels sturdy. Great quality.', '2025-11-13 04:29:44', 'Ryan T.'),
(4, 4, 'Would be nice to see more equipment details.', '2025-11-13 04:29:44', 'Liam P.'),
(5, 1, 'Appreciate how the equipment descriptions are detailed.', '2025-11-13 04:29:44', 'Emily K.'),
(6, 7, 'Is there a section for cardio gear?', '2025-11-13 04:29:44', 'Chris A.'),
(7, 6, 'Some pages take a while to load — maybe cache images?', '2025-11-13 04:29:44', 'Maya R.'),
(8, 8, 'Nice layout! The CMS looks clean and easy to navigate.', '2025-11-13 04:29:44', 'Noah J.'),
(9, 3, 'Hope to see a review section added soon.', '2025-11-13 04:29:44', 'Ava S.'),
(10, 2, 'Love that admins can respond directly to comments!', '2025-11-13 04:29:44', 'Ethan C.');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(10) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,6) NOT NULL,
  `comment_text` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `category_id`, `name`, `description`, `price`, `comment_text`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'Treadmill', 'Commercial-grade with 15% incline, 12mph max speed, heart rate monitor, and 10" touchscreen', 1900.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(2, 1, 'Rowing Machine', 'Magnetic resistance with 10 levels, LCD monitor, foldable design for storage', 500.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(3, 1, 'Exercise Bikes', 'Upright stationary bike, 24 resistance levels, pulse sensors, backlit display', 380.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(4, 4, 'Power Rack', 'Heavy-duty steel frame, multi-grip pull-up bar, safety spotters, weight storage pegs', 70.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(5, 2, 'Leg Press Machine', '45-degree sled design, 1000lb capacity, oversized backrest and foot platform', 1299.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(6, 3, 'Olympic Barbell', '7ft Olympic bar, 1500lb weight capacity, chrome finish with knurled grip', 250.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(7, 3, 'Weight Plates set', 'Rubber-coated Olympic plates, 255lb total (2x45lb, 2x35lb, 2x25lb, 2x10lb, 4x5lb, 2x2.5lb)', 430.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(8, 4, 'Flat Bench', 'Heavy-duty steel frame, 600lb weight capacity, high-density foam padding', 130.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(9, 5, 'Plyo Box', '3-in-1 wooden box (20", 24", 30" heights), non-slip surface, 400lb capacity', 150.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(10, 5, 'Resistance Bands', 'Set of 5 bands with different resistance levels, door anchor, handles, ankle straps', 30.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(11, 5, 'Battle Ropes', '1.5" diameter, 40ft length, polyester blend with heat-shrink handles', 90.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02'),
(12, 5, 'TRX Suspension Trainer', 'Professional-grade straps, multiple anchor options, workout guide included', 190.000000, '', NULL, '2025-11-13 04:21:02', '2025-11-13 04:21:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`) VALUES
(3, 'chibbs', 'chibbs@gmail.com', '$2y$10$t1kzaNenB.LnL5z6XpIGUusu/92XmA3X5zkmKT2hUsnjdhQKFyBkS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `equipment_id` (`equipment_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD KEY `fk_equipment_category` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `fk_equipment_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
