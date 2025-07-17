-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 02, 2025 at 05:45 AM
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
-- Database: `freelance`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `flancer_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `application_TS` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `is_new` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `flancer_id`, `job_id`, `application_TS`, `status`, `is_new`) VALUES
(46, 46, 42, '2025-03-29 05:31:04', 'accepted', 0),
(47, 46, 45, '2025-03-29 11:00:43', 'pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `username` varchar(16) NOT NULL,
  `email` varchar(32) NOT NULL,
  `rating` enum('Positive','Neutral','Negative') NOT NULL,
  `feedback_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `username`, `email`, `rating`, `feedback_text`, `created_at`) VALUES
(9, 'shreeya', 'shreeya1611@gmail.com', 'Positive', '5 star', '2025-03-29 01:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `free_user`
--

CREATE TABLE `free_user` (
  `flancer_id` int(11) NOT NULL,
  `flancer_name` varchar(256) NOT NULL,
  `flancer_email` varchar(100) NOT NULL,
  `flancer_phone` bigint(20) NOT NULL,
  `flancer_qualification` varchar(56) NOT NULL,
  `flancer_uni` varchar(128) NOT NULL,
  `flancer_uname` varchar(15) NOT NULL,
  `flancer_pass` varchar(32) NOT NULL,
  `flancer_TS` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `status` enum('active','suspended','deleted') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `free_user`
--

INSERT INTO `free_user` (`flancer_id`, `flancer_name`, `flancer_email`, `flancer_phone`, `flancer_qualification`, `flancer_uni`, `flancer_uname`, `flancer_pass`, `flancer_TS`, `last_login`, `status`) VALUES
(46, 'Shreeya', 'shreeya1611@gmail.com', 9876543210, 'BCA', 'Solapur', 'shreeya', 'Pass@123', '2025-03-29 05:29:00', '2025-03-29 16:30:12', 'active'),
(47, 'admin', 'admin@gmail.com', 0, '', '', 'admin', 'admin', '2025-03-29 10:16:19', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `institute_details`
--

CREATE TABLE `institute_details` (
  `institute_id` int(64) NOT NULL,
  `institute_name` varchar(512) NOT NULL,
  `institute_uname` varchar(16) NOT NULL,
  `institute_phone` bigint(10) NOT NULL,
  `institute_adrs` varchar(999) NOT NULL,
  `institute_email` varchar(128) NOT NULL,
  `institute_pass` varchar(32) NOT NULL,
  `institute_TS` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `account_status` enum('active','suspended','deleted') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institute_details`
--

INSERT INTO `institute_details` (`institute_id`, `institute_name`, `institute_uname`, `institute_phone`, `institute_adrs`, `institute_email`, `institute_pass`, `institute_TS`, `status`, `account_status`) VALUES
(14, 'mim', 'mim', 9876543210, 'solapur', 'mim@gmail.com', 'Pass@123', '2025-03-29 05:17:34', 'verified', 'active'),
(15, 'Sangmeshwar', 'sangclg', 9876543210, 'Saat rasta, Solapur', 'sangmeshwar@gmail.com', 'Pass@123', '2025-03-29 05:58:57', 'verified', 'active'),
(16, 'hncc', 'hncc', 9876543210, 'Solapur', 'hncc@gmail.com', 'Pass@123', '2025-03-29 10:58:08', 'verified', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `institute_responses`
--

CREATE TABLE `institute_responses` (
  `response_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `response_TS` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institute_responses`
--

INSERT INTO `institute_responses` (`response_id`, `application_id`, `message`, `response_TS`, `status`, `is_read`) VALUES
(23, 46, 'We will contact you shortly', '2025-03-29 05:38:10', 'accepted', 1);

-- --------------------------------------------------------

--
-- Table structure for table `job_details`
--

CREATE TABLE `job_details` (
  `job_id` int(11) NOT NULL,
  `occupation_title` varchar(64) NOT NULL,
  `salary` text NOT NULL,
  `experience_required` varchar(16) NOT NULL,
  `job_description` varchar(999) NOT NULL,
  `vacancy_available` varchar(32) NOT NULL,
  `skill_required` varchar(256) NOT NULL,
  `duration` varchar(32) NOT NULL,
  `institute_name` varchar(200) NOT NULL,
  `institute_id` varchar(20) NOT NULL,
  `job_TS` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','deleted','removed') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_details`
--

INSERT INTO `job_details` (`job_id`, `occupation_title`, `salary`, `experience_required`, `job_description`, `vacancy_available`, `skill_required`, `duration`, `institute_name`, `institute_id`, `job_TS`, `status`) VALUES
(42, 'AWS Certification', '2', '2 yr', 'We are seeking an experienced AWS certified instructor to guide our students.', '1', 'AWS EC2, EBS, Lambda', '2', 'mim', '14', '2025-03-29 05:25:27', 'active'),
(43, 'Python', '2', '4 yr', 'A well knowledged tutor', '1', 'Python, Adv python', '4', 'Sangmeshwar', '15', '2025-03-29 06:01:46', 'active'),
(44, 'Alpine JS instructor', '2', 'Not required', 'Strong knowledge of Alpine JS', '2', 'Expertise in Alpine js, javascript, HTML,CSS', '1', 'Sangmeshwar', '15', '2025-03-29 06:10:44', 'active'),
(45, 'Bootstrap Course trainer', '2', '2 years in teach', 'well knowleged in bootstrap', '1', 'Html css, Bootstrap', '6', 'hncc', '16', '2025-03-29 10:59:22', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `flancer_id` int(11) NOT NULL,
  `report_reason` enum('spam','fraud','inappropriate','other') NOT NULL,
  `report_text` text DEFAULT NULL,
  `report_TS` timestamp NOT NULL DEFAULT current_timestamp(),
  `action` enum('pending','resolved') NOT NULL,
  `usr_res` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `job_id`, `flancer_id`, `report_reason`, `report_text`, `report_TS`, `action`, `usr_res`) VALUES
(11, 42, 46, 'fraud', 'Its an urgent matter, please look into it asap', '2025-03-29 05:40:59', 'resolved', 'report has been viewed');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `flancer_id` (`flancer_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `free_user`
--
ALTER TABLE `free_user`
  ADD PRIMARY KEY (`flancer_id`);

--
-- Indexes for table `institute_details`
--
ALTER TABLE `institute_details`
  ADD PRIMARY KEY (`institute_id`);

--
-- Indexes for table `institute_responses`
--
ALTER TABLE `institute_responses`
  ADD PRIMARY KEY (`response_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `job_details`
--
ALTER TABLE `job_details`
  ADD PRIMARY KEY (`job_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `flancer_id` (`flancer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `free_user`
--
ALTER TABLE `free_user`
  MODIFY `flancer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `institute_details`
--
ALTER TABLE `institute_details`
  MODIFY `institute_id` int(64) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `institute_responses`
--
ALTER TABLE `institute_responses`
  MODIFY `response_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `job_details`
--
ALTER TABLE `job_details`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`flancer_id`) REFERENCES `free_user` (`flancer_id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `job_details` (`job_id`);

--
-- Constraints for table `institute_responses`
--
ALTER TABLE `institute_responses`
  ADD CONSTRAINT `institute_responses_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `job_details` (`job_id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`flancer_id`) REFERENCES `free_user` (`flancer_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
