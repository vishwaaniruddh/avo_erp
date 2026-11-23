-- ========================================================
-- AVO ERP Table Export: `Users`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 2
-- Sample Records Exported: 2 (Max Limit: 100)
-- Sort Column: `UserId` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Users`;

CREATE TABLE `Users` (
  `UserId` int(11) NOT NULL AUTO_INCREMENT,
  `UserName` varchar(200) NOT NULL,
  `Password` varchar(200) NOT NULL,
  `UserType` varchar(200) NOT NULL,
  `permission` varchar(250) NOT NULL,
  `roll_id` int(11) NOT NULL,
  `reg_id` int(11) NOT NULL,
  `Brand_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `Active` int(11) NOT NULL COMMENT '1 means Active ,0 means Inactive',
  PRIMARY KEY (`UserId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 2 records for table `Users`
-- --------------------------------------------------------

INSERT INTO `Users` (`UserId`, `UserName`, `Password`, `UserType`, `permission`, `roll_id`, `reg_id`, `Brand_id`, `hotel_id`, `Active`) VALUES
(1, 'Admin', 123456, 'Admin', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67', 1, 1, 0, 0, 1),
(2, 'das', 'das@123', 'Sales Associate', '46,47,49,50', 2, 1, 0, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
