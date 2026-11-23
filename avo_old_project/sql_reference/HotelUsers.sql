-- ========================================================
-- AVO ERP Table Export: `HotelUsers`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `HotelUsers`;

CREATE TABLE `HotelUsers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hotelname` varchar(30) NOT NULL,
  `Brand` varchar(100) NOT NULL,
  `empname` varchar(30) NOT NULL,
  `empno` int(11) NOT NULL,
  `emailid` varchar(50) NOT NULL,
  `mobile` bigint(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `verified` char(1) NOT NULL DEFAULT 'N',
  `Department` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emailid` (`emailid`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `HotelUsers`
-- --------------------------------------------------------

INSERT INTO `HotelUsers` (`id`, `hotelname`, `Brand`, `empname`, `empno`, `emailid`, `mobile`, `password`, `verified`, `Department`) VALUES
(1, 'Shyambabadham', '', 'gupta', 1, 'meanand.gupta21@gmail.com', 9987856464, 632068, 'Y', 'software');

SET FOREIGN_KEY_CHECKS = 1;
