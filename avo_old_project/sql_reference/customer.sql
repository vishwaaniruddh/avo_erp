-- ========================================================
-- AVO ERP Table Export: `customer`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 4
-- Sample Records Exported: 4 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cust_segment` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `branch_name` varchar(50) DEFAULT NULL,
  `contact_type` varchar(50) NOT NULL,
  `designation` varchar(50) NOT NULL,
  `sales_exe_name` varchar(50) NOT NULL,
  `contact_person` varchar(50) NOT NULL,
  `remark` varchar(250) DEFAULT NULL,
  `isWorth` tinyint(1) NOT NULL,
  `product_requirement` varchar(50) NOT NULL,
  `capacity` varchar(50) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `lakh_value` int(11) DEFAULT NULL,
  `finalization_date` varchar(50) DEFAULT NULL,
  `order_chance` varchar(15) DEFAULT NULL,
  `addition_remark` varchar(250) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 4 records for table `customer`
-- --------------------------------------------------------

INSERT INTO `customer` (`id`, `cust_segment`, `name`, `branch_name`, `contact_type`, `designation`, `sales_exe_name`, `contact_person`, `remark`, `isWorth`, `product_requirement`, `capacity`, `quantity`, `lakh_value`, `finalization_date`, `order_chance`, `addition_remark`, `created_by`, `updated_by`, `status`, `created_at`) VALUES
(36, 'Govt', 'Vishal Yadav', 'malad', 'Mail', 'android developer', 'Aniruddh', 'aniruddh vishwakarma', 'remark', 0, 'Product Requirement', 'mumbai', 2, 3, '2/1/2019', 'Low', 'Additional Remark', 'me', 1, 1, '2019-12-18 08:27:56'),
(37, 'Infra', 'aniruddh vishwakarma', 'malad', 'Phone', 'IOS DEVELOPER', 'Aniruddh', 'aniruddh vishwakarma', 'remark', 1, 'Product Requirement', 'mumbai', 2, 3, '2/1/2019', 'Medium', 'Additional Remark', 1, 1, 1, '2019-12-17 11:22:29'),
(38, 'EPS', 'Akash', 'Branch', '', 'IOS', 'Aniruddh', 'Sandeep Vishwakatmma', 'remark', 1, 'Product Requirement', 'mumbai', 2, 1, '2/1/2019', 'Medium', 'Additional Remark', 1, 1, 1, '2019-12-23 06:40:51'),
(39, 'EPS', 'Rahul Test', 'AVOMumbai', '', 'Branch head', 'Vishal', 'Rahul Test', '', 1, 'Inverter', '15 KVA', 15, 30, '01-01-2020', 'High', 'Priority Customer', 1, 1, 1, '2019-12-23 06:54:47');

SET FOREIGN_KEY_CHECKS = 1;
