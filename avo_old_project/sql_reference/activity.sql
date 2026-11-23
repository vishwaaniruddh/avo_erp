-- ========================================================
-- AVO ERP Table Export: `activity`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity`;

CREATE TABLE `activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `act_name` varchar(50) DEFAULT NULL,
  `act_branch_name` varchar(50) DEFAULT NULL,
  `act_cust_type` varchar(50) DEFAULT NULL,
  `act_city` varchar(50) DEFAULT NULL,
  `act_area` varchar(200) DEFAULT NULL,
  `act_address` varchar(200) DEFAULT NULL,
  `act_state` varchar(50) DEFAULT NULL,
  `act_pincode` varchar(50) DEFAULT NULL,
  `act_call_type` varchar(50) DEFAULT NULL,
  `act_con_person` varchar(50) DEFAULT NULL,
  `act_mobile` bigint(20) DEFAULT NULL,
  `act_remarks` varchar(50) DEFAULT NULL,
  `act_status` varchar(50) DEFAULT NULL,
  `act_other` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `activity`
-- --------------------------------------------------------

INSERT INTO `activity` (`id`, `act_name`, `act_branch_name`, `act_cust_type`, `act_city`, `act_area`, `act_address`, `act_state`, `act_pincode`, `act_call_type`, `act_con_person`, `act_mobile`, `act_remarks`, `act_status`, `act_other`, `status`, `created_by`, `created_at`) VALUES
(8, 'BHS', 'AVOMumbai', 'Salesman', 'mumbai', 'Govandi', 'Mulund, MumbaiMulund, MumbaiMulund, MumbaiMulund, MumbaiMulund, MumbaiMulund, Mumbai', 'Maharashtra', 400078, 'Invoice Submission', 'Salman Khan', 7021889882, 'Boss', 'complete', '', 1, 82, '2020-05-20 12:19:37');

SET FOREIGN_KEY_CHECKS = 1;
