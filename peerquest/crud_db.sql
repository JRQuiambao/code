-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2025 at 10:27 AM
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
-- Database: `crud_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievement_tbl`
--

CREATE TABLE `achievement_tbl` (
  `achievement_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `badge_name` varchar(50) DEFAULT NULL,
  `badge_earned_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answers_esy_tbl`
--

CREATE TABLE `answers_esy_tbl` (
  `essay_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` varchar(500) DEFAULT NULL,
  `grade` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `Attempt` smallint(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answers_mcq_collab_tbl`
--

CREATE TABLE `answers_mcq_collab_tbl` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` varchar(10) NOT NULL,
  `submitted_by` int(11) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grades` int(11) NOT NULL,
  `attempt` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answers_mcq_tbl`
--

CREATE TABLE `answers_mcq_tbl` (
  `answer_id` int(11) NOT NULL,
  `assessment_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `selected_option` enum('A','B','C','D') DEFAULT NULL,
  `Attempt` smallint(9) NOT NULL DEFAULT 0,
  `correct_answer` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `answers_tf_tbl`
--

CREATE TABLE `answers_tf_tbl` (
  `true_false_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` enum('True','False') NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `correct_answer` tinyint(1) NOT NULL,
  `Attempt` int(11) DEFAULT 0,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_tbl`
--

CREATE TABLE `assessment_tbl` (
  `assessment_id` int(11) NOT NULL,
  `type` varchar(400) NOT NULL,
  `status` enum('Saved','Published') DEFAULT 'Saved',
  `time_limit` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assessment_mode` varchar(50) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `mode` enum('Individual','Collaborative') DEFAULT 'Individual',
  `total_points` int(11) DEFAULT 0,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_tbl`
--

CREATE TABLE `class_tbl` (
  `class_id` int(11) NOT NULL,
  `class_section` varchar(50) DEFAULT NULL,
  `class_subject` varchar(100) DEFAULT NULL,
  `class_code` varchar(20) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_tbl`
--

INSERT INTO `class_tbl` (`class_id`, `class_section`, `class_subject`, `class_code`, `teacher_id`, `student_id`) VALUES
(2, 'WD-401', 'CAPSTONE1', 'BD9706', 2, NULL),
(3, 'CYB-201', 'ETHICAL-HACKING', '1DE8C2', 2, 1),
(4, 'WD-401', 'CLOUDCOMP', '11A735', 2, NULL),
(5, 'WD-302', 'INFOASEC', 'DBCB24', 2, NULL),
(8, 'section121', 'subject121', '35BFD2', 3, NULL),
(9, 'Section202', 'Subject202', '9C6180', 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard_tbl`
--

CREATE TABLE `leaderboard_tbl` (
  `leaderboard_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `leaderboard_ranking` int(11) DEFAULT NULL,
  `leaderboard_points` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules_tbl`
--

CREATE TABLE `modules_tbl` (
  `module_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) NOT NULL DEFAULT 'Saved'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules_tbl`
--

INSERT INTO `modules_tbl` (`module_id`, `class_id`, `teacher_id`, `title`, `content`, `file_name`, `file_path`, `created_at`, `status`) VALUES
(16, 8, 3, 'Title ', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nWhy do we use it?\r\nIt is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).', NULL, NULL, '2025-01-15 11:53:12', 'Published');

-- --------------------------------------------------------

--
-- Table structure for table `questions_esy_tbl`
--

CREATE TABLE `questions_esy_tbl` (
  `question_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `question_number` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `guided_answer` text DEFAULT NULL,
  `correct_answer` varchar(5) NOT NULL DEFAULT 'True'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions_mcq_collab_tbl`
--

CREATE TABLE `questions_mcq_collab_tbl` (
  `question_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions_mcq_tbl`
--

CREATE TABLE `questions_mcq_tbl` (
  `question_id` int(11) NOT NULL,
  `assessment_id` int(11) DEFAULT NULL,
  `question_text` varchar(500) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `correct_option` enum('A','B','C','D') DEFAULT NULL,
  `points` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions_reci_tbl`
--

CREATE TABLE `questions_reci_tbl` (
  `question_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `revealed_student_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions_tf_tbl`
--

CREATE TABLE `questions_tf_tbl` (
  `question_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `question_number` int(11) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `guided_answer` text DEFAULT NULL,
  `correct_answer` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_ready_tbl`
--

CREATE TABLE `room_ready_tbl` (
  `collab_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `is_ready` tinyint(1) DEFAULT 0,
  `is_host` tinyint(1) DEFAULT 0,
  `status` enum('waiting','started') DEFAULT 'waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_ready_tbl`
--

INSERT INTO `room_ready_tbl` (`collab_id`, `room_id`, `student_id`, `assessment_id`, `is_ready`, `is_host`, `status`) VALUES
(1, 2476, 7, 292, 1, 1, 'started'),
(2, 2476, 8, 292, 1, 0, 'started'),
(3, 4458, 7, 292, 0, 1, 'waiting'),
(4, 9727, 8, 292, 0, 1, 'waiting'),
(5, 6384, 7, 290, 0, 1, 'waiting'),
(6, 6233, 7, 290, 1, 1, 'started'),
(7, 2300, 8, 292, 0, 1, 'waiting'),
(8, 6233, 4, 290, 1, 0, 'started');

-- --------------------------------------------------------

--
-- Table structure for table `student_classes`
--

CREATE TABLE `student_classes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `achievements` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_classes`
--

INSERT INTO `student_classes` (`id`, `student_id`, `class_id`, `status`, `achievements`) VALUES
(1, 4, 5, NULL, NULL),
(3, 4, 3, NULL, NULL),
(4, 1, 5, NULL, NULL),
(5, 4, 2, NULL, NULL),
(6, 7, 8, NULL, NULL),
(7, 4, 8, NULL, NULL),
(8, 8, 8, NULL, NULL),
(9, 7, 9, NULL, NULL),
(10, 8, 9, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_tbl`
--

CREATE TABLE `student_tbl` (
  `student_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `student_email` varchar(100) DEFAULT NULL,
  `student_password` varchar(255) DEFAULT NULL,
  `student_first_name` varchar(100) NOT NULL,
  `student_last_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_tbl`
--

INSERT INTO `student_tbl` (`student_id`, `username`, `student_email`, `student_password`, `student_first_name`, `student_last_name`) VALUES
(1, 'cg18', 'cg18@gmail.com', '$2y$10$1YZZE5SAx7DjuIXnL1Zy2ufObrhMCc/fkOXx7IPX3SDk4whjNWfEK', 'Crisha', 'Hernandez'),
(2, 'Princes123', 'cgm1@gmail.com', '$2y$10$vywHgfkgjZ17aqwQWcLkP.CKL3HAfdzyCqFvzYLPxQ4UW5B.NFJQW', 'Princess', 'Liu'),
(4, 'rose1', 'rose1@gmail.com', '$2y$10$64vLb2.JwyNvp0vtDW9bK.S74MTtC0QW9S8lcME6YNWnWWwuFqwdK', 'Rose', 'Tiu'),
(6, 'Rose23', 'rose23@gmail.com', '$2y$10$XKsIR1wHzwwHkO.0zey81ONHozfLNGaJ/QE0MtXa0W34vaGShiuiW', 'Rosalyn', 'Kira'),
(7, 'student1', 's1@gmail.com', '$2y$10$f9Ds7a79L/l1vzE0T0jD5.oXtuliBUbJSjlB6jjPaTjwZKhX.4MFe', 'Student1', 'Student1'),
(8, 'student', 'wdadwafawfWQd@gmail.com', '$2y$10$fEKVJGfM7i5CbZnJe0ipMuvAYZw/2DkgU5KFYGWhB/eU4UCNtut0W', 'student', 'student'),
(9, 'cccc', 'ccc@gmail.com', '$2y$10$CHnOEZ8sMjTl6Idm5OcrZ.sFZiFLN8E4paEvP7M9JdyaXEC6kM5kS', 'cccc', 'cccc');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_tbl`
--

CREATE TABLE `teacher_tbl` (
  `teacher_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `teacher_email` varchar(100) DEFAULT NULL,
  `teacher_password` varchar(255) DEFAULT NULL,
  `teacher_first_name` varchar(100) NOT NULL,
  `teacher_last_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_tbl`
--

INSERT INTO `teacher_tbl` (`teacher_id`, `username`, `teacher_email`, `teacher_password`, `teacher_first_name`, `teacher_last_name`) VALUES
(1, 'Ms. Max', 'max11@gmail.com', '$2y$10$apvidvIovp0Of8coFfAr3.i2uFNcT3Omkd48RPL2QGtrUHL8.ytA6', 'Maxine', 'Lopez'),
(2, 'Ms.Joanne', 'joanneg13@gmail.com', '$2y$10$dU5quQFjjt9k4gFwbVibwesgzss/.rCPfNIF2JVNgKlm.lfXQg8Sq', 'Joanne', 'Galang'),
(3, 'teacher1', 't1@gmail.com', '$2y$10$yaaBsbYAkLklkcHVWzAB6eKhoIAQrE3NVTuF6oibMR0Aq.JhzOkQ.', 'Teacher1', 'Teacher1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievement_tbl`
--
ALTER TABLE `achievement_tbl`
  ADD PRIMARY KEY (`achievement_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `answers_esy_tbl`
--
ALTER TABLE `answers_esy_tbl`
  ADD PRIMARY KEY (`essay_id`),
  ADD KEY `fk_questions_esy_to_answers_esy` (`question_id`),
  ADD KEY `fk_assessment_to_answers_esy` (`assessment_id`);

--
-- Indexes for table `answers_mcq_collab_tbl`
--
ALTER TABLE `answers_mcq_collab_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_assessment_id` (`assessment_id`),
  ADD KEY `fk_submitted_by` (`submitted_by`);

--
-- Indexes for table `answers_mcq_tbl`
--
ALTER TABLE `answers_mcq_tbl`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `answers_tf_tbl`
--
ALTER TABLE `answers_tf_tbl`
  ADD PRIMARY KEY (`true_false_id`),
  ADD KEY `fk_questions_tf_to_answers_tf` (`question_id`),
  ADD KEY `fk_assessment_to_answers_tf` (`assessment_id`);

--
-- Indexes for table `assessment_tbl`
--
ALTER TABLE `assessment_tbl`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `class_tbl`
--
ALTER TABLE `class_tbl`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `leaderboard_tbl`
--
ALTER TABLE `leaderboard_tbl`
  ADD PRIMARY KEY (`leaderboard_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  ADD PRIMARY KEY (`module_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `questions_esy_tbl`
--
ALTER TABLE `questions_esy_tbl`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `fk_assessment_to_questions_esy` (`assessment_id`);

--
-- Indexes for table `questions_mcq_collab_tbl`
--
ALTER TABLE `questions_mcq_collab_tbl`
  ADD PRIMARY KEY (`question_id`);

--
-- Indexes for table `questions_mcq_tbl`
--
ALTER TABLE `questions_mcq_tbl`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `questions_reci_tbl`
--
ALTER TABLE `questions_reci_tbl`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `questions_tf_tbl`
--
ALTER TABLE `questions_tf_tbl`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `fk_assessment_to_questions_tf` (`assessment_id`);

--
-- Indexes for table `room_ready_tbl`
--
ALTER TABLE `room_ready_tbl`
  ADD PRIMARY KEY (`collab_id`);

--
-- Indexes for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `student_tbl`
--
ALTER TABLE `student_tbl`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `unique_student_username` (`username`),
  ADD UNIQUE KEY `student_email` (`student_email`),
  ADD UNIQUE KEY `unique_student_email` (`student_email`);

--
-- Indexes for table `teacher_tbl`
--
ALTER TABLE `teacher_tbl`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `unique_teacher_username` (`username`),
  ADD UNIQUE KEY `teacher_email` (`teacher_email`),
  ADD UNIQUE KEY `unique_teacher_email` (`teacher_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `answers_esy_tbl`
--
ALTER TABLE `answers_esy_tbl`
  MODIFY `essay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `answers_mcq_collab_tbl`
--
ALTER TABLE `answers_mcq_collab_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `answers_mcq_tbl`
--
ALTER TABLE `answers_mcq_tbl`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `answers_tf_tbl`
--
ALTER TABLE `answers_tf_tbl`
  MODIFY `true_false_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `assessment_tbl`
--
ALTER TABLE `assessment_tbl`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=298;

--
-- AUTO_INCREMENT for table `class_tbl`
--
ALTER TABLE `class_tbl`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `questions_esy_tbl`
--
ALTER TABLE `questions_esy_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `questions_mcq_collab_tbl`
--
ALTER TABLE `questions_mcq_collab_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions_mcq_tbl`
--
ALTER TABLE `questions_mcq_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `questions_reci_tbl`
--
ALTER TABLE `questions_reci_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `questions_tf_tbl`
--
ALTER TABLE `questions_tf_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `room_ready_tbl`
--
ALTER TABLE `room_ready_tbl`
  MODIFY `collab_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `student_tbl`
--
ALTER TABLE `student_tbl`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `teacher_tbl`
--
ALTER TABLE `teacher_tbl`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievement_tbl`
--
ALTER TABLE `achievement_tbl`
  ADD CONSTRAINT `achievement_tbl_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_tbl` (`student_id`);

--
-- Constraints for table `answers_esy_tbl`
--
ALTER TABLE `answers_esy_tbl`
  ADD CONSTRAINT `fk_assessment_to_answers_esy` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_questions_esy_to_answers_esy` FOREIGN KEY (`question_id`) REFERENCES `questions_esy_tbl` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `answers_mcq_collab_tbl`
--
ALTER TABLE `answers_mcq_collab_tbl`
  ADD CONSTRAINT `fk_assessment_id` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `student_tbl` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `answers_mcq_tbl`
--
ALTER TABLE `answers_mcq_tbl`
  ADD CONSTRAINT `answers_mcq_tbl_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_mcq_tbl_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions_mcq_tbl` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `answers_tf_tbl`
--
ALTER TABLE `answers_tf_tbl`
  ADD CONSTRAINT `fk_assessment_to_answers_tf` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_questions_tf_to_answers_tf` FOREIGN KEY (`question_id`) REFERENCES `questions_tf_tbl` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assessment_tbl`
--
ALTER TABLE `assessment_tbl`
  ADD CONSTRAINT `assessment_tbl_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class_tbl` (`class_id`);

--
-- Constraints for table `class_tbl`
--
ALTER TABLE `class_tbl`
  ADD CONSTRAINT `class_tbl_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_tbl` (`teacher_id`),
  ADD CONSTRAINT `class_tbl_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_tbl` (`student_id`);

--
-- Constraints for table `leaderboard_tbl`
--
ALTER TABLE `leaderboard_tbl`
  ADD CONSTRAINT `leaderboard_tbl_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class_tbl` (`class_id`),
  ADD CONSTRAINT `leaderboard_tbl_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_tbl` (`student_id`);

--
-- Constraints for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  ADD CONSTRAINT `modules_tbl_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class_tbl` (`class_id`),
  ADD CONSTRAINT `modules_tbl_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_tbl` (`teacher_id`);

--
-- Constraints for table `questions_esy_tbl`
--
ALTER TABLE `questions_esy_tbl`
  ADD CONSTRAINT `fk_assessment_to_questions_esy` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `questions_mcq_tbl`
--
ALTER TABLE `questions_mcq_tbl`
  ADD CONSTRAINT `questions_mcq_tbl_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE;

--
-- Constraints for table `questions_reci_tbl`
--
ALTER TABLE `questions_reci_tbl`
  ADD CONSTRAINT `questions_reci_tbl_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class_tbl` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_reci_tbl_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE;

--
-- Constraints for table `questions_tf_tbl`
--
ALTER TABLE `questions_tf_tbl`
  ADD CONSTRAINT `fk_assessment_to_questions_tf` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_classes`
--
ALTER TABLE `student_classes`
  ADD CONSTRAINT `student_classes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student_tbl` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_classes_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class_tbl` (`class_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
