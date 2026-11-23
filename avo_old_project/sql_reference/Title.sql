-- ========================================================
-- AVO ERP Table Export: `Title`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 2
-- Sample Records Exported: 2 (Max Limit: 100)
-- Sort Column: `title_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Title`;

CREATE TABLE `Title` (
  `title_id` int(11) NOT NULL AUTO_INCREMENT,
  `titleName` varchar(256) NOT NULL,
  PRIMARY KEY (`title_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 2 records for table `Title`
-- --------------------------------------------------------

INSERT INTO `Title` (`title_id`, `titleName`) VALUES
(1, 'Mr'),
(2, 'Mrs');

SET FOREIGN_KEY_CHECKS = 1;
