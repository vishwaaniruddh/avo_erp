-- ========================================================
-- AVO ERP Table Export: `customer_vertical`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 83
-- Sample Records Exported: 83 (Max Limit: 100)
-- Sort Column: `cust_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `customer_vertical`;

CREATE TABLE `customer_vertical` (
  `cust_id` int(11) NOT NULL AUTO_INCREMENT,
  `cust_name` varchar(50) NOT NULL,
  `email_id` varchar(50) NOT NULL,
  `phone_no1` varchar(12) NOT NULL,
  `phone_no2` varchar(12) DEFAULT NULL,
  `conpername` varchar(200) NOT NULL COMMENT 'contact person name',
  `contactno` varchar(11) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(200) NOT NULL,
  `state` varchar(200) NOT NULL,
  `pincode` int(11) NOT NULL,
  PRIMARY KEY (`cust_id`)
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 83 records for table `customer_vertical`
-- --------------------------------------------------------

INSERT INTO `customer_vertical` (`cust_id`, `cust_name`, `email_id`, `phone_no1`, `phone_no2`, `conpername`, `contactno`, `address`, `city`, `state`, `pincode`) VALUES
(3, 'Tata', '', '', NULL, '', '', '', '', '', 0),
(4, 'Euronet', '', '', NULL, '', '', '', '', '', 0),
(5, 'Prizm', '', '', NULL, '', '', '', '', '', 0),
(6, 'AGS', '', '', NULL, '', '', '', '', '', 0),
(7, 'FIS', '', '', NULL, '', '', '', '', '', 0),
(8, 'Diebold', '', '', NULL, '', '', '', '', '', 0),
(9, 'NCR', '', '', NULL, '', '', '', '', '', 0),
(10, 'HPCL', '', '', NULL, '', '', '', '', '', 0),
(11, 'EPS', 'bchhetri@avoups.com', '012345', 919022035552, 'Bikram', '09699931133', 'Bhandup', 'Mumbai', 'Maharashtra', 400078),
(12, 'Mphasis', 'jigar.intl@gmail.com', '02040740000', '02040740000', 'Santosh Swain', '090000000', 'Pune', 'Pue', 'Maharashtra', 400078),
(13, 'WB Allahabad Direct', 'jayanta.ghorui@avoups.com', '032017233', '', 'Jayanto', '09331665338', 'Kolkata', 'Kolkata', 'West Bengal', 700038),
(14, 'PNB Agra Circle', 'atmagr@pnb.co.in', '05624033790', '', 'Mr. Gaurav Vij', '05624033790', 'Agra Circle Office', 'Agra', 'Uttar Pradesh', 110110),
(15, 'CMS', 'Mridul.sarma@csl.cms.com', '09707826720', '', 'Mridul Sarma ', '09707826720', 'CMS Info Systems Pvt Ltd   |  House No -20, Gift Land,\r\nAnanda Nagar, By LAne - 5, Dispur Old Post Office, Guwahati ', 'Guwahati ', 'Assam', 781005),
(16, 'Abhijeet Direct', 'abhijit@avoups.com', '09322991412', '', 9322991412, '09967338796', 'Dreams Mall, Bhandup(w)', 'Mumbai', 'MAHARASHTRA', 400078),
(17, 'WB-Local AMC', 'hdhar@avoups.com', '09830262223', '', 'West Benagal AMC team', '09830715092', 'AVO', 'Kolkatta', 'West Bengal', 700041),
(18, 'WB- Under Warranty', 'hdhar@avoups.com', '09830262223', '', 'Hirak', '09830715092', 'Avo', 'Kolkatta', 'West Bengal', 700041),
(19, 'All Branch Local Calls', 'service3@avoups.com', '01234567890', '', 'Admin', '01234', 'Kolkatta', 'Kolkatta', 'All States', 300001),
(20, 'State Bank of India', 'service3@avoups.com', '011', '0112', 'Service3', '01111111111', 'India', 'Mumbai', 'maharashtra', 200001),
(21, 'State Bank of Hyderabad', 'service3@avoups.com', '040', '040', 'Prasun', '040', 'Hyderabad', 'Hyderabad', 'Andhra Pradesh', 500001),
(22, 'Aditya Birla Money Ltd', 'abm.care@adityabirla.com', '04439130002', '', 'Suryaprakash', '04439140460', 'Infrastructure & Administration| Aditya Birla Money Limited\r\nAli Centre| No.53 Greams Road | Chennai 600 006', 'Chennai ', 'TN', 600006),
(23, 'FSS', 'satyanarayanans@fss.co.in', '02242415000', '02242415000', 'Satyanarayana', '02242415000', 'Vikhroli', 'Mumbai', 'maharashtra', 200001),
(24, 'Mubarak Direct', 'mubarak@avoups.com', '09421086657', '09987807733', 'Mubarak Baig', '09421086657', 'Switching AVO Electro Power Limited\r\nB102, Patel pride,Sadatnagar\r\nAurangabad - 431001', 'Aurangabad', 'Maharashtra', 431001),
(25, 'NCR_Ravi', 'ravindra.shetty@avoups.com', '08451848869', '09987807733', 'Ravi shetty', '08451848869', 'WITCHING AVO ELECTRO POWER LIMITED\r\nUG - 200/201, Dreams The Mall, Station Road,\r\nBhandup (W), Mumbai ', 'Mumbai', 'Maharashtra', 400078),
(26, 'CSS', 'pallavid@cssindia.in', '07710016155', '', 'Pallavi Dharmendar', '07710016155', 'Mumbai', 'Mumbai', 'Maharashtra', 1),
(27, 'Axis Bank', 'tnaskar@avoups.com', '09038015823', 9674444055, 'Naskar', '09674444055', 'Mumbai', 'Mumbai', 'maharashtra', 400001),
(28, 'Atos', 'tushar.satdeve@atos.net', '09920785104', '', 'Arun', '09920785104', 'C/o. SAFEXPRESS\r\nPvt.Ltd. NH.Highway NO.3, Sonale Village Walshind Lonad, Opp Presidency\r\n421302 HIGHSCHOOL. THANE-BHIWANDI', 'Mumbai', 'Maharashtra', 421302),
(29, 'IDBI', 'asha@avoups.com', '08898002888', '', ' Asha Belgaonkar', '08898002888', '508 - A Wing, Merchant Centre, Sector - 19, Vashi, Navi Mumbai - 400703', 'Mumbai', 'Maharashtra', 400703),
(30, 'Hitachi', 'ezra.r@avoups.com', '09884833243', 9884833243, 'ezra', '09884833243', 'No. 303, 3rd Floor, Silver Metropolis, Jai Coach Compound, Off Western Express Highway Goregaon East Maharashtra ', 'Goregaon East', 'Mumbai', 400063),
(31, 'Amit Direct', 'asha@avoups.com', '08898002888', '', 8898002888, '08898002888', 'Mumbai', 'NAVI MUMBAI', 'Maharashtra', 400078),
(32, 'FIS-With Batt AMC', 'boopathy@avoups.com', '09551086665', '', 'Kiran', '09551086665', 'FIS Mumbai', 'Mumbai', 'Maharashtra', 400001),
(33, 'MAHINDRA', '', '', NULL, '', '', '', '', '', 0),
(34, 'UBI', '', '', NULL, '', '', '', '', '', 0),
(35, 'Writer', '', '', NULL, '', '', '', 'mumbai', 'maharashtra', 0),
(36, 'CMS-IT Services', 'pinki.mondal@avoups.com', '09331393323', '09331393323', 'Presenjit', '01234567890', 'West Bengal', 'Kolkatta', 'West Bengal', 7000001),
(37, 'Syndicate Bank', 'syedp@avoups.com', '07026619991', '', 'Syed Pasha', '07026619991', 'Bangalore', 'Bangalore', 'Karnataka', 560001),
(38, 'FSS-Solar', 'boopathy@avoups.com', '09551086665', 9551086665, 'Vinod', '09551086665', 'Mumbai', 'Mumbai', 'Maharashtra', 400001),
(39, 'TCS', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'West Bengal', 700001),
(40, 'ILFS', 'prabhu.avo@gmail.com', '09903387113', '', 'Mr. Prabhu', '09903387113', 'delhi', 'delhi', 'delhi', 110110),
(41, 'Edique', 'boopathy@avoups.com', '09551086665', '', 'Udyan', '09551086665', 'Ranchi', 'Ranchi', 'Jharkhand', 800001),
(42, 'SI- Vertical others', 'boopathy@avoups.com', '09551086665', '', 'Udayan', '09551086665', 'Ranchi', 'Ranchi', 'Jharkhand', 800001),
(43, 'Vijaya Bank', 'boopathy@avoups.com', '09551086665', '', 'Boopathy', '09551086665', 'Bangalore', 'Bangalore', 'Karnataka', 560001),
(44, 'Aryavart Bank-UP', 'boopathy@avoups.com', '09551086665', '09551086665', 'Ravish', '09551086665', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 700001),
(45, 'UIIC', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Royapet', 'Chennai', 'Tamil nadu', 600001),
(46, 'Oriental Bank of Commerce', 'boopathy@avoups.com', '09551086665', '', 'Shankar Sharma', '09551086665', 'Delhi', 'Delhi', 'Delhi', 247667),
(47, 'Edutel', 'skmarthad@edutelmail.in', '07026619831', '', 'Shesha', '07026619831', 'Bangalore', 'Bangalore', 'Karnataka', 560001),
(48, 'Channel Sales', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chennai', 'Chennai', 'Tamil nadu', 600001),
(49, 'Stock Transfer', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chennai', 'Chennai', 'Tamil nadu', 600001),
(50, 'L&T - Wi-Fi', 'boopathy@avoups.com', '09551086665', '', 'Boopathy', '09551086665', 'Patna', 'Patna', 'Bihar', 800001),
(51, 'Corporation Bank', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(52, 'Banks-Public', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001);

INSERT INTO `customer_vertical` (`cust_id`, `cust_name`, `email_id`, `phone_no1`, `phone_no2`, `conpername`, `contactno`, `address`, `city`, `state`, `pincode`) VALUES
(53, 'Banks-Private', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(54, 'Insurance-Govt', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(55, 'Insurance-Private', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(56, 'Goverments', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(57, 'Health_Care', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(58, 'Educational', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(59, 'Industries', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(60, 'Financial_Inst', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(61, 'BTI Payments', 'boopathy@avoups.com', '09551086665', '09551086665', 'Farid', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(62, 'Numax', 'boopathy@avoups.com', '09551086665', '09551086665', 'Boopathy', '09551086665', 'Hyderabad', 'Hyderabad', 'AP', 500001),
(63, 'VKGB', 'boopathy@avoups.com', '09551086665', 9551086665, 'Mubarak', '09551086665', 'Kolkatta', 'Maharashtra', 'Maharashtra', 200001),
(64, 'BPCL', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chennai', 'Chennai', 'Tamil Nadu', 600001),
(65, 'Allahabad Bank-UP', 'boopathy@avoups.com', '09551086665', '', 'Ravish', '09551086665', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 300001),
(66, 'AUGB-UP', 'boopathy@avoups.com', '09551086665', '', 'Ravish', '09551086665', 'Lucknow', 'Lucknow', 'Uttar Pradesh', 300001),
(67, 'CSPDCL', 'boopathy@avoups.com', '09551086665', '', 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'Chhattisgarh', 800001),
(68, 'Orpak', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Switching AVO\r\n97 Raja ram Mohan roy road Kolkata 91', 'kolkata', 'West Bengal', 700038),
(69, '3CIT Solutions', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chn,200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(70, 'Rocket', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', '200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(71, 'PNB-J&K Circle', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Delhi', 'Delhi', 'Delhi', 100001),
(72, 'Exide-UPS', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chn,200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(73, 'Equitas Bank', 'boopathy@avoups.com', '095510', 86665, 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(74, 'Veritas', 'boopathy@avoups.com', '095510', 86665, 'Boopathy', '09551086665', 'Chennai', 'Chennai', 'Tamil nadu', 600001),
(75, 'Vakrangee', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Kolkatta', 'Kolkatta', 'WB', 300001),
(76, 'U.W. Replacements', 'boopathy@avoups.com', '09551086665', '', 'Boopathy', '09551086665', 'Chn,200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(77, 'Sourashtra Gramin Bank', 'boopathy@avoups.com', '09551086665', '', 'Boopathy', '09551086665', 'Chn,200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(78, 'Rajasthan MG Bank', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chn,200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(79, 'AU Small Finance Bank', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chn,200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(80, 'WBSEDCL', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Chn,200001,mumbai', 'mumbai', 'Maharashtra', 200001),
(81, 'Unison', 'boopathy@avoups.com', '09551086665', 9551086665, 'Devarajan', '09551086665', 'Chennai', 'Chennai', 'Tamil Nadu', 600001),
(82, 'Baroda Gujarat Gramin Bank', 'boopathy@avoups.com', '09551086665', 9551086665, 'Mubarak', '09551086665', 'Switching AVO\r\n97 Raja ram Mohan roy road Kolkata 91', 'kolkata', 'West Bengal', 700038),
(83, 'Rajlakshmi Enterprise', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Kolkatta', 'kolkata', 'West Bengal', 700038),
(84, 'IBM', 'boopathy@avoups.com', '09551086665', 9551086665, 'Abhay', '09551086665', 'Chennai', 'Chennai', 'Tamil Nadu', 635901),
(85, 'Flipkart', 'boopathy@avoups.com', '09551086665', 9551086665, 'Boopathy', '09551086665', 'Bangalore', 'Bangalore', 'Karnataka', 560001);

SET FOREIGN_KEY_CHECKS = 1;
