-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2025 at 02:59 AM
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
(2, 'Strength Machines'),
(9, 'Testing');

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
(10, 2, 'Love that admins can respond directly to comments!', '2025-11-13 04:29:44', 'Ethan C.'),
(11, 1, 'Belt runs smooth even at 12 mph. Incline transitions are quick—no lag.', '2025-11-11 04:35:21', 'Jamal R.'),
(12, 1, 'Heart-rate monitor is pretty accurate compared to my chest strap. UI is simple.', '2025-11-12 04:35:21', 'Kara D.'),
(13, 2, 'Magnetic resistance feels consistent across all 10 levels. Quiet enough for apartments.', '2025-11-10 04:35:21', 'Andre P.'),
(14, 2, 'Fold-up hinge is sturdy—locks well after sessions. Foot straps could be longer.', '2025-11-11 04:35:21', 'Sonia L.'),
(15, 3, 'Seat is comfy for 30–40 min rides. The 24 resistance steps are small but noticeable.', '2025-11-09 04:35:21', 'Gabe N.'),
(16, 3, 'Backlit display is readable in low light. Would love Bluetooth stats export next.', '2025-11-12 16:35:21', 'Priya S.'),
(17, 4, 'Hole spacing is even, safeties feel solid under load. Multi-grip bar is a nice touch.', '2025-11-08 04:35:21', 'Leo V.'),
(18, 4, 'Plate storage pegs keep the area clean. Minor cosmetic scuff out of the box.', '2025-11-12 04:35:21', 'Marina C.'),
(19, 5, 'Sled glide is smooth; no sticking at heavier loads. Oversized footplate helps stance changes.', '2025-11-10 04:35:21', 'Owen T.'),
(20, 5, 'Backrest supports well—no hip pinch at depth. Add assembly torque specs to the guide?', '2025-11-13 04:35:21', 'Dylan W.'),
(21, 6, 'Knurling is medium-aggressive—great for presses without tearing skin.', '2025-11-07 04:35:21', 'Rita M.'),
(22, 6, 'Sleeves spin freely and there’s a bit of whip for Oly lifts. Finish cleans easily.', '2025-11-11 04:35:21', 'Caleb J.'),
(23, 7, 'Rubber coating has minimal odor and low bounce on drop. Collars seat tight.', '2025-11-09 04:35:21', 'Nina F.'),
(24, 7, 'Weights are within ~1% of stated mass—good accuracy for the price.', '2025-11-12 20:35:21', 'Victor K.'),
(25, 8, 'Bench height works well for leg drive. Padding is firm, no wobble under 300+ lb.', '2025-11-08 04:35:21', 'Haley G.'),
(26, 8, 'Vinyl grips well when sweaty. Feet have a wide stance—very stable.', '2025-11-12 04:35:21', 'Marcus B.'),
(27, 9, 'Edges are sanded smooth; no shin killers. Non-slip surface feels secure.', '2025-11-06 04:35:21', 'Jess E.'),
(28, 9, 'Love the 20/24/30 options. Heavier than foam but way more stable on hard floors.', '2025-11-11 04:35:21', 'Arman Y.'),
(29, 10, 'Door anchor is sturdy and doesn’t fray the bands. Good progression across the set.', '2025-11-10 04:35:21', 'Tasha Q.'),
(30, 10, 'Handles feel solid—no flex at higher tension. Please add a quick-start routine PDF!', '2025-11-13 04:35:21', 'Elliot H.'),
(31, 11, '40 ft length is perfect for our studio. Sleeves protect well—no fray after 2 weeks.', '2025-11-07 04:35:21', 'Bianca R.'),
(32, 11, 'Weight feels right for HIIT intervals. Easy to mount with a basic wall anchor.', '2025-11-12 04:35:21', 'Grant P.'),
(33, 12, 'Straps adjust fast and hold position during sets. Great for rows and single-leg work.', '2025-11-11 04:35:21', 'Maya D.'),
(34, 12, 'Multiple anchor options made setup simple in the garage. Buckles feel premium.', '2025-11-12 18:35:21', 'Ivan S.'),
(36, 11, 'Thus is just another test so testing...', '2025-11-19 07:59:12', 'Chibuike');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `reviewer_name` varchar(80) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `review_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `reviewer_name`, `rating`, `review_text`, `created_at`) VALUES
(1, 'Jordan P.', 5, 'Clean layout and easy to browse. Checkout was quick.', '2025-11-13 04:40:00'),
(2, 'Sasha L.', 4, 'Nice selection of equipment. Could use more photos.', '2025-11-12 10:15:00'),
(3, 'Guest', 5, 'Fast load times and clear categories.', '2025-11-10 08:05:00');

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
(1, 1, 'Treadmill', '<p>Commercial-grade with 15% incline, 12mph max speed, heart rate monitor, and 10\" touchscreen</p>', 1900.000000, '', 'uploads/equipment_1764771093_6bdced23.png', '2025-11-13 04:21:02', '2025-12-03 08:11:33'),
(2, 1, 'Rowing Machine', '<p>Magnetic resistance with 10 levels, LCD monitor, foldable design for storage</p>', 500.000000, '', 'uploads/equipment_1764763561_88324f0f.png', '2025-11-13 04:21:02', '2025-12-03 06:06:01'),
(3, 1, 'Exercise Bikes', '<p>Upright stationary bike, 24 resistance levels, pulse sensors, backlit display</p>', 380.000000, '', 'uploads/equipment_1764761985_a457cd43.png', '2025-11-13 04:21:02', '2025-12-03 05:39:45'),
(4, 4, 'Power Rack', '<p>Heavy-duty steel frame, multi-grip pull-up bar, safety spotters, weight storage pegs</p>', 70.000000, '', 'uploads/equipment_1764762882_0d99008c.png', '2025-11-13 04:21:02', '2025-12-03 05:54:42'),
(5, 2, 'Leg Press Machine', '<p>Leg machines, particularly leg extensions and leg curls, are staples for lower body workouts. They isolate the quads and hamstrings, respectively, allowing for focused muscle development. However, it\'s essential to use these machines correctly.. 45-degree sled design, 1000lb capacity, oversized backrest and foot platform.</p>', 1299.000000, '', 'uploads/equipment_1764762595_17d221f4.png', '2025-11-13 04:21:02', '2025-12-03 05:49:55'),
(6, 3, 'Olympic Barbell', '<p>7ft Olympic bar, 1500lb weight capacity, chrome finish with knurled grip</p>', 250.000000, '', 'uploads/equipment_1764762674_b80fe54d.png', '2025-11-13 04:21:02', '2025-12-03 05:51:14'),
(7, 3, 'Weight Plates set', '<p>Rubber-coated Olympic plates, 255lb total (2x45lb, 2x35lb, 2x25lb, 2x10lb, 4x5lb, 2x2.5lb)</p>', 430.000000, '', 'uploads/equipment_1764771116_9345b547.png', '2025-11-13 04:21:02', '2025-12-03 08:11:57'),
(8, 4, 'Flat Bench', '<p>Heavy-duty steel frame, 600lb weight capacity, high-density foam padding</p>', 130.000000, '', 'uploads/equipment_1764762152_7c6770d8.png', '2025-11-13 04:21:02', '2025-12-03 05:42:32'),
(9, 5, 'Plyo Box', '<p>3-in-1 wooden box (20\", 24\", 30\" heights), non-slip surface, 400lb capacity</p>', 150.000000, '', 'uploads/equipment_1764762795_f9097181.png', '2025-11-13 04:21:02', '2025-12-03 05:53:15'),
(10, 5, 'Resistance Bands', '<p>Set of 5 bands with different resistance levels, door anchor, handles, ankle straps</p>', 30.000000, '', 'uploads/equipment_1764763347_41315e07.png', '2025-11-13 04:21:02', '2025-12-03 06:02:27'),
(11, 5, 'Battle Ropes', '<p>1.5\" diameter, 40ft length, polyester blend with heat-shrink handles</p>', 50.000000, '', 'uploads/equipment_1764761848_5656f962.png', '2025-11-13 04:21:02', '2025-12-03 05:37:28'),
(12, 5, 'TRX Suspension Trainer', '<p>Professional-grade straps, multiple anchor options, workout guide included</p>', 190.000000, '', 'uploads/equipment_1764771106_6442d245.png', '2025-11-13 04:21:02', '2025-12-03 08:11:46'),
(18, 2, 'Lat Pulldown', '<p>Lat pull-down machines are commonly used for targeting the lats. While they can be effective, their performance depends heavily on the machine\'s design. Many users experience frustration with the resistance profile, which can lead to less effective workouts.</p>', 250.000000, '', 'uploads/equipment_1764762408_a1dc1901.png', '2025-11-26 08:28:55', '2025-12-03 05:46:48'),
(21, 9, 'Mile 4 test', '<p>Increadibly usefiul equipment.</p>', 324.000000, '', 'uploads/equipment_1764775677_beb27069.png', '2025-12-03 05:32:33', '2025-12-03 09:27:57');

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
(3, 'chibbs', 'chibbs@gmail.com', '$2y$10$t1kzaNenB.LnL5z6XpIGUusu/92XmA3X5zkmKT2hUsnjdhQKFyBkS'),
(4, 'testing', 'testing@yahoo.com', '$2y$10$OnVFR/RI6k/77oCp2iUC1e8W7npZZzNSlnaRyspJD/Kx8HOx4c0k2'),
(5, 'bob', 'bob@email.com', '$2y$10$VGdXbD63G/73EOvuxvPdMurSbMN5bXtXSapjVelubuQhmy1W3.wre');

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
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
