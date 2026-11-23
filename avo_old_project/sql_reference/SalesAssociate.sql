-- ========================================================
-- AVO ERP Table Export: `SalesAssociate`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `SalesmanId` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `SalesAssociate`;

CREATE TABLE `SalesAssociate` (
  `SalesmanId` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Designation` varchar(50) NOT NULL,
  `UserLevel` varchar(100) NOT NULL,
  `Address` varchar(250) NOT NULL,
  `Country` varchar(50) NOT NULL,
  `State` varchar(50) NOT NULL,
  `City` varchar(50) NOT NULL,
  `Pincode` int(11) NOT NULL,
  `ContactNo` int(11) NOT NULL,
  `Location` varchar(100) NOT NULL,
  `Company` varchar(500) NOT NULL,
  `Add` varchar(250) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`SalesmanId`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `SalesAssociate`
-- --------------------------------------------------------

INSERT INTO `SalesAssociate` (`SalesmanId`, `FirstName`, `LastName`, `Designation`, `UserLevel`, `Address`, `Country`, `State`, `City`, `Pincode`, `ContactNo`, `Location`, `Company`, `Add`, `entry_date`) VALUES
(1, 'Subhasis', 'Das', 'Marketing', 'NA', 'SBD', '', 1, '', 400064, 2147483647, 'Mumbai', 'SBDD', '', '2019-11-01 14:35:28');

SET FOREIGN_KEY_CHECKS = 1;
