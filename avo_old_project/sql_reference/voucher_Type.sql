-- ========================================================
-- AVO ERP Table Export: `voucher_Type`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `V_type_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `voucher_Type`;

CREATE TABLE `voucher_Type` (
  `V_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `serialNumber` varchar(200) NOT NULL,
  `serviceName` varchar(200) NOT NULL,
  `status` int(11) NOT NULL COMMENT '0- means not used , 1- means used',
  PRIMARY KEY (`V_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `voucher_Type`
-- --------------------------------------------------------

INSERT INTO `voucher_Type` (`V_type_id`, `Program_ID`, `level_id`, `serialNumber`, `serviceName`, `status`) VALUES
(1, 1, 1, 10000001, '5 star hotel', 0);

SET FOREIGN_KEY_CHECKS = 1;
