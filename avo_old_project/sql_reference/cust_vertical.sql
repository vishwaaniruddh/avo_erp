-- ========================================================
-- AVO ERP Table Export: `cust_vertical`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 5
-- Sample Records Exported: 5 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `cust_vertical`;

CREATE TABLE `cust_vertical` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cust_vertical_name` varchar(50) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 5 records for table `cust_vertical`
-- --------------------------------------------------------

INSERT INTO `cust_vertical` (`id`, `cust_vertical_name`, `status`, `created_at`) VALUES
(1, 'Mphasis', 1, '2019-12-24 15:57:46'),
(2, 'EPS', 0, '2020-03-16 14:05:21'),
(3, 'Tata', 1, '2020-03-16 14:04:49'),
(4, 'EPS', 1, '2020-03-16 14:05:12'),
(5, 'Hitachi', 1, '2020-12-21 11:08:37');

SET FOREIGN_KEY_CHECKS = 1;
