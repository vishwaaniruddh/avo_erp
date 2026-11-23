-- ========================================================
-- AVO ERP Table Export: `Members`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 1
-- Sample Records Exported: 1 (Max Limit: 100)
-- Sort Column: `mem_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `Members`;

CREATE TABLE `Members` (
  `mem_id` int(11) NOT NULL AUTO_INCREMENT,
  `GenerateMember_Id` varchar(40) NOT NULL,
  `Static_LeadID` int(11) NOT NULL,
  `Primary_Title` varchar(15) NOT NULL,
  `Primary_Pincode` int(11) NOT NULL,
  `Primary_mcode2` int(11) NOT NULL,
  `Primary_mob2` int(11) NOT NULL,
  `Primary_Contact1code` int(11) NOT NULL,
  `Primary_Contact1` int(11) NOT NULL,
  `Primary_Contact2code` int(11) NOT NULL,
  `Primary_Contact2` int(11) NOT NULL,
  `Primary_Contact3code` int(11) NOT NULL,
  `Primary_Contact3` int(11) NOT NULL,
  `Primary_nameOnTheCard` varchar(40) NOT NULL,
  `Primary_PhotoUpload` varchar(100) NOT NULL,
  `Primary_Email_ID2` varchar(30) NOT NULL,
  `Primary_DateOfBirth` date NOT NULL,
  `Primary_Anniversary` date NOT NULL,
  `Primary_AddressType1` varchar(30) NOT NULL,
  `Primary_BuldNo_addrss` varchar(100) NOT NULL,
  `Primary_Area_addrss` varchar(100) NOT NULL,
  `Primary_Landmark_addrss` varchar(100) NOT NULL,
  `Primary_MaritalStatus` varchar(30) NOT NULL,
  `Spouse_Title` varchar(10) NOT NULL,
  `Spouse_FirstName` varchar(50) NOT NULL,
  `Spouse_LastName` varchar(50) NOT NULL,
  `Spouse_GmailMArrid1` varchar(30) NOT NULL,
  `Spouse_GmailMArrid2` varchar(30) NOT NULL,
  `Spouse_PhotoUpload` varchar(30) NOT NULL,
  `Spouse_mcode1Married1` int(11) NOT NULL,
  `Spouse_mob1MArid1` varchar(11) NOT NULL,
  `Spouse_mcode1Married2` int(11) NOT NULL,
  `Spouse_mob1MArid2` int(11) NOT NULL,
  `Spouse_Contact1codeMArid` int(11) NOT NULL,
  `Spouse_Contact1Married` int(11) NOT NULL,
  `Spouse_Contact2codeMArid` int(11) NOT NULL,
  `Spouse_Contact2Married` int(11) NOT NULL,
  `Spouse_DateOfBirth` date NOT NULL,
  `Spouse_nameOnTheCardMarried` varchar(200) NOT NULL,
  `MembershipDetails_Level` varchar(50) NOT NULL,
  `MembershipDetails_Fee` int(11) NOT NULL,
  `MembershipDetails_offerCheck1` int(11) NOT NULL,
  `MembershipDts_Discount` int(11) NOT NULL,
  `MembershipDts_Author` varchar(100) NOT NULL,
  `MembershipDts_NetPayment` int(11) NOT NULL,
  `MembershipDts_GST` int(11) NOT NULL,
  `MembershipDts_GrossTotal` int(11) NOT NULL,
  `MembershipDts_PaymentDate` date NOT NULL,
  `MembershipDts_PaymentMode` varchar(40) NOT NULL,
  `MembershipDts_InstrumentNumber` varchar(50) NOT NULL,
  `Member_bankName` varchar(100) NOT NULL,
  `MemshipDts_UploadCopyOfTheInstmnt` varchar(40) NOT NULL,
  `MemshipDts_BatchNumber` varchar(30) NOT NULL,
  `MemshipDts_Remarks` varchar(250) NOT NULL,
  `Documentation_UploadSignatures` varchar(50) NOT NULL,
  `Documentation_AddressProof` varchar(50) NOT NULL,
  `Relationships_ReferredByLEADID` varchar(20) NOT NULL,
  `Relationships_ReferredByMEMBERSHIPID` varchar(50) NOT NULL,
  `itemCheck1` int(11) NOT NULL,
  `BookletCheck1` int(11) NOT NULL,
  `CertificatesCheck1` int(11) NOT NULL,
  `PromotionalCheck1` int(11) NOT NULL,
  `Issue_ReferredByLEADID` varchar(40) NOT NULL,
  `Issue_ReferredByMEMBERSHIPID` varchar(40) NOT NULL,
  `booklet_Series` int(11) NOT NULL,
  `entryDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `dispatched_status` int(11) NOT NULL COMMENT '0 means not dispatch, 1- means Dispatched',
  `Complemetory_card_Free` int(11) NOT NULL COMMENT '1 - means complementoryFreeCard',
  `canceledMember` int(11) NOT NULL COMMENT '1 means Cancel Member',
  `MemberCancelationReason` varchar(250) NOT NULL,
  `CancelationDate` datetime NOT NULL,
  `Sample` int(11) NOT NULL COMMENT '1 means Sample Pack',
  `receipt_no` int(11) NOT NULL,
  PRIMARY KEY (`mem_id`),
  UNIQUE KEY `GenerateMember_Id` (`GenerateMember_Id`),
  UNIQUE KEY `Static_LeadID` (`Static_LeadID`),
  KEY `Primary_nameOnTheCard` (`Primary_nameOnTheCard`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 1 records for table `Members`
-- --------------------------------------------------------

INSERT INTO `Members` (`mem_id`, `GenerateMember_Id`, `Static_LeadID`, `Primary_Title`, `Primary_Pincode`, `Primary_mcode2`, `Primary_mob2`, `Primary_Contact1code`, `Primary_Contact1`, `Primary_Contact2code`, `Primary_Contact2`, `Primary_Contact3code`, `Primary_Contact3`, `Primary_nameOnTheCard`, `Primary_PhotoUpload`, `Primary_Email_ID2`, `Primary_DateOfBirth`, `Primary_Anniversary`, `Primary_AddressType1`, `Primary_BuldNo_addrss`, `Primary_Area_addrss`, `Primary_Landmark_addrss`, `Primary_MaritalStatus`, `Spouse_Title`, `Spouse_FirstName`, `Spouse_LastName`, `Spouse_GmailMArrid1`, `Spouse_GmailMArrid2`, `Spouse_PhotoUpload`, `Spouse_mcode1Married1`, `Spouse_mob1MArid1`, `Spouse_mcode1Married2`, `Spouse_mob1MArid2`, `Spouse_Contact1codeMArid`, `Spouse_Contact1Married`, `Spouse_Contact2codeMArid`, `Spouse_Contact2Married`, `Spouse_DateOfBirth`, `Spouse_nameOnTheCardMarried`, `MembershipDetails_Level`, `MembershipDetails_Fee`, `MembershipDetails_offerCheck1`, `MembershipDts_Discount`, `MembershipDts_Author`, `MembershipDts_NetPayment`, `MembershipDts_GST`, `MembershipDts_GrossTotal`, `MembershipDts_PaymentDate`, `MembershipDts_PaymentMode`, `MembershipDts_InstrumentNumber`, `Member_bankName`, `MemshipDts_UploadCopyOfTheInstmnt`, `MemshipDts_BatchNumber`, `MemshipDts_Remarks`, `Documentation_UploadSignatures`, `Documentation_AddressProof`, `Relationships_ReferredByLEADID`, `Relationships_ReferredByMEMBERSHIPID`, `itemCheck1`, `BookletCheck1`, `CertificatesCheck1`, `PromotionalCheck1`, `Issue_ReferredByLEADID`, `Issue_ReferredByMEMBERSHIPID`, `booklet_Series`, `entryDate`, `dispatched_status`, `Complemetory_card_Free`, `canceledMember`, `MemberCancelationReason`, `CancelationDate`, `Sample`, `receipt_no`) VALUES
(1, 21322351, 2, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', '0000-00-00', '0000-00-00', '', '', '', '', '', '', '', '', '', '', '', 0, '', 0, 0, 0, 0, 0, 0, '0000-00-00', '', 1, 10000, 0, 0, '', 10000, 1800, 11800, '2019-11-08', 'Cash', 12, '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', 0, '2019-11-04 08:02:36', 0, 0, 0, '', '0000-00-00 00:00:00', 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
