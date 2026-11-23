-- ========================================================
-- AVO ERP Table Export: `MembershipNumberSeries`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `num_series_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `MembershipNumberSeries`;

CREATE TABLE `MembershipNumberSeries` (
  `num_series_id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) NOT NULL,
  `p_level_id` int(11) NOT NULL,
  `FromSeries` varchar(200) NOT NULL,
  `ToSeries` varchar(200) NOT NULL,
  PRIMARY KEY (`num_series_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
