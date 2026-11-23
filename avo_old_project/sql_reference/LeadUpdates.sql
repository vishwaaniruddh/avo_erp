-- ========================================================
-- AVO ERP Table Export: `LeadUpdates`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 4
-- Sample Records Exported: 4 (Max Limit: 100)
-- Sort Column: `UpdateId` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `LeadUpdates`;

CREATE TABLE `LeadUpdates` (
  `UpdateId` int(11) NOT NULL AUTO_INCREMENT,
  `LeadId` int(11) NOT NULL,
  `Comments` varchar(250) NOT NULL,
  `UpdateTime` datetime NOT NULL,
  `NextUpdate` datetime NOT NULL,
  PRIMARY KEY (`UpdateId`),
  KEY `LeadId` (`LeadId`),
  CONSTRAINT `LeadUpdates_ibfk_1` FOREIGN KEY (`LeadId`) REFERENCES `Leads_table` (`Lead_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 4 records for table `LeadUpdates`
-- --------------------------------------------------------

INSERT INTO `LeadUpdates` (`UpdateId`, `LeadId`, `Comments`, `UpdateTime`, `NextUpdate`) VALUES
(1, 1, ' test', '2019-11-02 13:43:57', '2019-11-01 00:00:00'),
(2, 1, ' busy', '2019-11-02 13:45:16', '2019-11-02 00:00:00'),
(3, 1, ' ready', '2019-11-02 13:45:47', '1970-01-01 00:00:00'),
(4, 2, ' ready', '2019-11-04 11:27:55', '1970-01-01 00:00:00');

SET FOREIGN_KEY_CHECKS = 1;
