-- ========================================================
-- AVO ERP Table Export: `state`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 36
-- Sample Records Exported: 36 (Max Limit: 100)
-- Sort Column: `state_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `state`;

CREATE TABLE `state` (
  `state_id` int(11) NOT NULL AUTO_INCREMENT,
  `state` varchar(50) NOT NULL,
  `country_id` int(11) NOT NULL,
  PRIMARY KEY (`state_id`),
  UNIQUE KEY `state` (`state`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 36 records for table `state`
-- --------------------------------------------------------

INSERT INTO `state` (`state_id`, `state`, `country_id`) VALUES
(1, 'Maharashtra', 1),
(2, 'Andaman and Nicobar Islands', 1),
(3, 'Andhra Pradesh', 1),
(4, 'Arunachal Pradesh', 1),
(5, 'Assam', 1),
(6, 'Bihar', 1),
(7, 'Chhattisgarh', 1),
(8, 'Dadra and Nagar Haveli', 1),
(9, 'Daman and Diu', 1),
(10, 'Delhi', 1),
(11, 'Goa', 1),
(12, 'Gujarat', 1),
(13, 'Chandigarh', 1),
(14, 'Haryana', 1),
(15, 'Himachal Pradesh', 1),
(17, 'Jammu and Kashmir', 1),
(18, 'Jharkhand', 1),
(19, 'Karnataka', 1),
(20, 'Kerala', 1),
(21, 'Lakshadweep', 1),
(22, 'Madhya Pradesh', 1),
(23, 'Manipur', 1),
(24, 'Meghalaya', 1),
(25, 'Mizoram', 1),
(26, 'Nagaland', 1),
(27, 'Orissa', 1),
(28, 'Pondicherry', 1),
(29, 'Punjab', 1),
(30, 'Rajasthan', 1),
(31, 'Sikkim', 1),
(32, 'West Bengal', 1),
(33, 'Tamil Nadu', 1),
(34, 'Tripura', 1),
(35, 'Uttar Pradesh', 1),
(36, 'Uttarakhand', 1),
(37, 'TELANGANA', 1);

SET FOREIGN_KEY_CHECKS = 1;
