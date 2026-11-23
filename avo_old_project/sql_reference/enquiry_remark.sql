-- ========================================================
-- AVO ERP Table Export: `enquiry_remark`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 5
-- Sample Records Exported: 5 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `enquiry_remark`;

CREATE TABLE `enquiry_remark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enquiry_id` int(11) DEFAULT NULL,
  `remark` varchar(200) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 5 records for table `enquiry_remark`
-- --------------------------------------------------------

INSERT INTO `enquiry_remark` (`id`, `enquiry_id`, `remark`, `created_at`) VALUES
(1, 3, 'something', '2020-04-22'),
(2, 3, 'customer was busy so swtch', '2020-04-22'),
(3, 2, 'difficult to get', '2020-04-22'),
(4, 7, 'some remark showjd', '2020-04-23'),
(5, 1, 'ek dummy remark', '2020-04-23');

SET FOREIGN_KEY_CHECKS = 1;
