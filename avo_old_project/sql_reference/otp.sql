-- ========================================================
-- AVO ERP Table Export: `otp`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: Default Natural Order
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `otp`;

CREATE TABLE `otp` (
  `mobile` bigint(20) NOT NULL,
  `otpvalue` int(11) NOT NULL,
  `gtime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `otp`
-- --------------------------------------------------------

INSERT INTO `otp` (`mobile`, `otpvalue`, `gtime`) VALUES
(9987856464, 9294, '2019-11-04 12:13:12');

SET FOREIGN_KEY_CHECKS = 1;
