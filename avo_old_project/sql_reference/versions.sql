-- ========================================================
-- AVO ERP Table Export: `versions`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: Default Natural Order
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `versions`;

CREATE TABLE `versions` (
  `version` varchar(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `versions`
-- --------------------------------------------------------

INSERT INTO `versions` (`version`) VALUES
(1.0);

SET FOREIGN_KEY_CHECKS = 1;
