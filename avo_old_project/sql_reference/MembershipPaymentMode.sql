-- ========================================================
-- AVO ERP Table Export: `MembershipPaymentMode`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 4
-- Sample Records Exported: 4 (Max Limit: 100)
-- Sort Column: `paymode_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `MembershipPaymentMode`;

CREATE TABLE `MembershipPaymentMode` (
  `paymode_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `Payment_mode` varchar(200) NOT NULL,
  PRIMARY KEY (`paymode_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 4 records for table `MembershipPaymentMode`
-- --------------------------------------------------------

INSERT INTO `MembershipPaymentMode` (`paymode_id`, `Program_ID`, `Payment_mode`) VALUES
(1, 2, 'Cash'),
(3, 2, 'Instamojo'),
(4, 1, 'Paytm'),
(5, 2, 'online');

SET FOREIGN_KEY_CHECKS = 1;
