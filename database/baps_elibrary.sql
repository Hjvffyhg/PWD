-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2026 at 09:28 AM
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
-- Database: `baps_elibrary`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(120) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `category` varchar(60) NOT NULL,
  `abstract` text DEFAULT NULL,
  `pages` int(11) DEFAULT 0,
  `total_copies` int(11) NOT NULL DEFAULT 1,
  `available_copies` int(11) NOT NULL DEFAULT 1,
  `cover` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `isbn`, `category`, `abstract`, `pages`, `total_copies`, `available_copies`, `cover`, `created_at`) VALUES
(2, 'The Secret of the Forgotten Forest', 'Kervin Tuiza', '978-1-23-456789-7', 'Fiction', 'A young boy discovers a hidden forest near his village. As he explores it, he finds strange creatures and uncovers a secret that could change his life.', 180, 5, 5, 'img_6ae73b20c03cb9.28288646.jpg', '2026-10-30 22:00:14'),
(3, 'The Kingdom Above the Clouds', 'The Kingdom Above the Clouds', '978-1-23-456790-3', 'Fiction', 'A curious traveler finds a kingdom floating above the clouds and begins an adventure to help its people save their home.', 220, 3, 3, 'img_6ae73b57d664d7.23212780.jpg', '2026-10-30 22:01:49'),
(4, 'My Journey to Self-Improvement', 'Rich Clarence Legaspi', '978-1-23-456791-0', 'Non-Fiction', 'This book shares simple ways to improve daily habits, build confidence, and become a better version of yourself through small steps.', 150, 4, 4, 'img_6ae73b741d6553.03043276.jpg', '2026-10-30 22:02:56'),
(5, 'Lessons I Learned in Life', 'Frederick Buenaventura', '978-1-23-456792-7', 'Non-Fiction', 'This book discusses everyday experiences, personal mistakes, and important lessons that can help readers make wiser decisions.', 165, 2, 2, 'img_6ae73b95db2118.79232198.jpg', '2026-10-30 22:04:03'),
(6, 'Discovering the World Around Us', 'John Ray Delgado', '978-1-23-456793-4', 'Science', 'This book introduces readers to nature, the environment, and scientific discoveries that explain how our world works.', 200, 3, 3, 'img_6ae73baf817d50.78273529.jpg', '2026-10-30 22:05:11'),
(7, 'The Mystery of the Human Brain', 'Eddie Geronimo', '978-1-23-456794-1', 'Science', 'This book explores how the brain helps people think, remember, learn, and respond to the world around them.', 190, 4, 4, 'img_6ae73bd2a574d0.28041806.jpg', '2026-10-30 22:06:07'),
(8, 'The Future of Artificial Intelligence', 'Mark Andre Piano', '978-1-23-456795-8', 'Technology', 'This book explains how artificial intelligence works and how it may affect education, communication, businesses, and everyday life.', 230, 5, 5, 'img_6ae73bf52345d2.42244117.jpg', '2026-10-30 22:08:07'),
(9, 'A Beginner\'s Guide to Computer Systems', 'John Peter Onte', '978-1-23-456796-5', 'Technology', 'This book introduces computer hardware, software, operating systems, and basic troubleshooting for readers who want to understand computers.', 250, 3, 3, 'img_6ae73c0bf407f5.52922383.jpg', '2026-10-30 22:08:53'),
(10, 'Making Mathematics Easy', 'Christian Miranda', '978-1-23-456797-2', 'Mathematics', 'This book provides simple explanations and examples to help students understand basic mathematical concepts and solve common problems.', 175, 2, 2, 'img_6ae73c27ddc655.38744939.jpg', '2026-10-30 22:09:57'),
(11, 'Numbers in Everyday Life', 'Paul Esguerra', '978-1-23-456798-9', 'Mathematics', 'This book shows how mathematics is used when budgeting money, measuring objects, managing time, and making everyday decisions.', 160, 4, 4, 'img_6ae73c46ea7d77.52723634.jpg', '2026-10-30 22:10:45'),
(12, 'The Rise of a Forgotten Civilization', 'Joshua Celes', '978-1-23-456799-6', 'History', 'This imagined historical story follows a researcher uncovering the traditions, achievements, and decline of an ancient civilization.', 280, 3, 3, 'img_6ae73c5f78a8f7.74166285.jpg', '2026-10-30 22:11:56'),
(13, 'Until We Meet Again', 'Gunther Doria', '978-1-23-456800-9', 'Romance', 'Two childhood friends promise to meet again after years apart. When life brings them back together, they realize that their feelings have changed and that some connections never truly disappear.', 195, 3, 3, 'img_6ae73c795afae4.02863846.jpg', '2026-10-30 22:12:55'),
(14, 'Starting Small, Dreaming Big', 'Gian Carlos Obligacion', '978-1-23-456801-6', 'Business', 'This book introduces the basics of starting a small business, planning a budget, attracting customers, and managing daily expenses.', 190, 5, 5, 'img_6ae73c9f8479b1.60261668.jpg', '2026-10-30 22:13:53'),
(15, 'The Clock That Stopped Time', 'Adrian Manzanero', '978-1-23-456802-3', 'Fiction', 'A student finds an old clock that can stop time for a few minutes. At first, he uses it to solve everyday problems, but he soon discovers that changing time comes with unexpected consequences.', 210, 4, 4, 'img_6ae595f9c07a86.85518200.jpg', '2026-10-30 22:31:23'),
(16, 'The Road to Nowhere', 'Anna Marie Abarientos', '978-1-23-456803-0', 'Fiction', 'A young traveler follows a mysterious road that does not appear on any map. Along the journey, he meets unusual people and learns the truth about his own past.', 195, 3, 3, 'img_6ae595cc702647.54664818.jpg', '2026-10-30 22:32:08'),
(17, 'The Power of Daily Discipline', 'Bon Ostine Bernabe', '978-1-23-456804-7', 'Non-Fiction', 'This book explains how discipline and consistency can help people achieve their goals. It provides simple ideas for managing time, avoiding distractions, and building productive routines.', 175, 5, 5, 'img_6ae5956c2023c1.41431744.jpg', '2026-10-30 22:33:19'),
(18, 'Finding Your Own Direction', 'DJ Sarsaba', '978-1-23-456805-4', 'Non-Fiction', 'This book encourages readers to understand their interests, make thoughtful choices, and set personal goals instead of always comparing themselves with others.', 160, 2, 2, 'img_6ae594ca62a4c7.57196560.jpg', '2026-10-30 22:34:23'),
(19, 'Beyond Our Solar System', 'Eire Camero', '978-1-23-456806-1', 'Science', 'Explore distant stars, planets, and galaxies while learning how scientists study space and search for clues about the universe.', 240, 3, 3, 'img_6ae594755be344.16920763.jpg', '2026-10-30 22:35:25'),
(20, 'Life Beneath the Surface', 'Ezy Cruz', '978-1-23-456807-8', 'Science', 'This book explores ecosystems and the hidden relationships between living organisms, showing how water, soil, and other natural resources support life.', 205, 4, 4, 'img_6ae5937dd1a3e0.31189144.jpg', '2026-10-30 22:38:10'),
(21, 'Connected in a Digital World', 'Francine Balaque', '978-1-23-456808-5', 'Technology', 'This book introduces computer networks, internet connections, and data sharing. It explains how devices communicate and why network security is important.', 225, 4, 4, 'img_6ae593026caec9.31975418.jpg', '2026-10-30 22:38:52'),
(22, 'From Code to Creation', 'Jerusha Nina Labide', '978-1-23-456809-2', 'Technology', 'Follow the process of turning an idea into a simple computer program. This book introduces logical thinking, debugging, and basic software development concepts.', 260, 3, 3, 'img_6ae59236d106b1.65502129.jpg', '2026-10-30 22:39:45'),
(23, 'Patterns Behind the Numbers', 'John Kenneth Logarta', '978-1-23-456810-8', 'Mathematics', 'Discover how patterns appear in numbers, shapes, and everyday situations. The book introduces sequences and basic mathematical reasoning through simple examples.', 185, 2, 2, 'img_6ae591c8858d39.21065471.jpg', '2026-10-30 22:40:32'),
(24, 'The Art of Logical Thinking', 'Ryza Mae Gozo', '978-1-23-456811-5', 'Mathematics', 'This book teaches readers to use logic, equations, and step-by-step reasoning to understand mathematical problems and find practical solutions.', 200, 5, 5, 'img_6ae5912cab95b3.80352721.jpg', '2026-10-30 22:41:20'),
(25, 'Voyages Into the Unknown', 'Rayan Sultan Mama', '978-1-23-456812-2', 'History', 'This book explores an imagined collection of historical voyages, focusing on the challenges faced by explorers as they traveled across unfamiliar seas and discovered new lands.', 270, 3, 3, 'img_6ae5909ae380d7.93332863.jpg', '2026-10-30 22:42:20'),
(26, 'A Village Through the Ages', 'Raj Steven Patinio', '978-1-23-456813-9', 'History', 'Follow a fictional village as it changes across generations. The story explores how traditions, inventions, leadership, and community decisions shape people\'s lives.', 235, 4, 4, 'img_6ae5904e383142.73115362.jpg', '2026-10-30 22:43:18'),
(27, 'A Thousand Little Moments', 'Sairah Capilitan', '978-1-23-456814-6', 'Romance', 'Two classmates begin spending time together while working on a school project. Through small acts of kindness and honest conversations, their friendship slowly develops into something deeper.', 215, 4, 4, 'img_6ae58ff1414da2.14110928.jpg', '2026-10-30 22:44:11');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `fav_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `trans_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `status` enum('pending','borrowed','return_requested','returned','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` datetime DEFAULT current_timestamp(),
  `borrowed_at` datetime DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `days_late` int(11) NOT NULL DEFAULT 0,
  `penalty_amount` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `student_no` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `course_year` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','librarian') NOT NULL DEFAULT 'student',
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `student_no`, `email`, `course_year`, `username`, `password`, `role`, `profile_pic`, `created_at`) VALUES
(1, 'Prof. Nico Barlizo', NULL, 'nicofbarlizo@cccc.edu.ph', NULL, 'barlizonico', '$2y$10$sJ7yVjgsaNVShEmAyrVOk.jGH1SymCRnuDt7CRAYTrMN8h3N4CpOi', 'librarian', 'assets/images/librarians/nico.jpg', '2026-11-01 03:56:19'),
(2, 'Prof. Mark Justine Almario', NULL, 'mjalmario@cccc.edu.ph', NULL, 'almariojustine2', '$2y$10$d7lWBktlwh14CS8i45ZkpOS4iKr63sZd70VbdDadxaJdbrGFwlKPy', 'librarian', 'assets/images/librarians/mark.jpg', '2026-11-01 03:56:19'),
(3, 'Prof. Byron Pajac', NULL, 'bypajac@cccc.edu.ph', NULL, 'pajacbyron', '$2y$10$wqgCHKkyh4WyOflLbNI7ou0.F7dVAfSewsimrUgMwm50vAYR6fcMG', 'librarian', 'assets/images/librarians/byron.jpg', '2026-11-01 03:56:19'),
(4, 'Prof. Juan Miguel Saligumba', NULL, 'jmsaligumbao@cccc.edu.ph', NULL, 'saligumbamiguel', '$2y$10$sxiLkAhIIxQ7tJiUG9wkPOo5YR6VAmKKe1P9Thp1m5xng6NlOqj6e', 'librarian', 'assets/images/librarians/miguel.jpg', '2026-11-01 03:56:20'),
(5, 'Maria Clara Santos', '2026-00001', 'maria.santos@example.com', 'BSIT - 3rd Year', 'mariasantos', '$2y$10$9fsTDi2hXxtCNzBz00p9Ke3nYsOkfmjx/g.bencVDMGrigm39JEXq', 'student', NULL, '2026-11-01 05:23:41'),
(6, 'Alexander Christopher Montgomery Jr.', '2026-00002', 'alexander.montgomery@example.com', 'BS Computer Science - 4th Year', 'alexmontgomery', '$2y$10$STm21gGtRr.ZBIij98r8xOsHEYeCpmMQfqA7Bvc9ggkT1IKdW2Y7q', 'student', NULL, '2026-11-01 05:23:42'),
(8, 'Justine Macuroy', '2025-20865', 'jmacuroy@ccc.edu.ph', 'BSIT - 2nd', 'justino', '$2y$10$qBsM6D10x8LkeCE1DzYGMevZ.TzWidnTA9wbDaseK8uXlcFFNvcEC', 'student', NULL, '2026-11-02 08:09:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`fav_id`),
  ADD UNIQUE KEY `one_fav` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `one_rating` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`trans_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `student_no` (`student_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `fav_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `trans_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;