-- ========================================================
-- AVO ERP Table Export: `RenewalBenefits`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `RenewalBenefits_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `RenewalBenefits`;

CREATE TABLE `RenewalBenefits` (
  `RenewalBenefits_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `RenewalBenefits` varchar(256) NOT NULL,
  PRIMARY KEY (`RenewalBenefits_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
