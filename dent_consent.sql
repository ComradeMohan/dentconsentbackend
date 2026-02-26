-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026 at 05:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dent_consent`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_name` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consent_records`
--

CREATE TABLE `consent_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `treatment_id` bigint(20) UNSIGNED NOT NULL,
  `quiz_score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `is_checklist_confirmed` tinyint(1) DEFAULT 0,
  `signature_path` varchar(255) DEFAULT NULL,
  `signed_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_profiles`
--

CREATE TABLE `doctor_profiles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` varchar(20) DEFAULT NULL,
  `council_id` varchar(50) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `experience_years` int(11) DEFAULT 0,
  `qualifications` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `signature_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_profiles`
--

INSERT INTO `doctor_profiles` (`user_id`, `full_name`, `mobile_number`, `gender`, `dob`, `council_id`, `specialization`, `experience_years`, `qualifications`, `created_at`, `updated_at`, `signature_url`) VALUES
(12, 'mohan reddy', '2883838388', 'Female', '2005-12-22', '27jejissui2', 'Implantologist', 0, 'BDS, MDS, DDS', '2026-02-21 12:21:07', '2026-02-21 13:11:13', 'uploads/signatures/doc_sig_efa2cc4318a9ba08dc0e9c241af85402.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `educational_videos`
--

CREATE TABLE `educational_videos` (
  `id` int(11) NOT NULL,
  `operation_type_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `video_url` varchar(500) NOT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `display_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `educational_videos`
--

INSERT INTO `educational_videos` (`id`, `operation_type_id`, `title`, `video_url`, `thumbnail_url`, `duration_seconds`, `display_order`, `created_at`) VALUES
(1, 1, 'Single Tooth Implant', 'https://www.youtube.com/watch?v=g-i3P-D6p7M', 'https://i.ytimg.com/vi/g-i3P-D6p7M/hqdefault.jpg', 180, 1, '2026-02-22 04:38:39'),
(2, 2, 'Multiple Tooth Implant', 'https://www.youtube.com/watch?v=yXuiA_6q1eQ', 'https://i.ytimg.com/vi/yXuiA_6q1eQ/hqdefault.jpg', 180, 2, '2026-02-22 04:38:39'),
(3, 3, 'Full Arch Implant', 'https://www.youtube.com/watch?v=ggDZY-mPwoA', 'https://i.ytimg.com/vi/ggDZY-mPwoA/hqdefault.jpg', 180, 3, '2026-02-22 04:38:39'),
(4, 4, 'Implant-Supported Bridge', 'https://www.youtube.com/watch?v=q_pqHuCFa-w', 'https://i.ytimg.com/vi/q_pqHuCFa-w/hqdefault.jpg', 180, 4, '2026-02-22 04:38:39'),
(5, 5, 'Implant-Supported Denture', 'https://www.youtube.com/watch?v=UFIURlJihas', 'https://i.ytimg.com/vi/UFIURlJihas/hqdefault.jpg', 180, 5, '2026-02-22 04:38:39'),
(6, 6, 'Bone Grafting (Support)', 'https://www.youtube.com/watch?v=VjQs3KQcqy0', 'https://i.ytimg.com/vi/VjQs3KQcqy0/hqdefault.jpg', 180, 6, '2026-02-22 04:38:39'),
(7, 7, 'Crown', 'https://www.youtube.com/watch?v=N7Yt2SEhGy0', 'https://i.ytimg.com/vi/N7Yt2SEhGy0/hqdefault.jpg', 180, 7, '2026-02-22 04:38:39'),
(8, 8, 'Bridge', 'https://www.youtube.com/watch?v=E0WaE_nO1FY', 'https://i.ytimg.com/vi/E0WaE_nO1FY/hqdefault.jpg', 180, 8, '2026-02-22 04:38:39'),
(9, 9, 'Complete Denture', 'https://www.youtube.com/watch?v=QlDAhrz4gV0', 'https://i.ytimg.com/vi/QlDAhrz4gV0/hqdefault.jpg', 180, 9, '2026-02-22 04:38:39'),
(10, 10, 'Partial Denture', 'https://www.youtube.com/watch?v=Ew1sb7eyFXc', 'https://i.ytimg.com/vi/Ew1sb7eyFXc/hqdefault.jpg', 180, 10, '2026-02-22 04:38:39'),
(11, 11, 'Veneer', 'https://www.youtube.com/watch?v=fc1Ik0xJmLk', 'https://i.ytimg.com/vi/fc1Ik0xJmLk/hqdefault.jpg', 180, 11, '2026-02-22 04:38:39'),
(12, 12, 'Full Mouth Rehab', 'https://www.youtube.com/watch?v=KkmlznaDEoY', 'https://i.ytimg.com/vi/KkmlznaDEoY/hqdefault.jpg', 180, 12, '2026-02-22 04:38:39');

-- --------------------------------------------------------

--
-- Table structure for table `key_topics`
--

CREATE TABLE `key_topics` (
  `id` int(11) NOT NULL,
  `operation_type_id` int(11) NOT NULL,
  `topic` text NOT NULL,
  `display_order` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `key_topics`
--

INSERT INTO `key_topics` (`id`, `operation_type_id`, `topic`, `display_order`, `created_at`) VALUES
(1, 1, 'Titanium screw acts as new root', 1, '2026-02-22 04:56:31'),
(2, 1, '3–6 months healing (osseointegration)', 2, '2026-02-22 04:56:31'),
(3, 1, '95–98% long-term success', 3, '2026-02-22 04:56:31'),
(4, 1, 'Local anesthesia only', 4, '2026-02-22 04:56:31'),
(5, 2, '2–4 implants support bridge', 1, '2026-02-22 04:56:31'),
(6, 2, 'No damage to healthy teeth', 2, '2026-02-22 04:56:31'),
(7, 2, 'Same healing time as single', 3, '2026-02-22 04:56:31'),
(8, 3, 'All-on-4 or All-on-6 technique', 1, '2026-02-22 04:56:31'),
(9, 3, 'Teeth in a Day possible', 2, '2026-02-22 04:56:31'),
(10, 3, 'Fixed permanent solution', 3, '2026-02-22 04:56:31'),
(11, 4, 'Implants replace missing teeth roots', 1, '2026-02-22 04:56:31'),
(12, 4, 'Stronger than tooth-supported bridge', 2, '2026-02-22 04:56:31'),
(13, 5, 'Snap-in stability', 1, '2026-02-22 04:56:31'),
(14, 5, 'Removable for cleaning', 2, '2026-02-22 04:56:31'),
(15, 5, '80–90% chewing power', 3, '2026-02-22 04:56:31'),
(16, 6, 'Builds bone for implant', 1, '2026-02-22 04:56:31'),
(17, 6, 'Can use patient’s own bone', 2, '2026-02-22 04:56:31'),
(18, 6, '3–6 months before implant', 3, '2026-02-22 04:56:31'),
(19, 7, 'Full coverage cap', 1, '2026-02-22 04:56:31'),
(20, 7, 'Protects weak tooth', 2, '2026-02-22 04:56:31'),
(21, 7, 'Zirconia or porcelain', 3, '2026-02-22 04:56:31'),
(22, 8, 'Replaces 1–3 teeth', 1, '2026-02-22 04:56:31'),
(23, 8, 'Anchored on adjacent teeth', 2, '2026-02-22 04:56:31'),
(24, 8, 'Faster than implants', 3, '2026-02-22 04:56:31'),
(25, 9, 'Full set for one jaw', 1, '2026-02-22 04:56:31'),
(26, 9, '5–7 visits required', 2, '2026-02-22 04:56:31'),
(27, 9, '20–30% chewing efficiency', 3, '2026-02-22 04:56:31'),
(28, 10, 'Clasps on remaining teeth', 1, '2026-02-22 04:56:31'),
(29, 10, 'Removable & affordable', 2, '2026-02-22 04:56:31'),
(30, 11, 'Ultra-thin porcelain shell', 1, '2026-02-22 04:56:31'),
(31, 11, 'Minimal enamel removal', 2, '2026-02-22 04:56:31'),
(32, 11, 'Front teeth only', 3, '2026-02-22 04:56:31'),
(33, 12, 'Complete smile & bite makeover', 1, '2026-02-22 04:56:31'),
(34, 12, 'May combine crowns + implants', 2, '2026-02-22 04:56:31'),
(35, 12, '6–12 months treatment', 3, '2026-02-22 04:56:31');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(1, 12, 8, 'hu', '2026-02-21 14:00:43');

-- --------------------------------------------------------

--
-- Table structure for table `operation_types`
--

CREATE TABLE `operation_types` (
  `id` int(11) NOT NULL,
  `specialization_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `success_rate` decimal(5,2) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operation_types`
--

INSERT INTO `operation_types` (`id`, `specialization_id`, `name`, `slug`, `description`, `success_rate`, `icon`, `video_url`, `created_at`) VALUES
(1, 1, 'Single Tooth Implant', 'single_tooth_implant', 'Replaces one missing tooth with a titanium implant, abutment, and crown. Preserves adjacent teeth and bone.', 96.50, 'single_tooth_implant.png', NULL, '2026-02-21 16:00:37'),
(2, 1, 'Multiple Tooth Implant', 'multiple_tooth_implant', 'Uses 2–4 implants to support a bridge replacing several adjacent missing teeth.', 95.80, 'multiple_tooth_implant.png', NULL, '2026-02-21 16:00:37'),
(3, 1, 'Full Arch Implant', 'full_arch_implant', 'Replaces all teeth in one jaw (e.g., All-on-4 or All-on-6) with a fixed bridge supported by 4–6 implants.', 97.20, 'full_arch_implant.png', NULL, '2026-02-21 16:00:37'),
(4, 1, 'Implant-Supported Bridge', 'implant_supported_bridge', 'Fixed bridge anchored by implants (typically 2–3) to replace multiple missing teeth in a row.', 96.00, 'implant_bridge.png', NULL, '2026-02-21 16:00:37'),
(5, 1, 'Implant-Supported Denture', 'implant_supported_denture', 'Overdenture that clips or locks onto 2–6 implants for improved stability over conventional dentures.', 94.50, 'implant_denture.png', NULL, '2026-02-21 16:00:37'),
(6, 1, 'Bone Grafting (Support)', 'bone_grafting_support', 'Augments jawbone volume (autograft, allograft, or synthetic) to enable implant placement when bone is insufficient.', 92.00, 'bone_grafting.png', NULL, '2026-02-21 16:00:37'),
(7, 2, 'Crown', 'dental_crown', 'Full-coverage restoration that caps a damaged or weakened tooth to restore shape, strength, and appearance.', 95.00, 'crown.png', NULL, '2026-02-21 16:00:37'),
(8, 2, 'Bridge', 'dental_bridge', 'Fixed prosthesis that replaces one or more missing teeth by anchoring to adjacent natural teeth or implants.', 93.50, 'bridge.png', NULL, '2026-02-21 16:00:37'),
(9, 2, 'Complete Denture', 'complete_denture', 'Removable full-arch prosthesis replacing all teeth in the upper or lower jaw.', 90.00, 'complete_denture.png', NULL, '2026-02-21 16:00:37'),
(10, 2, 'Partial Denture', 'partial_denture', 'Removable appliance replacing some missing teeth while preserving remaining natural teeth.', 91.00, 'partial_denture.png', NULL, '2026-02-21 16:00:37'),
(11, 2, 'Veneer', 'dental_veneer', 'Thin porcelain or composite shell bonded to the front of teeth to improve aesthetics (color, shape, alignment).', 96.00, 'veneer.png', NULL, '2026-02-21 16:00:37'),
(12, 2, 'Full Mouth Rehab.', 'full_mouth_rehabilitation', 'Comprehensive restoration of all teeth using crowns, bridges, implants, veneers to restore function and aesthetics.', 94.00, 'full_mouth_rehab.png', NULL, '2026-02-21 16:00:37');

-- --------------------------------------------------------

--
-- Table structure for table `patient_medical_conditions`
--

CREATE TABLE `patient_medical_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `condition_name` varchar(100) NOT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_medical_conditions`
--

INSERT INTO `patient_medical_conditions` (`id`, `patient_id`, `condition_name`, `details`) VALUES
(1, 8, 'Diabetes', NULL),
(2, 8, 'Heart Disease', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patient_profiles`
--

