-- ========================================================
-- AVO ERP Table Export: `validity`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 2
-- Sample Records Exported: 2 (Max Limit: 100)
-- Sort Column: `validity_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `validity`;

CREATE TABLE `validity` (
  `validity_id` int(11) NOT NULL AUTO_INCREMENT,
  `Leval_id` int(11) NOT NULL,
  `Program_ID` int(11) NOT NULL,
  `Expiry_month` varchar(200) NOT NULL,
  PRIMARY KEY (`validity_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 2 records for table `validity`
-- --------------------------------------------------------

INSERT INTO `validity` (`validity_id`, `Leval_id`, `Program_ID`, `Expiry_month`) VALUES
(1, 1, 1, 12),
(2, 2, 1, 12);

SET FOREIGN_KEY_CHECKS = 1;
