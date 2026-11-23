-- ========================================================
-- AVO ERP Table Export: `enquiry`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 17
-- Sample Records Exported: 17 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `enquiry`;

CREATE TABLE `enquiry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enquiry_by` int(11) NOT NULL,
  `name` varchar(60) DEFAULT NULL,
  `mobile` bigint(20) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `status` varchar(60) DEFAULT NULL,
  `remark` varchar(200) DEFAULT NULL,
  `enquiry_type` varchar(50) DEFAULT NULL,
  `customer_vertical` varchar(20) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 17 records for table `enquiry`
-- --------------------------------------------------------

INSERT INTO `enquiry` (`id`, `enquiry_by`, `name`, `mobile`, `email`, `city`, `state`, `address`, `status`, `remark`, `enquiry_type`, `customer_vertical`, `created_at`) VALUES
(1, 62, 'Jatin', 9970215757, 'jatin@panchal', 'Ahemdabad', 2, 'near kholpi stan. 602', 'Close', 'some remark', 'Close', 'Close', '2020-04-22'),
(2, 62, 'Jatin', 9970215757, 'jatin@panchal', 'Ahemdabad', 2, 'near kholpi stan. 602', 'Pending', 'some remark', 'Close', 1, '2020-04-22'),
(3, 62, 'Jatin', 9970215757, 'jatin@panchal', 'Ahemdabad', 2, 'near kholpi stan. 602', 'Reject', 'some remark', 'Close', 1, '2020-04-22'),
(7, 41, 'Jatin', 9970215757, 'jatin@panchal', 'Ahemdabad', 2, 'near kholpi stan. 602', 'Pending', 'some remark', 'Pending', '', '2020-04-23'),
(9, 62, 'THousand miles website', 8087033099, 'thousandmilesweb@gmail.com', 'Mumbai', 1, 'k 1501 New PMGP colony  Mulund East', 'Pending', 'some', 'Pending', 1, '2020-04-27'),
(29, 0, '', 0, '', '', 'Maharashtra', '', 'Pending', '', 'Pending', 'Tata', '2020-05-08'),
(30, 0, 'test', 9999999999, 'abc@g.b', 'Mumbai', 'Maharashtra', 'dd', 'Pending', 'test', 'Pending', 'EPS', '2020-05-08'),
(31, 0, 'Test', 8879265547, 'kvaljani@gmail.com', 'Mumbai', 'Maharashtra', 'Addinath', 'Pending', 'Test', 'Pending', 'Tata', '2020-05-08'),
(32, 77, 'STATE BANK OF INDIA ATM  SING000', 9247460057, 'ramanamurthy.dsv@sbi.co.in', 'GUDIVADA', 'Andhra Pradesh', 'state bank of india vendrpagada krishana dist', 'Pending', '100 ah quanta 8 nos batterys new', 'Pending', 'EPS', '2020-05-11'),
(33, 77, 'STATE BANK OF INDIA ATM  SING000', 9247460057, 'ramanamurthy.dsv@sbi.co.in', 'GUDIVADA', 'Andhra Pradesh', 'state bank of india vendrpagada krishana dist', 'Pending', '100 ah quanta 8 nos batterys new', 'Pending', 'EPS', '2020-05-11'),
(34, 77, 'STATE BANK OF INDIA ATM  SING000', 9247460057, 'ramanamurthy.dsv@sbi.co.in', 'GUDIVADA', 'Andhra Pradesh', 'state bank of india vendrpagada krishana dist', 'Pending', '100 ah quanta 8 nos batterys new', 'Pending', 'EPS', '2020-05-11'),
(35, 77, 'STATE BANK OF INDIA ATM  SING000', 9247460057, 'ramanamurthy.dsv@sbi.co.in', 'GUDIVADA', 'Andhra Pradesh', 'state bank of india vendrpagada krishana dist', 'Pending', '100 ah quanta 8 nos batterys new', 'Pending', 'EPS', '2020-05-11'),
(36, 53, 'foredort system', 9940626997, 'j.radhakrishnanavoups@gmail.com', 'chennai', 'Tamil Nadu', '', 'Pending', '', 'Pending', 'Mphasis', '2020-05-14'),
(37, 1, 'XYZ', 6290078781, 'xyzac@gmail.com', 'kolkata', 'West Bengal', 'Garcia station road kol-107', 'Pending', 'excellent', 'Pending', 'Mphasis', '2020-05-16'),
(38, 77, 'rm power control s', 9553121777, 'rmpowercontrolsvja@gmail.con', 'Vijayawada', 'Andhra Pradesh', 'Bhavani puram', 'Close', '6 kva 192 dvc ups oder confrom', 'Close', 'EPS', '2020-05-18'),
(39, 77, 'rm power control s', 9553121777, 'rmpowercontrolsvja@gmail.con', 'Vijayawada', 'Andhra Pradesh', 'Bhavani puram', 'Close', '6 kva 192 dvc ups oder confrom', 'Close', 'EPS', '2020-05-18'),
(40, 57, 'INDIAN BANK', 9700061387, 'Venkat@avoups.com', 'Vijayawada', 'Andhra Pradesh', 'Vijayawada', 'Pending', 'New po', 'Pending', 'Mphasis', '2020-06-17');

SET FOREIGN_KEY_CHECKS = 1;
