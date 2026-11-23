-- ========================================================
-- AVO ERP Table Export: `ParticipatingHotels`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `particip_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ParticipatingHotels`;

CREATE TABLE `ParticipatingHotels` (
  `particip_id` int(11) NOT NULL AUTO_INCREMENT,
  `Progam_name` int(11) NOT NULL,
  `ParticipatingHotelsName` varchar(100) NOT NULL,
  PRIMARY KEY (`particip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
