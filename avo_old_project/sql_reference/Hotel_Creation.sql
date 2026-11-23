-- ========================================================
-- AVO ERP Table Export: `Hotel_Creation`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 2
-- Sample Records Exported: 2 (Max Limit: 100)
-- Sort Column: `hotel_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Hotel_Creation`;

CREATE TABLE `Hotel_Creation` (
  `hotel_id` int(11) NOT NULL AUTO_INCREMENT,
  `logo` text NOT NULL,
  `Hotel_Name` varchar(250) NOT NULL,
  `Brand` int(11) NOT NULL,
  PRIMARY KEY (`hotel_id`),
  KEY `fk_Brand` (`Brand`),
  CONSTRAINT `fk_Brand` FOREIGN KEY (`Brand`) REFERENCES `Brand` (`Brand_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 2 records for table `Hotel_Creation`
-- --------------------------------------------------------

INSERT INTO `Hotel_Creation` (`hotel_id`, `logo`, `Hotel_Name`, `Brand`) VALUES
(1, 'assets/img/logos/card.png', 'Shyambabadham', 1),
(2, 'assets/img/logos/card.png', 'Shaym ji Hotel', 1);

SET FOREIGN_KEY_CHECKS = 1;
