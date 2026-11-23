-- ========================================================
-- AVO ERP Table Export: `login`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `login`;

CREATE TABLE `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `designation` int(11) NOT NULL,
  `register_id` int(11) NOT NULL,
  `permission` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
