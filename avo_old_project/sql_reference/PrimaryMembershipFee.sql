-- ========================================================
-- AVO ERP Table Export: `PrimaryMembershipFee`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 3
-- Sample Records Exported: 3 (Max Limit: 100)
-- Sort Column: `MembershipFee_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `PrimaryMembershipFee`;

CREATE TABLE `PrimaryMembershipFee` (
  `MembershipFee_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_id` int(11) NOT NULL,
  `P_Level_id` int(11) NOT NULL,
  `NewMembership` varchar(250) NOT NULL,
  `RenewalMembership` varchar(250) NOT NULL,
  `GST` varchar(250) NOT NULL,
  PRIMARY KEY (`MembershipFee_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 3 records for table `PrimaryMembershipFee`
-- --------------------------------------------------------

INSERT INTO `PrimaryMembershipFee` (`MembershipFee_id`, `Program_id`, `P_Level_id`, `NewMembership`, `RenewalMembership`, `GST`) VALUES
(1, 1, 1, 10000, 0, 3),
(2, 1, 2, 20000, 0, 3),
(3, 1, 3, 30000, 0, 3);

SET FOREIGN_KEY_CHECKS = 1;
