-- ========================================================
-- AVO ERP Table Export: `user_name`
-- Database: avoservice_avoserviCrm
-- Generated: 2026-11-19 20:24:18
-- Total Table Rows in DB: 110
-- Sample Records Exported: 100 (Max Limit: 100)
-- Sort Column: `id` DESC
-- ========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `user_name`;

CREATE TABLE `user_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_id` int(11) NOT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `user_password` varchar(50) DEFAULT NULL,
  `user_desgn` varchar(50) DEFAULT NULL,
  `roles` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `roles` (`roles`),
  CONSTRAINT `user_name_ibfk_1` FOREIGN KEY (`roles`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------
-- Dumping latest 100 records for table `user_name`
-- --------------------------------------------------------

INSERT INTO `user_name` (`id`, `sales_id`, `user_name`, `user_password`, `user_desgn`, `roles`, `status`, `created_at`) VALUES
(13, 48, 'AbhijithJadhav_14', 'AbhijithJadhav@123', 'Account manager', 2, 1, '2020-04-23 06:36:41'),
(14, 49, 'ShivShankarParasad_72', 'ShivShankarParasad@123', 'Branch Manager', 2, 1, '2020-04-23 06:36:47'),
(15, 50, 'PRADEEPKUMARJHA_82', 'PRADEEPKUMARJHA@123', 'Branch Manager', 2, 1, '2020-04-23 06:36:52'),
(16, 51, 'SantoshParmar_95', 'SantoshParmar@123', 'Branch Manager', 2, 1, '2020-04-23 06:36:38'),
(17, 52, 'Ananthram_59', 'Ananthram@123', 'Manager Sales', 2, 1, '2020-04-23 06:37:17'),
(18, 53, 'Radhakrishnan_86', 'Radhakrishnan@123', 'Manager Sales', 2, 1, '2020-04-23 06:37:12'),
(19, 54, 'Ramesh_54', 'Ramesh@123', 'Sales Executive', 2, 1, '2020-04-23 06:37:23'),
(20, 55, 'Mahesh_82', 'Mahesh@123', 'Sales Executive', 2, 1, '2020-04-23 06:37:25'),
(21, 56, 'Santosh_82', 'Santosh@123', 'Sales Executive', 2, 1, '2020-04-23 06:37:30'),
(22, 57, 'Venkat_52', 'Venkat@123', 'Sr Officer', 2, 1, '2020-04-23 06:37:33'),
(23, 58, 'Arun Yogi_48', 'Arun Yogi@123', 'Manager Sales', 2, 1, '2020-04-23 06:37:36'),
(24, 59, 'Antony_11', 'Antony@123', 'Branch Manager', 2, 1, '2020-04-23 06:37:39'),
(25, 60, 'ParthoChowdhury_99', 'ParthoChowdhury@123', 'Sales Executive', 2, 1, '2020-04-23 06:37:42'),
(26, 61, 'SusovanBiswas_73', 'SusovanBiswas@123', 'Sales Executive', 2, 1, '2020-04-23 06:37:47'),
(30, 62, 'swapnilvishwakarma_23', 'swapnilvishwakarma_23@123', 'Sales', 2, 1, '2020-04-23 06:42:33'),
(31, 63, 'nasiruddin_9', 'nasiruddin_9@123', 'Senior Manager', 2, 1, '2020-05-01 12:25:01'),
(32, 64, 'ajayagarwal_39', 'ajayagarwal_39@123', 'Branch Head', 1, 1, '2020-05-01 13:18:00'),
(33, 65, 'jayantaghorui_19', 'jayantaghorui_19@123', 'MANAGER- SALES & SERVICE', 2, 1, '2020-05-01 13:20:16'),
(34, 66, 'prasenjitmishra_14', 'prasenjitmishra_14@123', 'MANAGER- SALES & SERVICE', 2, 1, '2020-05-01 13:22:15'),
(35, 67, 'ashischakrabarti_78', 'ashischakrabarti_78@123', 'SENIOR MANAGER- SALES & SERVICE', 1, 1, '2020-05-01 13:25:13'),
(36, 68, 'sahelidedas_69', 'sahelidedas_69@123', ' MANAGER- SALES & SERVICE', 2, 1, '2020-05-01 13:26:45'),
(37, 69, 'manishagarwal_47', 'manishagarwal_47@123', 'Vice B. P', 1, 1, '2020-05-01 13:32:38'),
(38, 70, 'prasunchakraborty_50', 'prasunchakraborty_50@123', 'Vice President - Operation.', 1, 1, '2020-05-01 13:34:35'),
(39, 71, 'vijayjha_14', 'vijayjha_14@123', 'Asst. General Manager  -(AGM) (Import & Export )', 1, 1, '2020-05-01 13:37:09'),
(40, 72, 'shankarsharma_6', 'shankarsharma_6@123', ' MANAGER- SALES & SERVICE', 2, 1, '2020-05-01 13:40:30'),
(41, 73, 'anandsingh_75', 'anandsingh_75@123', 'AGM North', 1, 1, '2020-05-01 13:42:24'),
(42, 74, 'prasenjitsinha_73', 'prasenjitsinha_73@123', 'Sales person', 2, 1, '2020-05-01 13:55:03'),
(43, 75, 'diptikundusaha_55', 'diptikundusaha_55@123', 'Sales person', 2, 1, '2020-05-01 13:57:11'),
(44, 76, 'santosh(hydrabad)_44', 'santosh(hydrabad)_44@123', ' MANAGER- SALES & SERVICE', 2, 1, '2020-05-01 14:02:30'),
(45, 77, 'debasishkayal_2', 'debasishkayal_2@123', ' MANAGER- SALES & SERVICE', 2, 1, '2020-05-01 14:04:19'),
(46, 78, 'mvsankaran_88', 'mvsankaran_88@123', 'Vice President (South)', 1, 1, '2020-05-01 14:07:29'),
(47, 79, 'poumamichakraborty_77', 'poumamichakraborty_77@123', 'Sales person', 2, 1, '2020-05-01 14:10:14'),
(48, 80, 'sanjeevnarayanachari_69', 'sanjeevnarayanachari_69@123', 'Manager Sales', 2, 1, '2020-05-02 07:38:46'),
(49, 81, 'bikramchhetri_84', 'bikramchhetri_84@123', 'AGM', 1, 1, '2020-05-05 06:49:15'),
(50, 82, 'hirakjyotidhar_73', 'hirakjyotidhar_73@123', 'Sr Vice President', 1, 1, '2020-05-05 06:51:20'),
(52, 84, 'shankarsharma_60', 'shankarsharma_60@123', 'MANAGER', 2, 1, '2020-05-05 06:56:31'),
(53, 85, 'mksrivastav_59', 'mksrivastav_59@123', 'Sales Executive ', 2, 1, '2020-05-05 07:14:50'),
(54, 86, 'ravishkumarsingh_9', 'ravishkumarsingh_9@123', 'Sales Executive ', 2, 1, '2020-05-05 10:49:57'),
(55, 87, 'yudhistirrout_79', 'yudhistirrout_79@123', 'Sales Executive ', 2, 1, '2020-05-07 03:58:11'),
(57, 89, 'ravindraagarwal_55', 'ravindraagarwal_55@123', 'Director ', 1, 1, '2020-05-08 07:56:35'),
(58, 90, 'ranjithkumar_36', 'ranjithkumar_36@123', 'Sales Executive ', 2, 1, '2020-05-08 14:02:57'),
(59, 91, 'shrikantsanap_66', 'shrikantsanap_66@123', 'Manager', 2, 1, '2020-05-08 14:21:40'),
(60, 92, 'amoljadhav_89', 'amoljadhav_89@123', 'Corporate Accounts (West)', 2, 1, '2020-05-08 14:24:05'),
(62, 94, 'rabindraagarwal_79', 'rabindraagarwal_79@123', 'MD', 4, 1, '2020-05-09 08:25:38'),
(64, 96, 'prasunchakraborty_99', 'prasunchakraborty_99@123', 'VP', 2, 1, '2020-05-09 09:27:41'),
(65, 97, 'amitchakraborty_36', 'amitchakraborty_36@123', 'MANAGER- SALES & SERVICE', 2, 1, '2020-05-17 04:42:19'),
(66, 98, 'vishwakarma_22', 'vishwakarma_22@123', 'Native App Developer', 2, 1, '2020-05-12 09:28:28'),
(67, 99, 'vishwakarma_82', 'vishwakarma_82@123', 'Native App Developer', 2, 1, '2020-05-12 09:37:15'),
(68, 100, 'abhijitsharma_89', 'abhijitsharma_89@123', 'AVP', 1, 1, '2020-05-17 04:44:26'),
(69, 101, 'mohanraj_47', 'mohanraj_47@123', 'Sales Executive ', 2, 1, '2020-05-17 04:42:01');

INSERT INTO `user_name` (`id`, `sales_id`, `user_name`, `user_password`, `user_desgn`, `roles`, `status`, `created_at`) VALUES
(70, 102, 'jayramchoudhury_67', 'jayramchoudhury_67@123', 'Senior Branch Head', 1, 1, '2020-05-17 04:43:39'),
(71, 103, 'diptikundusaha_71', 'diptikundusaha_71@123', 'Officer Credit Control ', 2, 1, '2020-05-17 04:44:12'),
(72, 104, 'soumyade_94', 'soumyade_94@123', 'Manager Marketing (East)', 1, 1, '2020-05-13 13:55:29'),
(73, 105, 'jaishankar_23', 'jaishankar_23@123', 'Sales Executive ', 2, 1, '2020-05-17 04:42:10'),
(74, 106, 'alokranjan_24', 'alokranjan_24@123', 'Sales Executive ', 2, 1, '2020-05-17 04:43:17'),
(75, 107, 'vikasarote_11', 'vikasarote_11@123', 'Sales Executive ', 2, 1, '2020-05-17 07:09:55'),
(76, 108, 'jagdishsingh_71', 'jagdishsingh_71@123', 'MANAGER- SALES & SERVICE', 2, 1, '2020-05-18 07:39:56'),
(77, 109, 'mubarakbaig_81', 'mubarakbaig_81@123', 'Senior Manager', 2, 1, '2020-05-19 08:12:27'),
(78, 110, 'amiyanathsarkar_10', 'amiyanathsarkar_10@123', 'Sales Executive ', 1, 1, '2020-05-23 08:21:45'),
(79, 111, 'dineshverma_1', 'dineshverma_1@123', 'Sales Executive ', 4, 1, '2020-05-26 06:53:46'),
(80, 112, 'samuel_44', 'samuel_44@123', 'Sales Executive ', 2, 1, '2020-05-26 09:53:35'),
(81, 113, 'abhayshirke_78', 'abhayshirke_78@123', 'Account Manager', 2, 1, '2020-05-28 06:04:05'),
(82, 114, 'sanjayagarwal_50', 'sanjayagarwal_50@123', ' MANAGER- SALES & SERVICE', 2, 1, '2020-06-01 10:10:19'),
(83, 115, 'kesavan_10', 'kesavan_10@123', 'Sales Executive ta', 2, 1, '2020-06-02 07:42:48'),
(84, 116, 'sunilkumar_17', 'sunilkumar_17@123', 'Sales Executive ', 2, 1, '2020-06-04 13:28:26'),
(85, 117, 'gobi_100', 'gobi_100@123', 'Sales Executive ', 2, 1, '2020-06-05 05:14:23'),
(86, 118, 'prasenjitsinha_26', 'prasenjitsinha_26@123', 'Sales Executive ', 2, 1, '2020-06-05 10:32:05'),
(87, 119, 'navneetkumar_45', 'navneetkumar_45@123', 'AGM', 1, 1, '2020-06-09 11:57:24'),
(88, 120, 'udayanghosh_68', 'udayanghosh_68@123', 'MARKETING Manager ', 2, 1, '2020-06-15 07:06:29'),
(89, 121, 'udayanghosh_71', 'udayanghosh_71@123', 'MARKETING Manager ', 2, 1, '2020-06-15 07:06:40'),
(90, 122, 'anuproy_22', 'anuproy_22@123', 'Sales Executive ', 2, 1, '2020-06-17 08:27:28'),
(91, 123, 'abhijitsharma_20', 'abhijitsharma_20@123', 'Sales Executive ', 1, 1, '2020-06-30 10:52:57'),
(92, 124, 'subhasmaity_89', 'subhasmaity_89@123', 'Sales Executive ', 2, 1, '2020-07-08 08:23:39'),
(93, 125, 'rohitsharma_47', 'rohitsharma_47@123', 'Sales Executive ', 2, 1, '2020-07-10 09:19:36'),
(94, 126, 'sunhranshusekhardas_22', 'sunhranshusekhardas_22@123', 'Sales Executive ', 2, 1, '2020-10-07 09:29:11'),
(95, 127, 'sebastin_80', 'sebastin_80@123', 'Sales Executive ', 2, 1, '2020-10-27 06:21:22'),
(96, 128, 'biswajitdas_50', 'biswajitdas_50@123', 'Coordinator ', 2, 1, '2021-01-28 04:51:16'),
(97, 129, 'abinashsinha_13', 'abinashsinha_13@123', 'sales executive', 2, 1, '2021-02-04 07:32:43'),
(98, 130, 'ashokpramanik_73', 'ashokpramanik_73@123', 'Sales Executive ', 2, 1, '2021-03-02 12:31:41'),
(99, 131, 'priyankasinha_13', 'priyankasinha_13@123', 'Sales Executive ', 2, 1, '2021-03-03 11:06:43'),
(100, 132, 'kingshukchakraborty_62', 'kingshukchakraborty_62@123', 'Sales Executive ', 2, 1, '2021-03-22 05:43:07'),
(101, 133, 'surendraagarwal_17', 'surendraagarwal_17@123', 'Director ', 1, 1, '2021-04-01 08:10:25'),
(102, 134, 'anupagarwal_5', 'anupagarwal_5@123', 'Director ', 1, 1, '2021-04-02 05:59:09'),
(103, 135, 'souravagarwal_98', 'souravagarwal_98@123', 'Director ', 1, 1, '2021-05-12 05:11:43'),
(104, 136, 'mrpavan_37', 'mrpavan_37@123', 'Sales Executive ', 2, 1, '2021-08-06 07:58:44'),
(105, 137, 'shivanandagoudapatil_85', 'shivanandagoudapatil_85@123', 'Sales Executive ', 2, 1, '2021-10-21 06:08:35'),
(106, 138, 'amarsalunkhe_26', 'amarsalunkhe_26@123', 'VP- Internal Audit and System Development ᐧ', 2, 1, '2021-11-02 05:31:25'),
(107, 139, 'tauseefhamiddar_66', 'tauseefhamiddar_66@123', 'Manager- Sales (Jammu and Kashmir)', 2, 1, '2021-11-10 08:10:47'),
(108, 140, 'hiralalbag_39', 'hiralalbag_39@123', 'sales executive', 2, 1, '2021-12-14 13:16:55'),
(109, 141, 'faridequbal_37', 'faridequbal_37@123', 'Manager- Sales (Jammu and Kashmir)', 1, 0, '2023-02-10 06:35:59'),
(110, 142, 'sumitshah_5', 'sumitshah_5@123', 'Account Manager', 1, 1, '2022-04-04 11:05:46'),
(111, 143, 'kaushikdutta_66', 'kaushikdutta_66@123', 'Service ', 1, 1, '2022-05-25 10:08:30'),
(112, 144, 'radhakrisnan_53', 'radhakrisnan_53@123', 'Sales Executive ', 2, 1, '2022-06-07 07:18:58'),
(113, 145, 'agreshgoyal_21', 'agreshgoyal_21@123', 'Sales Executive ', 2, 1, '2022-06-07 08:59:44'),
(114, 146, 'ramesh_79', 'ramesh_79@123', 'Sales Executive ', 2, 1, '2022-06-07 10:53:34'),
(115, 147, 'sunilkumar_65', 'sunilkumar_65@123', 'Sales Executive ', 1, 1, '2022-06-13 10:04:01'),
(116, 148, 'sagarchauhan_30', 'sagarchauhan_30@123', 'Marketing Manager ', 4, 1, '2022-09-02 05:34:28'),
(117, 149, 'syed_39', 'syed_39@123', 'Sales Executive ', 2, 1, '2022-10-19 09:13:18'),
(118, 150, 'anuragpandey_69', 'anuragpandey_69@123', 'Central team', 2, 1, '2022-12-13 09:30:01'),
(119, 151, 'dalveersingh_22', 'dalveersingh_22@123', 'Sales Executive ', 1, 1, '2023-02-02 08:07:29');

SET FOREIGN_KEY_CHECKS = 1;
