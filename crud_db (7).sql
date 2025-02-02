-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2025 at 12:40 PM
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

--
-- Dumping data for table `achievement_tbl`
--

INSERT INTO `achievement_tbl` (`achievement_id`, `student_id`, `badge_name`, `badge_earned_date`) VALUES
(1, 8, '10 Modules Master', '2025-01-19'),
(2, 8, 'Assessment Beginner', '2025-01-19'),
(3, 8, 'Collaboration Novice', '2025-01-21'),
(4, 4, 'Assessment Beginner', '2025-01-22'),
(5, 4, 'Collaboration Novice', '2025-01-22'),
(6, 7, 'Assessment Beginner', '2025-01-22'),
(7, 7, 'Collaboration Novice', '2025-01-22');

-- --------------------------------------------------------

--
-- Table structure for table `answers_esy_collab_tbl`
--

CREATE TABLE `answers_esy_collab_tbl` (
  `collab_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `student_id` int(11) NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `grades` decimal(5,2) DEFAULT NULL,
  `attempt` int(11) NOT NULL DEFAULT 1,
  `ready` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `answers_esy_collab_tbl`
--

INSERT INTO `answers_esy_collab_tbl` (`collab_id`, `room_id`, `assessment_id`, `question_id`, `answer`, `student_id`, `submitted_at`, `grades`, `attempt`, `ready`) VALUES
(135, 9805, 329, 175, 'adw', 8, '2025-02-02 10:59:24', 0.00, 1, 0),
(136, 9805, 329, 175, '', 8, '2025-02-02 11:09:00', 0.00, 1, 0),
(137, 9805, 329, 175, 'asd', 4, '2025-02-02 11:38:08', 0.00, 1, 0),
(138, 9805, 329, 176, 'asd', 4, '2025-02-02 11:38:24', 0.00, 1, 0);

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

--
-- Dumping data for table `answers_mcq_tbl`
--

INSERT INTO `answers_mcq_tbl` (`answer_id`, `assessment_id`, `question_id`, `student_id`, `selected_option`, `Attempt`, `correct_answer`) VALUES
(48, 327, 145, 10, 'D', 1, 0),
(49, 327, 146, 10, 'D', 1, 1),
(50, 327, 147, 10, 'A', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `answers_tf_tbl`
--

CREATE TABLE `answers_tf_tbl` (
  `true_false_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` enum('True','False') NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `correct_answer` tinyint(11) NOT NULL,
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
  `total_points` int(11) DEFAULT 0,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_tbl`
--

INSERT INTO `assessment_tbl` (`assessment_id`, `type`, `status`, `time_limit`, `created_at`, `assessment_mode`, `class_id`, `teacher_id`, `name`, `total_points`, `instructions`) VALUES
(325, 'Essay', 'Saved', 10, '2025-01-22 23:38:17', 'Individual', 8, 3, 'essay 1', 30, 'Read Essay Instruction'),
(326, 'True or False', 'Saved', 10, '2025-01-22 23:39:29', 'Individual', 8, 3, 'true false 1', 5, ''),
(327, 'Multiple Choice - Individual', 'Published', 10, '2025-01-22 23:40:59', 'Individual', 8, 3, 'mcq indiv 1', 3, ''),
(328, 'Multiple Choice - Collaborative', 'Published', 10, '2025-01-22 23:43:38', 'Individual', 8, 3, 'mcq collab 1', 20, ''),
(329, 'Essay - Collaborative', 'Published', 100, '2025-01-29 09:34:37', 'Individual', 8, 3, 'Essay collab', 2, 'a');

-- --------------------------------------------------------

--
-- Table structure for table `chat_history`
--

CREATE TABLE `chat_history` (
  `chat_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `student_id` int(11) NOT NULL,
  `time_and_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_history`
--

INSERT INTO `chat_history` (`chat_id`, `assessment_id`, `room_id`, `content`, `student_id`, `time_and_date`) VALUES
(31, 329, 7328, 'awd', 8, '2025-01-29 16:27:33'),
(32, 329, 7328, 'wada', 7, '2025-01-29 16:27:35'),
(33, 329, 7328, 'awd', 8, '2025-01-29 16:27:37'),
(34, 329, 7328, 'daw', 8, '2025-01-29 18:07:21'),
(35, 329, 7328, 'dwa', 8, '2025-01-29 18:16:17'),
(36, 329, 7328, 'dw', 7, '2025-01-29 18:21:21'),
(37, 329, 7328, 'dadwa', 7, '2025-01-29 18:21:24'),
(38, 329, 2235, 'adw', 8, '2025-01-29 18:28:32'),
(39, 329, 6198, 'HELLOOO', 8, '2025-02-02 10:31:07'),
(40, 329, 6198, 'HELP', 7, '2025-02-02 10:31:17'),
(41, 329, 6198, 'ANSWER 2', 8, '2025-02-02 10:33:52');

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
(16, 8, 3, 'Title ', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.\r\n\r\nWhy do we use it?\r\nIt is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using \'Content here, content here\', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for \'lorem ipsum\' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).', NULL, NULL, '2025-01-15 11:53:12', 'Saved'),
(17, 8, 3, 'a', 'a', NULL, NULL, '2025-01-17 08:45:04', 'Published'),
(18, 8, 3, 'a', 'a', NULL, NULL, '2025-01-17 08:45:08', 'Saved');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tbl`
--

CREATE TABLE `password_reset_tbl` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tbl`
--

INSERT INTO `password_reset_tbl` (`id`, `email`, `user_type`, `token`, `expires_at`) VALUES
(1, 'allencarlo32@gmail.com', 'student', '5c79807960c636711742fd03aaaa76d38241fe83d19326a1b42189502a21f723', '2025-01-22 03:54:36'),
(2, 'allencarlo32@gmail.com', 'student', '28deabf936f6b64ec4bc2fc3ccc0c9f3a42e5e55614111c8d6ad93d6a2396134', '2025-01-22 03:56:40'),
(3, 'allencarlo32@gmail.com', 'student', '42ece1e226a4739d4b5aaeb9a3c101aa21025be08483d4befa04f16412cf7434', '2025-01-22 03:56:56');

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

--
-- Dumping data for table `questions_esy_tbl`
--

INSERT INTO `questions_esy_tbl` (`question_id`, `assessment_id`, `question_text`, `question_number`, `points`, `guided_answer`, `correct_answer`) VALUES
(172, 325, 'q1', 0, 10, '', 'True'),
(173, 325, 'q2', 0, 5, '', 'True'),
(174, 325, 'q3', 0, 15, '', 'True'),
(175, 329, 'a', 0, 1, '1', 'True'),
(176, 329, 'a', 0, 1, 'a1', 'True');

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

--
-- Dumping data for table `questions_mcq_tbl`
--

INSERT INTO `questions_mcq_tbl` (`question_id`, `assessment_id`, `question_text`, `options`, `correct_option`, `points`) VALUES
(143, 328, ' A company wants to reduce its environmental impact by adopting sustainable practices. Which of the following actions would best achieve this goal?', '{\"A\":\"Increasing the use of plastic packaging\",\"B\":\"Implementing a recycling program and reducing waste\",\"C\":\"Expanding production without environmental considerations\",\"D\":\"Using non-renewable energy sources\"}', 'B', 10),
(144, 328, 'A project team is facing conflicts due to differing opinions on how to execute a task. What is the best approach to resolve this conflict collaboratively?', '{\"A\":\"Ignore the conflict and proceed with the majority\'s decision\",\"B\":\"Allow only the team leader to decide\",\"C\":\"Conduct an open discussion and find a compromise\",\"D\":\"Remove the team members who disagree\"}', 'C', 10),
(145, 327, ' What is the capital of France?', '{\"A\":\"Berlin\",\"B\":\"Madrid\",\"C\":\"Paris\",\"D\":\"Rome\"}', 'C', 1),
(146, 327, 'Which planet is known as the Red Planet?', '{\"A\":\"Earth\",\"B\":\"Venus\",\"C\":\"Jupiter\",\"D\":\"Mars\"}', 'D', 1),
(147, 327, 'What is the chemical symbol for gold?', '{\"A\":\"Au\",\"B\":\"Ag\",\"C\":\"Fe\",\"D\":\"Pb\"}', 'A', 1);

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

--
-- Dumping data for table `questions_tf_tbl`
--

INSERT INTO `questions_tf_tbl` (`question_id`, `assessment_id`, `question_text`, `question_number`, `points`, `guided_answer`, `correct_answer`) VALUES
(27, 326, 'The Great Wall of China is visible from space.', 0, 1, NULL, 'False'),
(28, 326, 'Water boils at a lower temperature at higher altitudes.', 0, 1, NULL, 'True'),
(29, 326, 'The human body has four lungs.', 0, 1, NULL, 'False'),
(30, 326, 'Lightning never strikes the same place twice.', 0, 1, NULL, 'False'),
(31, 326, 'The Pacific Ocean is the largest ocean on Earth.', 0, 1, NULL, 'True');

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
  `status` enum('waiting','started') DEFAULT 'waiting',
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_ready_tbl`
--

INSERT INTO `room_ready_tbl` (`collab_id`, `room_id`, `student_id`, `assessment_id`, `is_ready`, `is_host`, `status`, `class_id`) VALUES
(207, 4736, 8, 328, 1, 1, 'started', 8),
(208, 4736, 7, 328, 1, 1, 'started', 8),
(209, 1384, 7, 328, 0, 1, 'waiting', 8),
(212, 4332, 7, 328, 1, 1, 'started', 8),
(213, 8901, 8, 329, 1, 1, 'started', 8),
(214, 8800, 8, 329, 1, 1, 'started', 8),
(215, 6045, 8, 329, 1, 1, 'started', 8),
(216, 3034, 8, 329, 1, 1, 'started', 8),
(217, 1666, 7, 329, 1, 1, 'started', 8),
(218, 1666, 8, 329, 1, 0, 'started', 8),
(219, 5896, 8, 329, 1, 1, 'started', 8),
(220, 5896, 7, 329, 1, 0, 'started', 8),
(222, 8495, 8, 329, 1, 1, 'started', 8),
(224, 8283, 8, 329, 1, 1, 'started', 8),
(225, 8283, 7, 329, 1, 0, 'started', 8),
(226, 8002, 7, 329, 1, 1, 'started', 8),
(227, 8002, 8, 329, 1, 0, 'started', 8),
(228, 5789, 8, 329, 1, 1, 'started', 8),
(229, 8816, 8, 329, 1, 1, 'started', 8),
(230, 4928, 8, 329, 1, 1, 'started', 8),
(231, 6145, 8, 329, 1, 1, 'started', 8),
(232, 3805, 8, 329, 1, 1, 'started', 8),
(233, 5908, 8, 329, 1, 1, 'started', 8),
(234, 1166, 8, 329, 1, 1, 'started', 8),
(235, 2990, 8, 329, 1, 1, 'started', 8),
(236, 7334, 8, 329, 1, 1, 'started', 8),
(238, 7334, 7, 329, 1, 0, 'started', 8),
(240, 7328, 7, 329, 1, 1, 'started', 8),
(241, 7328, 8, 329, 1, 0, 'started', 8),
(242, 2235, 8, 329, 1, 1, 'started', 8),
(243, 6198, 8, 329, 1, 1, 'started', 8),
(244, 6198, 7, 329, 1, 0, 'started', 8),
(245, 9805, 8, 329, 1, 1, 'started', 8),
(246, 9805, 4, 329, 1, 0, 'started', 8);

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
(10, 8, 9, NULL, NULL),
(11, 10, 8, NULL, NULL),
(12, 11, 8, NULL, NULL);

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
  `student_last_name` varchar(100) NOT NULL,
  `ach_last_login` date NOT NULL,
  `ach_streak` int(11) NOT NULL,
  `ach_modules_read` int(11) NOT NULL,
  `ach_answered_assessments` int(11) NOT NULL,
  `ach_collaborated` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_tbl`
--

INSERT INTO `student_tbl` (`student_id`, `username`, `student_email`, `student_password`, `student_first_name`, `student_last_name`, `ach_last_login`, `ach_streak`, `ach_modules_read`, `ach_answered_assessments`, `ach_collaborated`) VALUES
(1, 'cg18', 'cg18@gmail.com', '$2y$10$1YZZE5SAx7DjuIXnL1Zy2ufObrhMCc/fkOXx7IPX3SDk4whjNWfEK', 'Crisha', 'Hernandez', '0000-00-00', 0, 0, 0, 0),
(2, 'Princes123', 'cgm1@gmail.com', '$2y$10$vywHgfkgjZ17aqwQWcLkP.CKL3HAfdzyCqFvzYLPxQ4UW5B.NFJQW', 'Princess', 'Liu', '0000-00-00', 0, 0, 0, 0),
(4, 'rose1', 'rose1@gmail.com', '$2y$10$64vLb2.JwyNvp0vtDW9bK.S74MTtC0QW9S8lcME6YNWnWWwuFqwdK', 'Rose', 'Tiu', '2025-02-02', 0, 0, 22, 75),
(6, 'Rose23', 'rose23@gmail.com', '$2y$10$XKsIR1wHzwwHkO.0zey81ONHozfLNGaJ/QE0MtXa0W34vaGShiuiW', 'Rosalyn', 'Kira', '0000-00-00', 0, 0, 0, 0),
(7, 'student1', 's1@gmail.com', '$2y$10$f9Ds7a79L/l1vzE0T0jD5.oXtuliBUbJSjlB6jjPaTjwZKhX.4MFe', 'Student1', 'Student1', '2025-01-29', 2, 8, 66, 237),
(8, 'student', 'wdadwafawfWQd@gmail.com', '$2y$10$fEKVJGfM7i5CbZnJe0ipMuvAYZw/2DkgU5KFYGWhB/eU4UCNtut0W', 'student', 'student', '2025-02-02', 0, 14, 94, 264),
(9, 'cccc', 'ccc@gmail.com', '$2y$10$CHnOEZ8sMjTl6Idm5OcrZ.sFZiFLN8E4paEvP7M9JdyaXEC6kM5kS', 'cccc', 'cccc', '0000-00-00', 0, 0, 0, 0),
(10, 'aaa', 'allencarlo32@gmail.com', '$2y$10$llMpxGNNzopHVeHyszFDx.20E9JDYIHQAtVC6ghKugQ0Rz6qbM3iG', 'aaa', 'aaa', '2025-01-28', 1, 0, 12, 62),
(11, 'studentstudent', 'studentstudent@gmail.com', '$2y$10$ZdBALYnSx6DnBdjnIwKxF.y9aqE8/dR/ziwQlx6rnsFwTX0BMI/Pu', 'studentstudent', 'studentstudent', '2025-01-28', 1, 0, 4, 15);

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
-- Indexes for table `answers_esy_collab_tbl`
--
ALTER TABLE `answers_esy_collab_tbl`
  ADD PRIMARY KEY (`collab_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `question_id` (`question_id`),
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
-- Indexes for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `student_id` (`student_id`);

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
-- Indexes for table `password_reset_tbl`
--
ALTER TABLE `password_reset_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`token`);

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
  ADD PRIMARY KEY (`collab_id`),
  ADD KEY `fk_class_id` (`class_id`);

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
-- AUTO_INCREMENT for table `achievement_tbl`
--
ALTER TABLE `achievement_tbl`
  MODIFY `achievement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `answers_esy_collab_tbl`
--
ALTER TABLE `answers_esy_collab_tbl`
  MODIFY `collab_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `answers_esy_tbl`
--
ALTER TABLE `answers_esy_tbl`
  MODIFY `essay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `answers_mcq_collab_tbl`
--
ALTER TABLE `answers_mcq_collab_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=485;

--
-- AUTO_INCREMENT for table `answers_mcq_tbl`
--
ALTER TABLE `answers_mcq_tbl`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `answers_tf_tbl`
--
ALTER TABLE `answers_tf_tbl`
  MODIFY `true_false_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `assessment_tbl`
--
ALTER TABLE `assessment_tbl`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT for table `chat_history`
--
ALTER TABLE `chat_history`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `class_tbl`
--
ALTER TABLE `class_tbl`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  MODIFY `module_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `password_reset_tbl`
--
ALTER TABLE `password_reset_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `questions_esy_tbl`
--
ALTER TABLE `questions_esy_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `questions_mcq_collab_tbl`
--
ALTER TABLE `questions_mcq_collab_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions_mcq_tbl`
--
ALTER TABLE `questions_mcq_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `questions_reci_tbl`
--
ALTER TABLE `questions_reci_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `questions_tf_tbl`
--
ALTER TABLE `questions_tf_tbl`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `room_ready_tbl`
--
ALTER TABLE `room_ready_tbl`
  MODIFY `collab_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `student_classes`
--
ALTER TABLE `student_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `student_tbl`
--
ALTER TABLE `student_tbl`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
-- Constraints for table `answers_esy_collab_tbl`
--
ALTER TABLE `answers_esy_collab_tbl`
  ADD CONSTRAINT `answers_esy_collab_tbl_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_esy_collab_tbl_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions_esy_tbl` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `answers_esy_collab_tbl_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `student_tbl` (`student_id`) ON DELETE CASCADE;

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
-- Constraints for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment_tbl` (`assessment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_history_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student_tbl` (`student_id`) ON DELETE CASCADE;

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
-- Constraints for table `room_ready_tbl`
--
ALTER TABLE `room_ready_tbl`
  ADD CONSTRAINT `fk_class_id` FOREIGN KEY (`class_id`) REFERENCES `class_tbl` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
