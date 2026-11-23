-- ========================================================
-- AVO ERP Table Export: `voucher_Details`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `voucher_Id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `voucher_Details`;

CREATE TABLE `voucher_Details` (
  `voucher_Id` int(11) NOT NULL AUTO_INCREMENT,
  `MembershipNumber` varchar(250) NOT NULL,
  `VoucherBookletNumber` varchar(250) NOT NULL,
  PRIMARY KEY (`voucher_Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
