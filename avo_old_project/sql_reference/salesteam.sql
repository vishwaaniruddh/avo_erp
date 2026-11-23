-- ========================================================
-- AVO ERP Table Export: `salesteam`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 126
-- Sample Records Exported: 100 (Max Limit: 100)
-- Sort Column: `exe_id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `salesteam`;

CREATE TABLE `salesteam` (
  `exe_id` int(11) NOT NULL AUTO_INCREMENT,
  `exe_name` varchar(50) NOT NULL,
  `exe_title` varchar(25) NOT NULL,
  `exe_desgn` varchar(25) NOT NULL,
  `exe_empid` varchar(25) DEFAULT NULL,
  `exe_loc` varchar(25) DEFAULT NULL,
  `exe_contact` bigint(20) NOT NULL,
  `exe_email` varchar(50) DEFAULT NULL,
  `sales_head` varchar(50) NOT NULL,
  `sales_rep` varchar(50) NOT NULL,
  `created_by` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1-active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`exe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 100 records for table `salesteam`
-- --------------------------------------------------------

INSERT INTO `salesteam` (`exe_id`, `exe_name`, `exe_title`, `exe_desgn`, `exe_empid`, `exe_loc`, `exe_contact`, `exe_email`, `sales_head`, `sales_rep`, `created_by`, `status`, `created_at`) VALUES
(64, 'Ajay Agarwal ', 'Head', 'Branch Head', '.', 'ODISHA ', 9437504208, 'ajay.agarwal@avoups.com', 'Ajay agarwal ', 'Ravindra Agarwal ', 'me', 0, '2026-04-10 14:06:05'),
(65, 'Jayanta Ghorui', 'Sales Exec', 'MANAGER- SALES & SERVICE', '.', 'WEST BENGAL ', 9331665338, 'jayanta.ghorui@avoups.com', 'Manish Agarwal ', 'Ravindra Agarwal ', 'me', 1, '2020-05-01 14:22:50'),
(66, 'Prasenjit Mishra', 'Sales Exec', 'MANAGER- SALES & SERVICE', '.', 'WEST BENGAL ', 7001567065, 'prasenjit.misra@avoups.com', 'Manish Agarwal ', 'Ravindra Agarwal ', 'me', 1, '2020-05-01 14:23:20'),
(67, 'Ashis Chakrabarti', 'Head', 'SENIOR MANAGER- SALES & S', '', 'WEST BENGAL ', 9230741410, 'ashis.chakrabarti@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 0, '2023-11-23 07:29:52'),
(68, 'Saheli De Das', 'Sales Exec', ' MANAGER- SALES & SERVICE', '', 'WEST BENGAL ', 7044302975, 'saheli.dd@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 0, '2023-11-23 07:28:14'),
(69, 'Manish Agarwal ', 'Head', 'Vice B. P', '', 'WEST BENGAL ', 7980508892, 'magarwal2@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 1, '2020-05-01 14:25:06'),
(70, 'Prasun Chakraborty ', 'Head', 'Vice President - Operatio', '', 'WEST BENGAL ', 9331393323, 'pchakraborty2@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 0, '2026-04-10 14:04:24'),
(71, 'Vijay Jha', 'Head', 'Asst. General Manager  -(', '', 'WEST BENGAL ', 9830585533, 'vijay.jha@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 1, '2020-05-01 14:26:01'),
(72, 'Shankar Sharma', 'Sales Exec', ' MANAGER- SALES & SERVICE', '', 'Delhi', 9350560293, 'ssharma@avoups.com', 'Anand Singh', 'Anand Singh', 'me', 1, '2020-05-01 14:26:35'),
(73, 'Anand Singh', 'Head', 'AGM North', '', 'Delhi', 9818880969, 'anand@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 1, '2020-05-01 14:27:01'),
(74, 'Prasenjit Sinha', 'Sales Exec', 'Sales Executive ', '', 'WEST BENGAL ', 9830541350, 'prasenjitsinha@pseups.co.in', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 0, '2022-06-07 07:08:14'),
(75, 'Murugesan', 'Sales Exec', 'Sales Executive ', '', 'Tamilnadu ', 9841818672, 'sankaran.mv@avoups.com', 'SANKARAN mv', 'M v SANKARAN ', 'me', 1, '2021-08-12 06:23:20'),
(76, 'Santosh (Hydrabad)', 'Sales Exec', ' MANAGER- SALES & SERVICE', 'santhosh@avoups.com', 'HYDERABAD ', 9885976041, 'santhosh@avoups.com', 'Prasun Chakraborty ', 'Prasun Chakraborty ', 'me', 0, '2020-05-01 14:16:31'),
(77, 'Debasish Kayal', 'Sales Exec', ' MANAGER- SALES & SERVICE', '', 'HYDERABAD ', 8332940702, 'debasishavo@gmail.com', 'Prasun Chakraborty ', 'Prasun Chakraborty ', 'me', 0, '2026-04-10 14:06:25'),
(78, ' M V Sankaran', 'Head', 'Vice President (South)', '', 'Chennai', 7305897446, 'sankaran.mv@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 1, '2020-05-01 14:28:46'),
(79, 'Pounami Chakraborty ', 'Sales Exec', 'Sales Executive ', '', 'WEST BENGAL ', 7980906026, 'pounami.chakraborty@avoups.com', 'Hirakjyoti Dhar', 'Hirakjyoti Dhar ', 'me', 0, '2022-06-07 06:55:41'),
(80, 'Sanjeev Narayan Achari', 'Sales Exec', 'Manager Sales', 'A000981', 'Raipur', 7828903498, 'spochitachi@avoups.com', 'Prasun Chakraborty', 'Prasun Chakraborty', 'me', 1, '2020-05-02 07:38:46'),
(81, 'Bikram Chhetri', 'Head', 'AGM', 'A000036', 'MUMBAI', 9699931133, 'BCHHETRI2@AVOUPS.COM', 'BIKRAM CHHETRI', 'RABINDRA AGARWAL', 'me', 0, '2026-04-10 14:13:22'),
(82, 'Hirakjyoti Dhar', 'Head', 'Sr Vice President', '', 'Kolkata', 8240959892, 'hdhar@avoups.com', 'Hirakjyoti Dhar', 'RABINDRA AGARWAL', 'me', 0, '2026-04-10 14:04:48'),
(83, 'ANAND SINGH', 'Team Lead', 'AGM - NORTH', '', 'DELHI', 9818880969, 'anand@avoups.com', 'ANAND SINGH', 'RABINDRA AGARWAL', 'me', 0, '2022-06-07 07:02:18'),
(84, 'SHANKAR SHARMA', 'Sales Exec', 'MANAGER', '', 'Delhi', 9350560293, 'ssharma@avoups.com', 'ANAND SINGH', 'ANAND SINGH', 'me', 1, '2020-05-05 06:56:31'),
(85, 'M K Srivastav ', 'Sales Exec', 'Sales Executive ', '.', 'LUCKNOW ', 7068214458, 'mksrivastav@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 0, '2026-04-10 14:13:38'),
(86, 'Ravish Kumar Singh', 'Sales Exec', 'Sales Executive ', '', 'Uttar Pradesh ', 9838944412, 'ravish.singh@avoups.com', 'Ravindra Agarwal ', 'Ravindra Agarwal ', 'me', 1, '2020-05-05 10:49:57'),
(87, 'Yudhistir Rout', 'Sales Exec', 'Sales Executive ', '', 'ODISHA ', 9348732942, 'yudhisthirroutray@gmail.com', 'Ajay agarwal ', 'Ajay agarwal ', 'me', 1, '2020-05-07 03:58:11'),
(90, 'Ranjith Kumar', 'Sales Exec', 'Sales Executive ', '', 'KARNATAKA ', 9900092014, 'bangaloreservice@avoups.com', 82, 83, 'me', 1, '2020-05-08 14:02:57'),
(91, 'Shrikant Sanap', 'Sales Exec', 'Manager', 'A000519', 'Mumbai', 9930028597, 'shrikant.sanap@avoups.com', 89, '', 'me', 1, '2020-05-08 14:21:40'),
(92, 'Amol Jadhav', 'Sales Exec', 'Corporate Accounts (West)', 'A000176', 'Mumbai', 9834186286, 'spocags@avoups.com', 89, '', 'me', 1, '2020-05-08 14:24:05'),
(94, 'Rabindra Agarwal', 'Team Lead', 'MD', '', 'Kolkatta', 9830064719, 'ragarwal2@avoups.com', 89, '', 'me', 1, '2020-05-09 08:25:38'),
(95, 'Prasun Chakraborty', 'Sales Exec', 'VP', '', 'Kolkata', 9331393323, 'pchakraborty2@avoups.com', 'Rabindra Agarwal', 'Rabindra Agarwal', 'me', 0, '2022-06-07 07:10:06'),
(96, 'Prasun Chakraborty', 'Sales Exec', 'VP', '', 'Kolkata', 9331393323, 'pchakraborty2@avoups.com', 70, 94, 'me', 0, '2020-05-09 09:33:52'),
(97, 'Amit Chakraborty ', 'Sales Exec', 'MANAGER- SALES & SERVICE', '', 'WEST BENGAL ', 9830719531, 'amit.chakraborty@avoups.com', 82, 94, 'me', 1, '2020-05-17 04:53:58'),
(100, 'Abhijit Sharma', 'Team Lead', 'AVP', 'A000312', 'Mumbai', 9322991412, 'abhijit@avoups.com', 89, 94, 'me', 0, '2026-04-10 14:07:13'),
(101, 'Mohanraj', 'Sales Exec', 'Sales Executive ', '', 'TAMILNADU ', 9600811845, 'mohancbe@avoups.com', 78, 94, 'me', 1, '2020-05-17 04:54:08'),
(102, 'Jayram Choudhury ', 'Team Lead', 'Senior Branch Head', '', 'Bihar', 8340419249, 'jayram.choudhury@avoups.com', 89, 94, 'me', 0, '2026-04-10 14:07:27'),
(103, 'Dipti Kundu Saha', 'Team Lead', 'Officer Credit Control ', '', 'Kolkata', 8910366498, 'dks@avoups.com', 82, 94, 'me', 0, '2022-06-07 07:00:57'),
(104, 'Soumya De', 'Head', 'Manager Marketing (East)', '', 'Kolkata', 8420676258, 'soumya.dey@avoups.com', 82, 94, 'me', 1, '2020-05-13 13:55:29'),
(105, 'Jaisankar', 'Sales Exec', 'Sales Executive ', '', 'TAMILNADU ', 9600811845, 'jaisankar.v@avoups.com', 78, 94, 'me', 1, '2020-05-14 05:58:13'),
(106, 'Alok Ranjan', 'Team Lead', 'Sales Executive ', '', 'Bihar,jharkhand ', 9835280813, 'alokranjanavo@gmail.com', 69, 94, 'me', 0, '2023-11-23 07:30:13'),
(107, 'Vikas Arote', 'Sales Exec', 'Sales Executive ', '', 'MUMBAI ', 8422952133, 'varote@avoups.com', 81, 94, 'me', 0, '2022-06-07 06:56:40'),
(108, 'Jagdish Singh', 'Sales Exec', 'MANAGER- SALES & SERVICE', '', 'Punjab ', 9316688286, 'jsingh@avoups.com', 64, 94, 'me', 0, '2026-04-10 14:07:42'),
(109, 'Mubarak Baig', 'Sales Exec', 'Senior Manager', '', 'MUMBAI ', 9421086657, 'mubarak@avoups.com', 70, 94, 'me', 0, '2026-04-10 14:07:48'),
(110, 'Amiya Nath Sarkar', 'Head', 'Sales Executive ', '', 'WEST BENGAL ', 9330833076, 's_amiya@avoups.com', 82, 94, 'me', 0, '2026-04-10 14:07:55'),
(111, 'Dinesh Verma', 'Team Lead', 'Sales Executive ', '', 'WEST BENGAL ', 9836670729, 'dverma@avoups.com', 82, 94, 'me', 1, '2020-05-26 06:53:46'),
(112, 'Samuel', 'Sales Exec', 'Sales Executive ', '', 'TAMILNADU ', 9600811845, 'chennaisales@avoups.com', 78, 94, 'me', 1, '2020-05-26 09:53:35'),
(113, 'Abhay Shirke', 'Sales Exec', 'Account Manager', 'XZA', 'Mumbai', 9987079443, 'spocfe@avoups.com', 82, 94, 'me', 0, '2022-06-07 06:51:07'),
(114, 'Sanjay Agarwal ', 'Sales Exec', ' MANAGER- SALES & SERVICE', '', 'WEST BENGAL ', 9038937968, 'sanjay@avoups.com', 70, 94, 'me', 1, '2020-06-01 10:10:19'),
(115, 'Kesavan', 'Sales Exec', 'Sales Executive ta', '', 'TAMILNADU ', 9445863605, 'chennaisales@avoups.com', 78, 94, 'me', 1, '2020-06-02 07:42:48'),
(116, 'Sunil Kumar ', 'Sales Exec', 'Sales Executive ', '', 'GUJARAT ', 9173077530, 'sunil.kumar@avoups.com', 70, 94, 'me', 0, '2022-06-07 07:01:19'),
(117, 'Gobi', 'Sales Exec', 'Sales Executive ', '', 'TAMILNADU ', 9524206676, 'chennaisales@avoups.com', 78, 94, 'me', 0, '2022-06-07 06:57:00'),
(118, 'Prasenjit Sinha', 'Sales Exec', 'Sales Executive ', '', 'WEST BENGAL ', 9830541350, 'prasenjitsinha@pseups.co.in', 70, 94, 'me', 1, '2020-06-05 10:32:05');

INSERT INTO `salesteam` (`exe_id`, `exe_name`, `exe_title`, `exe_desgn`, `exe_empid`, `exe_loc`, `exe_contact`, `exe_email`, `sales_head`, `sales_rep`, `created_by`, `status`, `created_at`) VALUES
(119, 'Navneet Kumar', 'Head', 'AGM', '', 'GUJARAT ', 9375430740, 'navneet.kumar@avoups.com', 70, 94, 'me', 1, '2020-06-09 11:57:24'),
(120, 'Udayan Ghosh', 'Sales Exec', 'MARKETING Manager ', '', 'WEST BENGAL ', 8798146747, 'udayan@avoups.com', 82, 94, 'me', 0, '2023-01-20 14:05:42'),
(121, 'Udayan Ghosh', 'Sales Exec', 'MARKETING Manager ', '', 'WEST BENGAL ', 8798146747, 'udayan@avoups.com', 82, 94, 'me', 0, '2020-06-15 07:07:08'),
(122, 'Anup Roy', 'Sales Exec', 'Sales Executive ', '', 'ASSAM ', 6290184684, 'anupavo18@gmail.com', 82, 94, 'me', 0, '2023-02-10 06:27:48'),
(123, 'Abhijit Sharma', 'Head', 'Sales Executive ', '', 'MUMBAI ', 9967338796, 'mumbaisales@avoups.com', 100, 94, 'me', 0, '2022-06-07 07:06:55'),
(124, 'Subhas Maity', 'Sales Exec', 'Sales Executive ', '', 'Jammu and Kashmir ', 7289918224, 'subhasmaity116@gmail.com', 64, 94, 'me', 0, '2023-11-23 07:27:28'),
(125, 'Rohit Sharma ', 'Sales Exec', 'Sales Executive ', '', 'Punjab ', 9316688286, 'jsingh@avoups.com', 64, 94, 'me', 1, '2020-07-10 09:19:36'),
(126, 'Sunhranshu Sekhar Das', 'Sales Exec', 'Sales Executive ', '', 'ODISHA ', 9938539282, 'subhranshu.avo@gmail.com', 64, 94, 'me', 0, '2023-11-23 07:23:47'),
(127, 'Sebastin', 'Sales Exec', 'Sales Executive ', '', 'Tamilnadu ', 9836670729, 'chennaisales@avoups.com', 78, 94, 'me', 0, '2023-11-23 07:27:48'),
(128, 'Biswajit Das', 'Sales Exec', 'Coordinator ', '', 'WEST BENGAL ', 8336909001, 'biswajitavo@gmail.com', 69, 94, 'me', 1, '2021-01-28 04:51:16'),
(129, 'Abinash sinha', 'Sales Exec', 'sales executive', '', 'lucknow', 6290184684, 'abinashsinha.avo@gmail.com', 73, 94, 'me', 1, '2026-04-15 05:27:53'),
(130, 'Ashok Pramanik', 'Sales Exec', 'Sales Executive ', '', 'WEST BENGAL ', 9007696335, 'pujashaw.avo@gmail.com', 64, 94, 'me', 0, '2026-04-10 14:10:41'),
(131, 'Priyanka Sinha', 'Sales Exec', 'Sales Executive ', '', 'JHARKHAND ', 6203172954, 'franchise@avoups.com', 69, 94, 'me', 0, '2023-02-10 06:28:14'),
(132, 'Kingshuk Chakraborty ', 'Sales Exec', 'Sales Executive ', '', 'WEST BENGAL ', 9836412225, 'k.chakraborty@avoups.com', 82, 94, 'me', 0, '2023-11-23 07:29:17'),
(133, 'Surendra Agarwal ', 'Head', 'Director ', '', 'WEST BENGAL ', 8336909002, 'vkp1973@gmail.com', 64, 94, 'me', 1, '2021-04-01 08:10:25'),
(134, 'Anup Agarwal ', 'Head', 'Director ', '', 'WEST BENGAL ', 9874492229, 'saleorder@avoups.com', 133, 94, 'me', 1, '2021-04-02 05:59:09'),
(135, 'Sourav Agarwal ', 'Head', 'Director ', '', 'WEST BENGAL ', 9830412342, 'dverma@avoups.com', 134, 94, 'me', 1, '2021-05-12 05:11:43'),
(136, 'Mr Pavan', 'Sales Exec', 'Sales Executive ', '', 'KOLKATA ', 7980906026, 'shivanna816@gmail.com', 64, 94, 'me', 0, '2026-04-10 14:10:59'),
(137, 'Shivananda gouda Patil', 'Sales Exec', 'Sales Executive ', '', 'KARNATAKA ', 9007696335, 'shiva.patil@avoups.com', 82, 94, 'me', 1, '2021-10-21 06:08:35'),
(138, ' Amar Salunkhe', 'Sales Exec', 'VP- Internal Audit and Sy', '', 'MUMBAI ', 9403685775, 'amar.salunkhe@avoups.com', 64, 94, 'me', 0, '2022-06-07 06:57:41'),
(139, 'Tauseef Hamid Dar', 'Sales Exec', 'Manager- Sales (Jammu and', '', 'JAMMU', 6006755142, 'jk_sales@avoups.com', 64, 94, 'me', 0, '2023-11-23 07:23:25'),
(140, 'Hiralal Bag', 'Sales Exec', 'sales executive', '', 'kolkata', 9547895196, 'hiralal@avoups.com', 134, 94, 'me', 0, '2023-11-23 07:22:38'),
(141, 'Farid Equbal', 'Head', 'Manager- Sales (Jammu and', '', 'JAMMU AND KASHMIR', 9650295508, 'farid.equbal@avoups.com', 134, 94, 'me', 0, '2023-01-20 14:02:41'),
(142, 'Sumit Sa', 'Head', 'Account Manager', '', 'West Bengal', 9733569096, 'patnaaccounts@avoups.com', 70, 94, 'me', 1, '2022-04-04 11:09:18'),
(143, 'Kaushik Dutta', 'Head', 'Service ', '', 'West Bengal', 9775350156, 'koushikdutta209@gmail.com', 70, 94, 'me', 0, '2023-11-23 07:28:36'),
(144, 'Radhakrisnan ', 'Sales Exec', 'Sales Executive ', '', 'Tamilnadu ', 4442183054, 'chennaisales@avoups.com', 78, 94, 'me', 1, '2022-06-07 07:18:58'),
(145, 'Agresh Goyal', 'Sales Exec', 'Sales Executive ', '', 'KARNATAKA ', 9727707676, 'agresh.goyal@avoups.com', 82, 94, 'me', 1, '2022-06-07 08:59:44'),
(146, 'Ramesh', 'Sales Exec', 'Sales Executive ', '', 'Tamilnadu ', 9488048838, 'avotrichy.ranesh@gmail.com', 78, 94, 'me', 1, '2022-06-07 10:53:34'),
(147, 'Sunil Kumar', 'Head', 'Sales Executive ', '', 'Mp', 9173077530, 'sunil.kumar@avoups.com', 69, 94, 'me', 1, '2022-06-13 10:04:01'),
(148, 'Sagar Chauhan', 'Team Lead', 'Marketing Manager ', '', 'GUJARAT ', 9016030740, 'sagarchauhan@avoups.com', 134, 94, 'me', 1, '2022-09-02 05:34:28'),
(149, 'Syed', 'Sales Exec', 'Sales Executive ', '', 'KARNATAKA ', 9606179991, 'syedp@avoups.com', 82, 94, 'me', 1, '2022-10-19 09:13:18'),
(150, 'Anurag Pandey', 'Sales Exec', 'Central team', '', 'Uttar Pradesh', 7985098401, 'anurag.pandey@avoups.com', 70, 94, 'me', 0, '2023-11-23 07:23:05'),
(151, 'Dalveer Singh', 'Head', 'Sales Executive ', '', 'Delhi', 7007297196, 'spoccorp@avoups.com', 70, 94, 'me', 1, '2023-02-02 08:07:29'),
(152, 'Deepak Jha', 'Sales Exec', 'Sales Executive', NULL, 'Kolkatta', 8789763983, 'boopathy@avoups.com', 70, 94, 'Boopathy', 1, '2023-08-24 07:37:04'),
(153, 'Lalit Kumar Bhalotia', 'Sales Exec', 'Sales Executive', NULL, 'Jaipur', 9874966667, 'bhalotialalit2@gmail.com', 70, 94, 'Boopathy', 1, '2026-04-15 05:27:13'),
(154, 'Vishal Paul', 'Sales Exec', 'Sales Executive', 'EXE', 'Mumbai', 7400206032, 'mumbaisales@avoups.com', 'Prasun', 'Prasun', 'me', 0, '2026-04-10 14:09:12'),
(155, 'Anil Dulani', 'Manager', 'Manager Sales', 12345, 'Mammbai', 8007701100, 'anil.dulani@avoups.com', 'No', 'No', 'Me', 1, '2025-05-12 10:00:00'),
(156, 'Raina Jhunjhunwala', 'Sales Exec', 'Manager Sales', NULL, 'Kolkatta', 9830363317, 'rainajhunjhunwala@avoups.com', 'Prasun', 'Prasun', 'me', 1, '2025-06-04 13:00:00'),
(157, 'Vishal Pandey', 'Sales Exec', 'Sales Exec', 123456, 'Kolkatta', 8013692606, 'vishal@avoups.com', 'No', 'MD', 'me', 1, '2025-07-25 10:00:00'),
(158, 'Chandan Nayak', 'Sales Exec', 'Sales Exec', 'Exec-101', 'Cuttack', 9776082906, 'chandan@avoups.com', 'Direct', 'Direct', 'me', 1, '2025-10-07 07:38:37'),
(159, 'Mukesh Sharma', 'Sales Exec', 'Sales Exec', 'Exec-101', 'Kolkatta', 898426227, 'mukesh@avoups.com', 'Direct', 'Direct', 'me', 1, '2025-08-10 10:00:00'),
(160, 'Prashant Pathare', 'Head', 'Sales Head', NULL, 'Mumbai', 9833553196, 'prashanth@avoups.com', 'No', 'MD', 'me', 1, '2025-12-12 10:00:00'),
(161, 'Prakash Mishra', 'Sales Exe', 'Sales Exe', 1000, 'Guwahati', 6204703011, 'prakash@avoups.com', 'Abhishek', 'Abhishek', 'me', 1, '2026-01-31 12:00:00'),
(162, 'Satyendra Upadhyay', 'Branch Head', 'Branch Head', 'A0001', 'Kolkatta', 9864084540, 'S.upadhyay@avoups.com', 'Abhishek', 'Abhishek', 'me', 1, '2026-02-10 12:00:00'),
(163, 'Prashant Manohar Pendurkar', 'Head - Channels', 'Vice President- Channels', 'A1001', 'Mumbai', 9820604060, 'ppendurkar@avoups.com', 'Rabiji', 'Nil', 'me', 1, '2026-02-25 10:00:00'),
(164, 'Nisha Mohite', 'Sales', 'Executive', 'A800', 'Mumbai', 7710860357, 'nmohite@avoups.com', 'Rabi Sir', 'Rabi Sir', 'me', 1, '2026-04-10 14:03:58'),
(165, 'Manoj Kumar', 'Marketing Manager', 'Marketing', 'Marketing', 'Delhi', 8178273266, 'Kumar.Manoj@avoups.com', 'Syed Pasha', 'Syed', 'me', 1, '2026-07-21 12:00:00'),
(166, 'Tyagi Nand Firi', 'Sales Exec', 'Sales Exec', 'A1000', 'Jharkhand', 9122802272, 'tyagi@avoups.com', 'Manish Agarwal', 'Manish', 'me', 1, '2026-08-25 12:00:00'),
(167, 'Rahul Gound', 'Sales Exec', 'Sales Exec', 'A001082', 'Mumbai', 8983512021, 'mumbailogistics@avoups.com', 'Rabi Sir', 'Rabi SIr', 'me', 1, '2026-08-25 12:00:00'),
(168, 'Man Bahadur Patel', 'Account Manager', 'Sales Exec', 'A001800', 'Mumbai', 8169388478, 'financesupport@avoups.com', 'Rabindra Agarwal', 'Rabindra', 'me', 1, '2026-09-10 12:00:00');

SET FOREIGN_KEY_CHECKS = 1;
