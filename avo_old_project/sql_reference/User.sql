-- ========================================================
-- AVO ERP Table Export: `User`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 4
-- Sample Records Exported: 4 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `User`;

CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_fname` varchar(50) DEFAULT NULL,
  `user_lname` varchar(50) DEFAULT NULL,
  `user_email` varchar(50) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `user_perm` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 4 records for table `User`
-- --------------------------------------------------------

INSERT INTO `User` (`id`, `user_fname`, `user_lname`, `user_email`, `user_role`, `user_perm`, `status`, `created_by`, `created_at`) VALUES
(1, 'aniruddh', 'vishwakarma', 'aniruddhvishwa@gmail.com', 'Admin', 'null', 1, 'me', '2019-12-18 10:14:25'),
(2, 'aniruddh', 'vishwakarma', 'aniruddhvishwa@gmail.com', 'Super Admin', 'null', 1, 'me', '2019-12-18 10:17:40'),
(3, 'aniruddh', 'vishwakarma', 'aniruddhvishwa@gmail.com', 'Super Admin', '[\"1\",\"1\",\"1\"]', 1, 'me', '2019-12-18 10:18:12'),
(4, 'aniruddh', 'vishwakarma', 'aniruddhvishwa@gmail.com', 'Manager', '[\"1\",\"1\",\"1\"]', 1, 'me', '2019-12-18 10:18:48');

SET FOREIGN_KEY_CHECKS = 1;
