-- ========================================================
-- AVO ERP Table Export: `customer_contact`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 3
-- Sample Records Exported: 3 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `customer_contact`;

CREATE TABLE `customer_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `contact_type` varchar(50) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` bigint(20) NOT NULL,
  `landline` bigint(20) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `pincode` varchar(50) DEFAULT NULL,
  `area` varchar(50) DEFAULT NULL,
  `address` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 3 records for table `customer_contact`
-- --------------------------------------------------------

INSERT INTO `customer_contact` (`id`, `customer_id`, `contact_type`, `email`, `mobile`, `landline`, `city`, `state`, `pincode`, `area`, `address`) VALUES
(5, 36, 'Mail', 'vishalYadav@gmail.com', 7021889883, 7021889883, 'mumbai', 'Maharashtra', 400078, 'Govandi', 'Shyam Baba Dham, Village - Chanana, District - Jhunjhunu, Rajasthan - 333026'),
(6, 37, 'Phone', 'aniruddhvishwa@gmail.com', 7021889883, 7021889883, 'mumbai', 'Maharashtra', 400078, 'Govandi', 'Shyam Baba Dham, Village - Chanana, District - Jhunjhunu, Rajasthan - 333026'),
(7, 39, '', 'rahulpurandare@sarmicrosystems.in', 9324576504, 2298989898, 'Mumbai', 'Maharashtra', 400064, 'Malad', '8/64 Sainath Road, Near Lifeline Hospital');

SET FOREIGN_KEY_CHECKS = 1;