CREATE TABLE `patient_profiles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `residential_address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_profiles`
--

INSERT INTO `patient_profiles` (`user_id`, `full_name`, `mobile_number`, `dob`, `gender`, `residential_address`, `city`, `state`, `pincode`, `allergies`, `created_at`, `updated_at`) VALUES
(8, 'm mohan Reddy', '1234567890', '2005-12-22', 'Male', 'house', 'chennai', 'ehjs', '727737', 'dot ', '2026-02-20 18:22:18', '2026-02-20 18:22:18');

-- --------------------------------------------------------

--
-- Table structure for table `procedure_benefits`
--

CREATE TABLE `procedure_benefits` (
  `id` int(11) NOT NULL,
  `operation_type_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procedure_benefits`
--

INSERT INTO `procedure_benefits` (`id`, `operation_type_id`, `title`, `description`, `display_order`) VALUES
(1, 1, 'Preserves Adjacent Teeth', 'No need to cut healthy neighboring teeth unlike bridge', 1),
(2, 1, 'Stops Bone Loss', 'Implant acts as natural root and prevents jawbone resorption', 2),
(3, 1, 'Natural Look & Feel', 'Looks, feels and functions exactly like your own tooth', 3),
(4, 1, 'Long Lasting', '95–98% success rate at 10+ years with proper care', 4),
(5, 1, 'High Confidence', 'Eat, speak and smile without worry', 5),
(6, 2, 'No Damage to Healthy Teeth', 'Implants replace missing teeth independently', 1),
(7, 2, 'Better Chewing Power', 'Stable and strong like natural teeth', 2),
(8, 2, 'Bone Preservation', 'Prevents further bone loss in the area', 3),
(9, 2, 'Aesthetic Restoration', 'Seamless smile with fixed crowns/bridge', 4),
(10, 3, 'Teeth in a Day', 'Fixed temporary teeth same day in most cases', 1),
(11, 3, 'Fixed Solution', 'No removable denture – feels permanent', 2),
(12, 3, 'Excellent Stability', '96–98% success rate', 3),
(13, 3, 'Restores Full Function', 'Eat anything you want', 4),
(14, 4, 'No Grinding Healthy Teeth', 'Supported only by implants', 1),
(15, 4, 'Strong & Durable', 'Better than traditional bridge', 2),
(16, 4, 'Bone Preservation', 'Maintains jawbone volume', 3),
(17, 5, 'No More Loose Denture', 'Snaps securely on implants', 1),
(18, 5, '80–90% Chewing Efficiency', 'Much better than conventional denture', 2),
(19, 5, 'Improved Speech & Confidence', 'Stable during talking and eating', 3),
(20, 6, 'Enables Implant Placement', 'Creates sufficient bone where it was lacking', 1),
(21, 6, 'Long-term Success', 'Up to 100% success when done correctly', 2),
(22, 6, 'Natural Bone Regeneration', 'Uses your own or advanced materials', 3),
(23, 7, 'Saves Damaged Tooth', 'Protects weak tooth from further damage', 1),
(24, 7, 'Restores Strength & Beauty', 'Looks and functions naturally', 2),
(25, 7, '10–15+ Years Durability', 'With good oral hygiene', 3),
(26, 8, 'Quick Solution', 'Faster and cheaper than implants', 1),
(27, 8, 'Restores Missing Teeth', 'Fixed and natural looking', 2),
(28, 9, 'Restores Smile Quickly', 'Full set of teeth in few visits', 1),
(29, 9, 'Affordable Option', 'When implants not possible', 2),
(30, 10, 'Preserves Remaining Teeth', 'Clasps on natural teeth', 1),
(31, 10, 'Removable & Easy to Clean', 'Convenient for patient', 2),
(32, 11, 'Dramatic Smile Makeover', 'Minimal tooth reduction', 1),
(33, 11, 'Stain Resistant', 'Beautiful long-lasting results', 2),
(34, 12, 'Complete Oral Restoration', 'Fixes function + aesthetics', 1),
(35, 12, 'Long-term Solution', 'Custom comprehensive plan', 2);

-- --------------------------------------------------------

--
-- Table structure for table `procedure_risks`
--

CREATE TABLE `procedure_risks` (
  `id` int(11) NOT NULL,
  `operation_type_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `risk_percentage` decimal(5,2) DEFAULT NULL,
  `display_order` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procedure_risks`
--

INSERT INTO `procedure_risks` (`id`, `operation_type_id`, `title`, `description`, `risk_percentage`, `display_order`) VALUES
(1, 1, 'Peri-implantitis (Infection)', 'Inflammation around implant', 5.00, 1),
(2, 1, 'Early Implant Failure', 'Before loading', 2.50, 2),
(3, 1, 'Nerve Damage / Sinus Issue', 'With proper planning', 0.80, 3),
(4, 2, 'Infection', 'Same as single', 5.50, 1),
(5, 2, 'Implant Failure', 'Slightly higher load', 3.50, 2),
(6, 3, 'Implant Failure', 'All-on-4/All-on-6', 2.80, 1),
(7, 3, 'Provisional Fracture', 'Temporary teeth', 8.00, 2),
(8, 4, 'Peri-implantitis', '', 5.00, 1),
(9, 4, 'Mechanical Complication', 'Screw loosening', 4.00, 2),
(10, 5, 'Attachment Wear', 'Need replacement', 15.00, 1),
(11, 5, 'Peri-implantitis', '', 6.00, 2),
(12, 6, 'Graft Failure', '', 5.00, 1),
(13, 6, 'Infection/Swelling', '', 4.50, 2),
(14, 7, 'Need for Root Canal', 'After preparation', 7.00, 1),
(15, 7, 'Crown Fracture', '', 4.00, 2),
(16, 8, 'Decay under Bridge', 'Over 10 years', 15.00, 1),
(17, 8, 'Bone Loss under Pontic', '', 20.00, 2),
(18, 9, 'Loose over time', 'Due to bone resorption', 60.00, 1),
(19, 9, 'Sore spots', 'Initial period', 40.00, 2),
(20, 10, 'Clasp loosening', '', 25.00, 1),
(21, 10, 'Bone loss under base', '', 30.00, 2),
(22, 11, 'Chipping', '', 4.00, 1),
(23, 11, 'Sensitivity', '', 8.00, 2),
(24, 12, 'Longer Treatment Time', '', 100.00, 1),
(25, 12, 'Higher Overall Cost', '', 100.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `procedure_steps`
--

CREATE TABLE `procedure_steps` (
  `id` int(11) NOT NULL,
  `operation_type_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `duration_note` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procedure_steps`
--

INSERT INTO `procedure_steps` (`id`, `operation_type_id`, `step_number`, `title`, `description`, `duration_note`, `created_at`) VALUES
(1, 1, 1, 'Consultation & Diagnostics', 'Thorough exam, medical history, X-rays, and CBCT scan to assess bone and plan implant position.', '45–90 min', '2026-02-21 16:02:50'),
(2, 1, 2, 'Tooth Extraction (if needed)', 'Gentle removal of damaged tooth/root to preserve bone.', '15–45 min', '2026-02-21 16:02:50'),
(3, 1, 3, 'Bone Grafting / Preparation (if required)', 'Augment bone volume if insufficient for implant stability.', '30–60 min', '2026-02-21 16:02:50'),
(4, 1, 4, 'Implant Placement Surgery', 'Titanium post surgically inserted into jawbone under local anesthesia.', '45–90 min', '2026-02-21 16:02:50'),
(5, 1, 5, 'Osseointegration & Healing', 'Implant fuses with bone; healing abutment or cover screw in place.', '3–6 months', '2026-02-21 16:02:50'),
(6, 1, 6, 'Abutment Placement', 'Expose implant and attach connector (abutment).', '20–40 min', '2026-02-21 16:02:50'),
(7, 1, 7, 'Impressions & Crown Fabrication', 'Digital/traditional impressions; lab fabricates custom crown.', '1–3 weeks', '2026-02-21 16:02:50'),
(8, 1, 8, 'Final Crown Delivery', 'Permanent crown cemented/screwed; occlusion and fit verified.', '30–60 min', '2026-02-21 16:02:50'),
(9, 1, 9, 'Follow-up Care', 'Regular hygiene checks and peri-implant monitoring.', 'Ongoing', '2026-02-21 16:02:50'),
(10, 2, 1, 'Consultation & 3D Planning', 'CBCT imaging and planning for 2–4 implant positions.', '60–120 min', '2026-02-21 16:02:50'),
(11, 2, 2, 'Extractions (if necessary)', 'Removal of failing teeth in the area.', '30–60 min', '2026-02-21 16:02:50'),
(12, 2, 3, 'Implant Placement', 'Surgical insertion of multiple implants to support bridge.', '90–180 min', '2026-02-21 16:02:50'),
(13, 2, 4, 'Healing Phase', 'Osseointegration with possible temporary restoration.', '3–6 months', '2026-02-21 16:02:50'),
(14, 2, 5, 'Abutment Connection', 'Attach multi-unit abutments.', '30–60 min', '2026-02-21 16:02:50'),
(15, 2, 6, 'Final Bridge Delivery', 'Custom multi-unit bridge seated and adjusted.', '60–90 min', '2026-02-21 16:02:50'),
(16, 3, 1, 'Comprehensive Planning & Imaging', 'CBCT, digital smile design, and guided surgery planning.', '60–120 min', '2026-02-21 16:02:50'),
(17, 3, 2, 'Extractions & Bone Preparation', 'Remove remaining teeth; contour bone if needed.', 'Part of surgery', '2026-02-21 16:02:50'),
(18, 3, 3, 'Implant Placement', 'Strategic placement of 4–6 angled implants for immediate load.', '2–4 hours', '2026-02-21 16:02:50'),
(19, 3, 4, 'Immediate Provisional Prosthesis', 'Attach fixed temporary bridge same day (Teeth-in-a-Day).', 'Same day', '2026-02-21 16:02:50'),
(20, 3, 5, 'Healing & Osseointegration', 'Bone integration while wearing provisional.', '3–6 months', '2026-02-21 16:02:50'),
(21, 3, 6, 'Final Prosthesis Delivery', 'Permanent fixed bridge/hybrid delivered and fine-tuned.', '2–3 visits', '2026-02-21 16:02:50'),
(22, 4, 1, 'Evaluation & Imaging', 'Assess bone and plan 2–3 implant positions.', '45–90 min', '2026-02-21 16:02:50'),
(23, 4, 2, 'Implant Placement Surgery', 'Insert implants to anchor the bridge.', '90–150 min', '2026-02-21 16:02:50'),
(24, 4, 3, 'Healing Period', 'Osseointegration with healing components.', '3–6 months', '2026-02-21 16:02:50'),
(25, 4, 4, 'Abutment & Impressions', 'Place multi-unit abutments; take impressions.', '45–60 min', '2026-02-21 16:02:50'),
(26, 4, 5, 'Final Bridge Delivery', 'Cement/seat permanent bridge; verify fit.', '60–90 min', '2026-02-21 16:02:50'),
(27, 5, 1, 'Consultation & Diagnostics', 'CBCT to plan 2–6 implant positions.', '60 min', '2026-02-21 16:02:50'),
(28, 5, 2, 'Implant Placement', 'Surgical placement of implants (Locator/bar system).', '1–3 hours', '2026-02-21 16:02:50'),
(29, 5, 3, 'Healing Phase', 'Osseointegration; use modified existing denture temporarily.', '3–6 months', '2026-02-21 16:02:50'),
(30, 5, 4, 'Abutment Placement & Impressions', 'Attach Locator/bar abutments; final impressions.', '45–90 min', '2026-02-21 16:02:50'),
(31, 5, 5, 'Jaw Records & Try-in', 'Bite registration, tooth setup, wax try-in.', '60–90 min', '2026-02-21 16:02:50'),
(32, 5, 6, 'Final Overdenture Delivery', 'Process attachments; deliver and adjust denture.', '60–90 min', '2026-02-21 16:02:50'),
(33, 6, 1, 'Assessment & CBCT', 'Evaluate defect and select graft type (autograft/allograft/etc.).', '45–60 min', '2026-02-21 16:02:50'),
(34, 6, 2, 'Surgical Preparation', 'Flap elevation and site cleaning.', 'Part of surgery', '2026-02-21 16:02:50'),
(35, 6, 3, 'Graft Placement', 'Apply graft material ± membrane for containment.', '45–120 min', '2026-02-21 16:02:50'),
(36, 6, 4, 'Closure & Initial Healing', 'Suture site; allow maturation.', '4–9 months', '2026-02-21 16:02:50'),
(37, 6, 5, 'Follow-up & Re-evaluation', 'Imaging to confirm integration before implants.', 'Ongoing', '2026-02-21 16:02:50'),
(38, 7, 1, 'Examination & Diagnosis', 'Assess tooth; X-rays to check vitality and structure.', '30–60 min', '2026-02-21 16:02:50'),
(39, 7, 2, 'Tooth Preparation', 'Anesthetic; reduce enamel/structure for crown space.', '45–90 min', '2026-02-21 16:02:50'),
(40, 7, 3, 'Impressions & Shade Selection', 'Digital scan or putty impression; match color.', '15–30 min', '2026-02-21 16:02:50'),
(41, 7, 4, 'Temporary Crown Placement', 'Protect tooth while permanent crown is fabricated.', 'Same day', '2026-02-21 16:02:50'),
(42, 7, 5, 'Permanent Crown Delivery', 'Remove temp; seat, adjust, and cement/bond crown.', '30–60 min', '2026-02-21 16:02:50'),
(43, 8, 1, 'Consultation & Planning', 'Evaluate abutment teeth and pontic space.', '45 min', '2026-02-21 16:02:50'),
(44, 8, 2, 'Abutment Preparation', 'Reduce/shape supporting teeth for crowns.', '60–120 min', '2026-02-21 16:02:50'),
(45, 8, 3, 'Impressions & Shade', 'Capture prepared teeth; select color.', '30 min', '2026-02-21 16:02:50'),
(46, 8, 4, 'Temporary Bridge Placement', 'Protect area with provisional bridge.', 'Same day', '2026-02-21 16:02:50'),
(47, 8, 5, 'Final Bridge Delivery', 'Cement permanent bridge; check occlusion.', '45–75 min', '2026-02-21 16:02:50'),
(48, 9, 1, 'Initial Impressions & Exam', 'Alginate impressions; oral health evaluation.', '45 min', '2026-02-21 16:02:50'),
(49, 9, 2, 'Custom Tray & Final Impressions', 'Border molding for accurate fit.', '60 min', '2026-02-21 16:02:50'),
(50, 9, 3, 'Jaw Relation & Wax Try-in', 'Vertical dimension, centric relation, tooth setup.', '60–90 min', '2026-02-21 16:02:50'),
(51, 9, 4, 'Aesthetic & Functional Try-in', 'Verify appearance, speech, and bite in wax.', '45 min', '2026-02-21 16:02:50'),
(52, 9, 5, 'Processing & Delivery', 'Lab processes acrylic; deliver and adjust.', '60 min', '2026-02-21 16:02:50'),
(53, 10, 1, 'Examination & Design', 'Assess remaining teeth; plan clasps/rests.', '45 min', '2026-02-21 16:02:50'),
(54, 10, 2, 'Impressions & Surveying', 'Detailed impressions; surveyor for RPD framework.', '60 min', '2026-02-21 16:02:50'),
(55, 10, 3, 'Framework Try-in', 'Check metal framework fit.', '30–45 min', '2026-02-21 16:02:50'),
(56, 10, 4, 'Tooth Setup & Wax Try-in', 'Arrange teeth; verify occlusion.', '45 min', '2026-02-21 16:02:50'),
(57, 10, 5, 'Delivery & Adjustments', 'Process acrylic; seat partial and fine-tune.', '60 min', '2026-02-21 16:02:50'),
(58, 11, 1, 'Consultation & Smile Design', 'Discuss goals; mock-up/digital preview.', '45–60 min', '2026-02-21 16:02:50'),
(59, 11, 2, 'Tooth Preparation', 'Minimal enamel reduction (0.3–0.7 mm) on facial surface.', '60–90 min', '2026-02-21 16:02:50'),
(60, 11, 3, 'Impressions & Shade Selection', 'Digital scan/impression; choose veneer color.', '30 min', '2026-02-21 16:02:50'),
(61, 11, 4, 'Temporary Veneers (optional)', 'Place provisionals for protection/aesthetics.', 'Same day', '2026-02-21 16:02:50'),
(62, 11, 5, 'Veneer Bonding', 'Etch, bond, cement veneers; polish and adjust.', '60–120 min', '2026-02-21 16:02:50'),
(63, 12, 1, 'Comprehensive Diagnosis', 'Full exam, photos, CBCT, models, records.', '90–180 min', '2026-02-21 16:02:50'),
(64, 12, 2, 'Treatment Planning & Mock-up', 'Wax-up/digital design for proposed restorations.', 'Multiple visits', '2026-02-21 16:02:50'),
(65, 12, 3, 'Preparatory Work', 'Extractions, endo, perio therapy as needed.', 'Varies', '2026-02-21 16:02:50'),
(66, 12, 4, 'Tooth Preparation & Provisionals', 'Prep teeth; place temporary crowns/bridges.', 'Several appointments', '2026-02-21 16:02:50'),
(67, 12, 5, 'Final Restorations Delivery', 'Seat permanent crowns/bridges/veneers; adjust bite.', 'Multiple visits', '2026-02-21 16:02:50'),
(68, 12, 6, 'Maintenance Phase', 'Occlusal adjustments, hygiene, nightguard.', 'Ongoing', '2026-02-21 16:02:50');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Implantology', 'Focuses on surgical placement and restoration of dental implants to replace missing teeth, including bone support procedures.', '2026-02-21 16:00:37'),
(2, 'Prosthodontics', 'Specialty dedicated to diagnosis, treatment planning, and restoration of oral function and aesthetics using crowns, bridges, dentures, veneers, and full-mouth rehabilitation.', '2026-02-21 16:00:37');

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `operation_type_id` int(11) DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `anesthesia_required` tinyint(1) DEFAULT 0,
  `anesthesia_pdf_url` varchar(255) DEFAULT NULL,
  `implant_pdf_url` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','in_progress','educated','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `clinical_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `treatments`
