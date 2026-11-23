-- ========================================================
-- AVO ERP Table Export: `roll`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 3
-- Sample Records Exported: 3 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `roll`;

CREATE TABLE `roll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roll` varchar(100) NOT NULL,
  `permission` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 3 records for table `roll`
-- --------------------------------------------------------

INSERT INTO `roll` (`id`, `roll`, `permission`) VALUES
(1, 'Admin', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20...'),
(2, 'Sales Associate', '46,47,49,50'),
(3, 'Fulfillment Team', '12,13,47,49,51,52,53,54,55,56,12,13');

SET FOREIGN_KEY_CHECKS = 1;
