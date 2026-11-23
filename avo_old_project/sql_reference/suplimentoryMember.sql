-- ========================================================
-- AVO ERP Table Export: `suplimentoryMember`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `SupID` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `suplimentoryMember`;

CREATE TABLE `suplimentoryMember` (
  `SupID` int(11) NOT NULL AUTO_INCREMENT,
  `Memberid` varchar(50) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Relationship` varchar(50) NOT NULL,
  `DateOfBirth` date NOT NULL,
  `Email` varchar(50) NOT NULL,
  `MobileCode` int(11) NOT NULL,
  `MobileNumber` int(11) NOT NULL,
  `NameOnTheCard` varchar(22) NOT NULL,
  `PaymentMode` varchar(50) NOT NULL,
  `Amount` int(11) NOT NULL,
  `entryDate` datetime NOT NULL,
  PRIMARY KEY (`SupID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
