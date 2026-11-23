-- ========================================================
-- AVO ERP Table Export: `Brand`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `Brand_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Brand`;

CREATE TABLE `Brand` (
  `Brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `Brand_name` varchar(250) NOT NULL,
  PRIMARY KEY (`Brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `Brand`
-- --------------------------------------------------------

INSERT INTO `Brand` (`Brand_id`, `Brand_name`) VALUES
(1, 'Babadham');

SET FOREIGN_KEY_CHECKS = 1;
