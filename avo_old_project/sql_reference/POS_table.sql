-- ========================================================
-- AVO ERP Table Export: `POS_table`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `POS_ID` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `POS_table`;

CREATE TABLE `POS_table` (
  `POS_ID` int(11) NOT NULL AUTO_INCREMENT,
  `City` varchar(100) NOT NULL,
  `CertificateNumber` int(11) NOT NULL,
  `Time` time NOT NULL,
  `BillDate` date NOT NULL,
  `POS_code` int(11) NOT NULL,
  `CheckNo` int(11) NOT NULL,
  `No_of_Pax` int(11) NOT NULL,
  `No_of_paxClose` int(11) NOT NULL,
  `TenderMediaNumber` int(11) NOT NULL,
  `FoodAmt` int(11) NOT NULL,
  `FoodDiscAmt` int(11) NOT NULL,
  `SoftBevAmt` int(11) NOT NULL,
  `SoftBevDiscAmt` int(11) NOT NULL,
  `IndianLiqAmt` int(11) NOT NULL,
  `IndianLiqDiscAmt` int(11) NOT NULL,
  `ImpLiqAmt` int(11) NOT NULL,
  `ImpLiqDiscAmt` int(11) NOT NULL,
  `TobaccoAmt` int(11) NOT NULL,
  `TobaccoDiscAmt` int(11) NOT NULL,
  `MiscAmt` int(11) NOT NULL,
  `MiscDiscAmt` int(11) NOT NULL,
  `Tips` int(11) NOT NULL,
  `AutomaticServiceCharge` int(11) NOT NULL,
  `NettAmount` int(11) NOT NULL,
  `TotalTaxCollected` int(11) NOT NULL,
  `GrossTotal` int(11) NOT NULL,
  `CashierNumber` int(11) NOT NULL,
  `type` varchar(250) NOT NULL COMMENT '1- means memberid, 2- means voucher',
  PRIMARY KEY (`POS_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
