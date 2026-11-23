-- ========================================================
-- AVO ERP Table Export: `Lead_Sources`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 6
-- Sample Records Exported: 6 (Max Limit: 100)
-- Sort Column: `SourceId` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Lead_Sources`;

CREATE TABLE `Lead_Sources` (
  `SourceId` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Description` varchar(250) NOT NULL,
  `Active` varchar(50) NOT NULL,
  PRIMARY KEY (`SourceId`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 6 records for table `Lead_Sources`
-- --------------------------------------------------------

INSERT INTO `Lead_Sources` (`SourceId`, `Name`, `Description`, `Active`) VALUES
(1, 'Rajesh Modi', 'Idea Man', 'YES'),
(2, 'Facebook', 'Facebook', 'YES'),
(3, 'Associates', 'Marketing Team', 'YES'),
(4, 'Google', 'Google', 'YES'),
(5, 'Office', 'Office', 'YES'),
(6, 'Sharad', 'sir', 'YES');

SET FOREIGN_KEY_CHECKS = 1;
