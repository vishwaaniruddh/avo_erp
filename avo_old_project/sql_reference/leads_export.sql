-- ========================================================
-- AVO ERP Table Export: `leads_export`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 0
-- Sample Records Exported: 0 (Max Limit: 100)
-- Sort Column: `Lead_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `leads_export`;

CREATE TABLE `leads_export` (
  `Lead_id` int(11) NOT NULL AUTO_INCREMENT,
  `Title` varchar(100) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `MobileCode` int(11) NOT NULL,
  `MobileNumber` varchar(20) NOT NULL,
  `MobileCode2` int(11) NOT NULL,
  `MobileNumber2` int(11) NOT NULL,
  `contact1Code` varchar(10) NOT NULL,
  `ContactNo1` varchar(20) NOT NULL,
  `contact2Code` int(11) NOT NULL,
  `ContactNo2` varchar(20) NOT NULL,
  `contact3Code` int(11) NOT NULL,
  `ContactNo3` varchar(20) NOT NULL,
  `EmailId` varchar(50) NOT NULL,
  `FacebookId` varchar(100) NOT NULL,
  `Address1` varchar(100) NOT NULL,
  `Address2` varchar(100) NOT NULL,
  `Address3` varchar(100) NOT NULL,
  `Country` varchar(50) NOT NULL,
  `State` varchar(50) NOT NULL,
  `City` varchar(50) NOT NULL,
  `PinCode` varchar(50) NOT NULL,
  `Nationality` varchar(50) NOT NULL,
  `Company` varchar(50) NOT NULL,
  `Designation` varchar(100) NOT NULL,
  `LeadSource` varchar(100) NOT NULL,
  `Status` varchar(100) NOT NULL COMMENT '1-open,2-close,3-Suspense,4-Payment Received,5-Member,6-Payment in Process, 7- means Ready to payment, 8-means Canceled',
  `DelegationStatus` varchar(11) NOT NULL,
  `Creation` datetime NOT NULL,
  `Assigned` datetime NOT NULL,
  `CloseReason` varchar(250) NOT NULL,
  `Close` datetime NOT NULL,
  `leadEntryef` varchar(50) NOT NULL,
  `Hotel_Name` varchar(100) NOT NULL,
  `Excel` int(11) NOT NULL COMMENT '0-without Excel,1-Means something wrong,2-Means Proper Data Upload',
  `ExcelReason` varchar(100) NOT NULL,
  PRIMARY KEY (`Lead_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- (Table has no records or no sample data selected)

SET FOREIGN_KEY_CHECKS = 1;
