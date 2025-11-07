-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2025 at 01:44 PM
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
-- Database: `genta`
--

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `choices` text NOT NULL,
  `answer` text NOT NULL,
  `score` int(11) NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `subject_id`, `description`, `image`, `choices`, `answer`, `score`, `status`, `created`, `modified`) VALUES
(1, 1, 'I  am an odd number that is less than 80 but  greater than 77, what am I?', NULL, '[\"79\"]', '79', 1, 2, '2024-02-15 13:57:36', '2024-04-11 10:22:02'),
(2, 1, 'What is the sum of all even numbers that are less than 30?', NULL, '[\"240\"]', '240', 1, 1, '2024-02-19 09:23:22', '2024-03-20 15:24:51'),
(3, 1, 'Write the missing letters to complete the sequence? M, N, O, _, Q, R, S?', NULL, '[\"P\"]', 'P', 1, 1, '2024-02-19 09:24:20', '2024-03-20 15:25:07'),
(4, 1, 'Write the missing numbers to complete the sequence? 4, 8, 12, _, 20, 24, 28?', NULL, '[\"16\"]', '16', 1, 2, '2024-02-19 09:26:11', '2024-04-11 10:22:33'),
(5, 1, 'Miss Padla bought  4 over 5  kg of beans, 5 over 3  kg of cabbage and 8 over 9 kg of carrots. Which kind of vegetable did she buy the most?\r\n', NULL, '[\"cabbage\"]', 'cabbage', 1, 2, '2024-02-19 09:26:46', '2024-04-11 10:22:16'),
(6, 1, 'What is the geometric figure that has one endpoint and arrowhead?', NULL, '[\"Ray\"]', 'Ray', 1, 1, '2024-02-19 09:28:02', '2024-03-20 15:26:36'),
(7, 1, 'What is the missing number in 7 multiplied by 8 = _ multiplied by 7?', NULL, '[\"8\"]', '8', 1, 2, '2024-02-19 09:29:19', '2024-04-11 10:22:22'),
(8, 1, 'Ano ang remainder kapag ang 29 ay dinivide sa 3?', NULL, '[\"2\"]', '2', 1, 1, '2024-02-19 09:30:00', '2024-03-20 15:27:01'),
(9, 1, 'Si Rita ay mayroong 3 sets ng pechay seedlings. Kada set ay mayroong 5 pechay seedlings. Ilang pechay seedlings ang mayroon si Rita?', NULL, '[\"15\"]', '15', 1, 1, '2024-02-19 09:31:00', '2024-03-20 15:27:15'),
(10, 1, 'Mister Santos has 4 daughters. Each of his daughters has a brother. How many children does Mr. Santos have?', NULL, '[\"5\"]', '5', 1, 2, '2024-02-19 09:31:35', '2024-04-11 10:22:45');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `student_code` varchar(255) NOT NULL,
  `grade` int(11) NOT NULL,
  `section` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `student_code`, `grade`, `section`, `remarks`, `created`, `modified`) VALUES
(1, 'Alen Guiwan', '22-0536', 3, 'C-306', 'Shows excellent understanding of basic addition and subtraction concepts. But needs reinforcement in division and multiplication.', '2025-02-19 09:33:30', '2025-02-19 09:33:30'),
(5, 'Jonathan Tiglao', '22-0530', 3, 'C-306', 'Needs reinforcement on basic division, multiplication, remainders, and fractions.', '2025-03-14 09:53:44', '2025-03-14 09:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `student_quiz`
--

CREATE TABLE `student_quiz` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_quiz`
--

INSERT INTO `student_quiz` (`id`, `student_id`, `subject_id`, `created`, `modified`) VALUES
(1, 1, 1, '2025-02-19 09:38:28', '2025-02-19 09:38:28'),
(2, 2, 1, '2025-02-19 09:38:34', '2025-02-19 09:38:34'),
(3, 3, 1, '2025-02-19 09:38:37', '2025-02-19 09:38:37'),
(4, 4, 1, '2025-02-19 09:38:41', '2025-02-19 09:38:41'),
(22, 5, 1, '2025-03-14 09:35:25', '2025-03-14 09:35:25');

-- --------------------------------------------------------

--
-- Table structure for table `student_quiz_questions`
--

CREATE TABLE `student_quiz_questions` (
  `id` int(11) NOT NULL,
  `student_quiz_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `choices` text NOT NULL,
  `answer` text NOT NULL,
  `student_answer` text DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_quiz_questions`
--

INSERT INTO `student_quiz_questions` (`id`, `student_quiz_id`, `description`, `image`, `choices`, `answer`, `student_answer`, `score`, `status`, `created`, `modified`) VALUES
(260, 22, 'I  am an odd number that is less than 80 but  greater than 77, what am I?', '', '', '79', '79', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(261, 22, 'What is the sum of all even numbers that are less than 30?', '', '', '240.', '300', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(262, 22, 'Write the missing letters to complete the sequence? M, N, O, _, Q, R, S?', '', '', 'p', 'p', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(263, 22, 'Write the missing numbers to complete the sequence? 4, 8, 12, _, 20, 24, 28?', '', '', '16.', 'labing anim', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(264, 22, 'Miss Padla bought  4 over 5  kg of beans, 5 over 3  kg of cabbage and 8 over 9 kg of carrots. Which kind of vegetable did she buy the most?', '', '', 'cabbage.', 'kabit', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(265, 22, 'What is the geometric figure that has one endpoint and arrowhead?', '', '', 'R', 'weh', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(266, 22, 'What is the missing number in 7 multiplied by 8 = _ multiplied by 7?', '', '', '8', '8', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(267, 22, 'Ano ang remainder kapag ang 29 ay dinivide sa 3?', '', '', '2', '2', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(268, 22, 'Si Rita ay mayroong 3 sets ng pechay seedlings. Kada set ay mayroong 5 pechay seedlings. Ilang pechay seedlings ang mayroon si Rita?', '', '', '15', '15', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17'),
(269, 22, 'Mister Santos has 4 daughters. Each of his daughters has a brother. How many children does Mr. Santos have?', '', '', '5.', 'Seek', 1, 1, '2025-04-12 16:32:17', '2025-04-12 16:32:17');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`, `created`, `modified`) VALUES
(1, 'Math', NULL, '2025-02-19 08:55:44', '2025-02-19 08:55:44'),
(2, 'English', 'English description here', '2025-02-19 08:56:02', '2025-02-19 08:56:02'),
(3, 'Science', NULL, '2025-02-19 08:56:19', '2025-02-19 08:56:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `token` text DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `type` int(11) NOT NULL DEFAULT 1,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `token`, `first_name`, `last_name`, `status`, `type`, `created`, `modified`) VALUES
(12, 'alenguiwan@gmail.com', '$2y$10$E37VPBWiZ6O0/JBuIdbNv.r3GRPCpZmMTKelTMnZqrsCudWVDDQPG', NULL, 'Alen', 'Guiwan', 1, 1, '2025-05-05 15:09:14', '2025-05-05 15:09:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_quiz`
--
ALTER TABLE `student_quiz`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_quiz_questions`
--
ALTER TABLE `student_quiz_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_quiz`
--
ALTER TABLE `student_quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `student_quiz_questions`
--
ALTER TABLE `student_quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=270;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
