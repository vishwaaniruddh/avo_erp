-- ========================================================
-- AVO ERP Table Export: `Location`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Location`;

CREATE TABLE `Location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(50) NOT NULL,
  `latitude` double(10,6) NOT NULL,
  `longitude` double(10,6) NOT NULL,
  `dt` datetime NOT NULL,
  `address` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mac_address` (`mac_address`),
  KEY `dt` (`dt`),
  KEY `latitude` (`latitude`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
