-- ========================================================
-- AVO ERP Table Export: `Program`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 2
-- Sample Records Exported: 2 (Max Limit: 100)
-- Sort Column: `Program_ID` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Program`;

CREATE TABLE `Program` (
  `Program_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Hotel_id` int(11) NOT NULL,
  `Progam_name` varchar(250) NOT NULL,
  PRIMARY KEY (`Program_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 2 records for table `Program`
-- --------------------------------------------------------

INSERT INTO `Program` (`Program_ID`, `Hotel_id`, `Progam_name`) VALUES
(1, 1, 'Lead Entry'),
(2, 2, 'Lead entry by salea Associate');

SET FOREIGN_KEY_CHECKS = 1;
