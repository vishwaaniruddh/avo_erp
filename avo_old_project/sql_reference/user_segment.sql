-- ========================================================
-- AVO ERP Table Export: `user_segment`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 13
-- Sample Records Exported: 13 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `user_segment`;

CREATE TABLE `user_segment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seg_name` varchar(50) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 13 records for table `user_segment`
-- --------------------------------------------------------

INSERT INTO `user_segment` (`id`, `seg_name`, `status`, `created_at`) VALUES
(17, 'BFSI', 1, '2019-12-24 16:40:13'),
(18, 'Commercial', 1, '2019-12-24 14:29:06'),
(19, 'Govt', 1, '2019-12-24 14:29:31'),
(20, 'Defence', 1, '2019-12-24 14:30:33'),
(21, 'Energy', 1, '2019-12-24 14:30:46'),
(22, 'Industry', 1, '2019-12-24 14:31:00'),
(23, 'IT-BPO', 1, '2019-12-24 14:31:16'),
(24, 'Health', 1, '2019-12-24 14:31:25'),
(25, 'Infra', 1, '2019-12-24 14:31:33'),
(26, 'Others', 1, '2019-12-24 14:31:42'),
(27, '', 0, '2019-12-24 15:58:28'),
(28, '', 0, '2019-12-24 15:58:24'),
(29, 'vishal', 0, '2020-02-28 13:43:24');

SET FOREIGN_KEY_CHECKS = 1;
