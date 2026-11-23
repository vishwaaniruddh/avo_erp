-- ========================================================
-- AVO ERP Table Export: `crm_po_assets`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 2
-- Sample Records Exported: 2 (Max Limit: 100)
-- Sort Column: `assettrack_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `crm_po_assets`;

CREATE TABLE `crm_po_assets` (
  `po_no` varchar(50) NOT NULL,
  `assets_name` varchar(50) NOT NULL,
  `specs` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `warranty` varchar(50) NOT NULL,
  `rate` int(11) NOT NULL,
  `po_trackid` int(11) NOT NULL,
  `assettrack_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`assettrack_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 2 records for table `crm_po_assets`
-- --------------------------------------------------------

INSERT INTO `crm_po_assets` (`po_no`, `assets_name`, `specs`, `qty`, `warranty`, `rate`, `po_trackid`, `assettrack_id`) VALUES
('PURCHASE!', 'Stabilizer', 46, 70, 12, 500, 8, 5),
('PURCHASE!', 'Stabilizer', 17, 70, 24, 500, 8, 6);

SET FOREIGN_KEY_CHECKS = 1;