--

INSERT INTO `treatments` (`id`, `doctor_id`, `patient_id`, `operation_type_id`, `category`, `status`, `clinical_notes`, `created_at`, `updated_at`) VALUES
(9, 12, 8, NULL, 'Multiple Tooth Implant', 'consent_sent', 'multi', '2026-02-21 15:10:52', '2026-02-21 15:10:52'),
(10, 12, 8, 1, 'Single Tooth Implant', 'consent_sent', '', '2026-02-21 16:19:35', '2026-02-21 16:19:35'),
(11, 12, 8, 4, 'Implant-Supported Bridge', 'consent_sent', 'jaca', '2026-02-21 16:30:21', '2026-02-21 16:30:21'),
(12, 12, 8, 4, 'Implant-Supported Bridge', 'consent_sent', 'anavabsb\n', '2026-02-21 16:49:25', '2026-02-21 16:49:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','doctor','patient') NOT NULL DEFAULT 'patient',
  `profile_image` varchar(255) DEFAULT NULL,
  `fcm_token` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `profile_image`, `fcm_token`, `last_login`, `created_at`, `updated_at`) VALUES
(8, 'cm@cm.cm', '$2y$10$OC5wOkZslTVSNtGjTmnOMughH4ZlPSf8FeGHq7JTU7TudSqVz0Fai', 'patient', 'uploads/profile_images/dfac7b514ed3c96d9dd81c047c1fa591.jpg', NULL, '2026-02-21 22:33:25', '2026-02-20 18:22:18', '2026-02-21 17:03:25'),
(12, 'doctor@test.com', '$2y$10$dk/H.fUjYXiKeLY3DJYHy.CZf2lGPR2VCNO2gv9IxNxzZLEgKWzl6', 'doctor', 'uploads/profile_images/4e61603546374feba4acee1a2f8e5df6.jpg', NULL, '2026-02-21 22:15:23', '2026-02-21 12:21:07', '2026-02-21 16:45:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`);

--
-- Indexes for table `consent_records`
--
ALTER TABLE `consent_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `treatment_id` (`treatment_id`);

--
-- Indexes for table `doctor_profiles`
--
ALTER TABLE `doctor_profiles`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `council_id` (`council_id`);

--
-- Indexes for table `educational_videos`
--
ALTER TABLE `educational_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operation_type_id` (`operation_type_id`);

--
-- Indexes for table `key_topics`
--
ALTER TABLE `key_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operation_type_id` (`operation_type_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operation_types`
--
ALTER TABLE `operation_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Indexes for table `patient_medical_conditions`
--
ALTER TABLE `patient_medical_conditions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_condition_patient` (`patient_id`);

--
-- Indexes for table `patient_profiles`
--
ALTER TABLE `patient_profiles`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_patient_mobile` (`mobile_number`);

--
-- Indexes for table `procedure_benefits`
--
ALTER TABLE `procedure_benefits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operation_type_id` (`operation_type_id`);

--
-- Indexes for table `procedure_risks`
--
ALTER TABLE `procedure_risks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `operation_type_id` (`operation_type_id`);

--
-- Indexes for table `procedure_steps`
--
ALTER TABLE `procedure_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_step` (`operation_type_id`,`step_number`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_treatment_doctor` (`doctor_id`),
  ADD KEY `idx_treatment_patient` (`patient_id`),
  ADD KEY `fk_treatment_operation` (`operation_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consent_records`
--
ALTER TABLE `consent_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `educational_videos`
--
ALTER TABLE `educational_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `key_topics`
--
ALTER TABLE `key_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `operation_types`
--
ALTER TABLE `operation_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `patient_medical_conditions`
--
ALTER TABLE `patient_medical_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `procedure_benefits`
--
ALTER TABLE `procedure_benefits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `procedure_risks`
--
ALTER TABLE `procedure_risks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `procedure_steps`
--
ALTER TABLE `procedure_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `treatments`
--
ALTER TABLE `treatments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consent_records`
--
ALTER TABLE `consent_records`
  ADD CONSTRAINT `fk_consent_treatment` FOREIGN KEY (`treatment_id`) REFERENCES `treatments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_profiles`
--
ALTER TABLE `doctor_profiles`
  ADD CONSTRAINT `fk_doctor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `educational_videos`
--
ALTER TABLE `educational_videos`
  ADD CONSTRAINT `educational_videos_ibfk_1` FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types` (`id`);

--
-- Constraints for table `key_topics`
--
ALTER TABLE `key_topics`
  ADD CONSTRAINT `key_topics_ibfk_1` FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types` (`id`);

--
-- Constraints for table `operation_types`
--
ALTER TABLE `operation_types`
  ADD CONSTRAINT `operation_types_ibfk_1` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`);

--
-- Constraints for table `patient_medical_conditions`
--
ALTER TABLE `patient_medical_conditions`
  ADD CONSTRAINT `fk_medical_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient_profiles` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_profiles`
--
ALTER TABLE `patient_profiles`
  ADD CONSTRAINT `fk_patient_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `procedure_benefits`
--
ALTER TABLE `procedure_benefits`
  ADD CONSTRAINT `procedure_benefits_ibfk_1` FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types` (`id`);

--
-- Constraints for table `procedure_risks`
--
ALTER TABLE `procedure_risks`
  ADD CONSTRAINT `procedure_risks_ibfk_1` FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types` (`id`);

--
-- Constraints for table `procedure_steps`
--
ALTER TABLE `procedure_steps`
  ADD CONSTRAINT `procedure_steps_ibfk_1` FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types` (`id`);

--
-- Constraints for table `treatments`
--
ALTER TABLE `treatments`
  ADD CONSTRAINT `fk_treatment_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor_profiles` (`user_id`),
  ADD CONSTRAINT `fk_treatment_operation` FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types` (`id`),
  ADD CONSTRAINT `fk_treatment_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient_profiles` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
