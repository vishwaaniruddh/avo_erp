-- ========================================================
-- AVO ERP Table Export: `CertificateBenefits`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `CertificateBenefits_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `CertificateBenefits`;

CREATE TABLE `CertificateBenefits` (
  `CertificateBenefits_id` int(11) NOT NULL AUTO_INCREMENT,
  `Program_ID` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `CertificateBenefits` varchar(256) NOT NULL,
  PRIMARY KEY (`CertificateBenefits_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `CertificateBenefits`
-- --------------------------------------------------------

INSERT INTO `CertificateBenefits` (`CertificateBenefits_id`, `Program_ID`, `level_id`, `CertificateBenefits`) VALUES
(1, 2, 1, 'asas');

SET FOREIGN_KEY_CHECKS = 1;
