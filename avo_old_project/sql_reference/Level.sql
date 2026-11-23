-- ========================================================
-- AVO ERP Table Export: `Level`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 8
-- Sample Records Exported: 8 (Max Limit: 100)
-- Sort Column: `Leval_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Level`;

CREATE TABLE `Level` (
  `Leval_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `level_name` varchar(200) NOT NULL,
  PRIMARY KEY (`Leval_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 8 records for table `Level`
-- --------------------------------------------------------

INSERT INTO `Level` (`Leval_id`, `Program_ID`, `level_name`) VALUES
(1, 2, 'Country'),
(2, 2, 'Zone'),
(3, 2, 'State'),
(4, 2, 'City'),
(5, 2, 'District'),
(6, 2, 'Taluka'),
(7, 2, 'Pincode'),
(8, 2, 'Village');

SET FOREIGN_KEY_CHECKS = 1;
