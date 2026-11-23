-- ========================================================
-- AVO ERP Table Export: `Leads_table`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 3
-- Sample Records Exported: 3 (Max Limit: 100)
-- Sort Column: `Lead_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Leads_table`;

CREATE TABLE `Leads_table` (
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
  `pincodeOfArea` varchar(250) NOT NULL,
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
  `leadEntryef` int(11) NOT NULL,
  `Hotel_Name` varchar(100) NOT NULL,
  `Excel` int(11) NOT NULL COMMENT '0-without Excel,1-Means something wrong,2-Means Proper Data Upload',
  `ExcelReason` varchar(100) NOT NULL,
  PRIMARY KEY (`Lead_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 3 records for table `Leads_table`
-- --------------------------------------------------------

INSERT INTO `Leads_table` (`Lead_id`, `Title`, `FirstName`, `LastName`, `MobileCode`, `MobileNumber`, `MobileCode2`, `MobileNumber2`, `contact1Code`, `ContactNo1`, `contact2Code`, `ContactNo2`, `contact3Code`, `ContactNo3`, `EmailId`, `FacebookId`, `Address1`, `Address2`, `Address3`, `Country`, `State`, `City`, `PinCode`, `pincodeOfArea`, `Nationality`, `Company`, `Designation`, `LeadSource`, `Status`, `DelegationStatus`, `Creation`, `Assigned`, `CloseReason`, `Close`, `leadEntryef`, `Hotel_Name`, `Excel`, `ExcelReason`) VALUES
(1, 'Mr', 'Aniruddh', 'Test', 91, 7021889883, 0, 0, '020', '', 20, '', 20, '', 'aniruddhvishwa@gmail.com', '', '', '', '', 'India', 'MAHARASHTRA', 'Mumbai', 400064, 400064, 'Indian', 'Dew Digital', 'CEO', 5, 7, '', '2019-11-01 19:46:40', '2019-11-02 13:21:21', ' ', '2019-11-02 13:45:47', 1, 2, 0, ''),
(2, 'Mr', 'anand', 'gupta', 91, 2424242424, 0, 0, '020', '', 20, '', 20, '', 'meanand.gupta21@gmail.com', '', '', '', '', 'India', 'MAHARASHTRA', 'Mumbai', 400078, 'bhandup', 'Indian', 'sar', 'developer', 1, 4, '', '2019-11-02 13:20:41', '2019-11-04 11:25:30', ' ', '2019-11-04 11:27:55', 1, 2, 0, ''),
(3, 'Mr', 'aniruddh Vishwakarma', 'Vishwakarma', 91, 7021889882, 0, 0, '020', '', 20, '', 20, '', 'aniruddhvishwa@gmail.com', 'aniruddh Vishwakarma aniruddh Vishwakarma', '', '', '', 'India', 'MAHARASHTRA', 'Mumbai', 400078, 400078, 'India', 'SAR', 'deg', '', 0, '', '2019-11-08 21:31:01', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', 1, 2, 0, '');

SET FOREIGN_KEY_CHECKS = 1;
