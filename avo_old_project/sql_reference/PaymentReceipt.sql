-- ========================================================
-- AVO ERP Table Export: `PaymentReceipt`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `PayReceipt_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `PaymentReceipt`;

CREATE TABLE `PaymentReceipt` (
  `PayReceipt_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `FromSeries` varchar(100) NOT NULL,
  `ToSeries` varchar(100) NOT NULL,
  `ReceiptFormat` varchar(200) NOT NULL,
  `CountRecipt` int(11) NOT NULL,
  PRIMARY KEY (`PayReceipt_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
