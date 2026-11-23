-- ========================================================
-- AVO ERP Table Export: `LeadDelegation`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 2
-- Sample Records Exported: 2 (Max Limit: 100)
-- Sort Column: `DelegationId` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `LeadDelegation`;

CREATE TABLE `LeadDelegation` (
  `DelegationId` int(11) NOT NULL AUTO_INCREMENT,
  `LeadId` int(11) NOT NULL,
  `SalesmanId` int(11) NOT NULL,
  `DelegatedTIme` datetime NOT NULL,
  PRIMARY KEY (`DelegationId`),
  KEY `LeadId` (`LeadId`),
  KEY `SalesmanId` (`SalesmanId`),
  CONSTRAINT `LeadDelegation_ibfk_1` FOREIGN KEY (`LeadId`) REFERENCES `Leads_table` (`Lead_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 2 records for table `LeadDelegation`
-- --------------------------------------------------------

INSERT INTO `LeadDelegation` (`DelegationId`, `LeadId`, `SalesmanId`, `DelegatedTIme`) VALUES
(1, 1, 1, '2019-11-02 13:21:21'),
(2, 2, 1, '2019-11-04 11:25:30');

SET FOREIGN_KEY_CHECKS = 1;
