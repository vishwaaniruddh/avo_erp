-- ========================================================
-- AVO ERP Table Export: `voucher_Booklet`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `v_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `voucher_Booklet`;

CREATE TABLE `voucher_Booklet` (
  `v_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `FromSerialNo` varchar(250) NOT NULL,
  `ToSerialNo` varchar(250) NOT NULL,
  `Level_id` int(11) NOT NULL,
  `AssignBooklet` bigint(20) NOT NULL,
  PRIMARY KEY (`v_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `voucher_Booklet`
-- --------------------------------------------------------

INSERT INTO `voucher_Booklet` (`v_id`, `Program_ID`, `FromSerialNo`, `ToSerialNo`, `Level_id`, `AssignBooklet`) VALUES
(1, 1, 10000001, 99000000, 1, 0);

SET FOREIGN_KEY_CHECKS = 1;
