-- ========================================================
-- AVO ERP Table Export: `roles`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 3
-- Sample Records Exported: 3 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) DEFAULT NULL,
  `permission` varchar(50) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 3 records for table `roles`
-- --------------------------------------------------------

INSERT INTO `roles` (`id`, `role`, `permission`, `status`, `created_at`) VALUES
(1, 'Head', '[\"1\",\"1\",\"1\"]', 1, '0000-00-00 00:00:00'),
(2, 'Sales Exec', '[\"1\",\"1\",\"1\"]', 1, '2020-05-17 04:36:44'),
(4, 'Team Lead', '[\"1\",\"1\",\"1\"]', 1, '2020-03-16 14:04:16');

SET FOREIGN_KEY_CHECKS = 1;
