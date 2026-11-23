-- ========================================================
-- AVO ERP Table Export: `CardBenefit`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `CardBenefit_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `CardBenefit`;

CREATE TABLE `CardBenefit` (
  `CardBenefit_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `CardBenefit` varchar(256) NOT NULL,
  PRIMARY KEY (`CardBenefit_id`),
  KEY `foreign key` (`Program_ID`),
  CONSTRAINT `foreign key` FOREIGN KEY (`Program_ID`) REFERENCES `Program` (`Program_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `CardBenefit`
-- --------------------------------------------------------

INSERT INTO `CardBenefit` (`CardBenefit_id`, `Program_ID`, `level_id`, `CardBenefit`) VALUES
(1, 2, 1, 'aaa');

SET FOREIGN_KEY_CHECKS = 1;
