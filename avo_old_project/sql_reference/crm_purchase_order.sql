-- ========================================================
-- AVO ERP Table Export: `crm_purchase_order`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `crm_purchase_order`;

CREATE TABLE `crm_purchase_order` (
  `po_no` varchar(50) NOT NULL,
  `no_sites` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `ref_id` varchar(25) NOT NULL,
  `pm` int(11) NOT NULL COMMENT 'Primitive Maintenance',
  `po_date` date NOT NULL,
  `ed` int(11) NOT NULL DEFAULT 0,
  `del_type` varchar(10) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buyeraddress` varchar(150) DEFAULT NULL,
  `gst` varchar(50) DEFAULT NULL,
  `salesperson` varchar(50) DEFAULT NULL,
  `po_OAN_no` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `po_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `po_raiseby` int(11) NOT NULL,
  `po_mode` varchar(25) NOT NULL,
  `po_tat` int(11) NOT NULL,
  `po_payment` varchar(50) NOT NULL,
  `po_remarks` varchar(100) NOT NULL,
  `po_status` int(11) NOT NULL COMMENT '9-delete-new SO',
  `other_charge` varchar(50) NOT NULL,
  `non_standard_item_product` varchar(200) NOT NULL COMMENT 'Other charge details',
  `branch_id` int(11) NOT NULL,
  `is_approve` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_no` (`po_no`),
  KEY `del_type` (`del_type`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `crm_purchase_order`
-- --------------------------------------------------------

INSERT INTO `crm_purchase_order` (`po_no`, `no_sites`, `cust_id`, `ref_id`, `pm`, `po_date`, `ed`, `del_type`, `id`, `buyeraddress`, `gst`, `salesperson`, `po_OAN_no`, `buyer_id`, `po_time`, `po_raiseby`, `po_mode`, `po_tat`, `po_payment`, `po_remarks`, `po_status`, `other_charge`, `non_standard_item_product`, `branch_id`, `is_approve`, `created_by`) VALUES
('PURCHASE!', 0, 8, '', 0, '2020-04-30', 0, 'site_del', 8, '35,  Jasukie Market  G.S. RoadDimapur', '13AAACD3206C1ZM', 41, 0, 69, '2020-05-09 08:08:46', 42, 1, 0, 'Payment Terms:', 'Payment Terms:', 1, 'Other Char', 'Brief on Other charges, if any :', 2, 1, 0);

SET FOREIGN_KEY_CHECKS = 1;
