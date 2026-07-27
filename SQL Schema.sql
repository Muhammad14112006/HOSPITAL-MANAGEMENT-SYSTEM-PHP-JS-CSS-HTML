-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 11:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `EnrollmentID` int(11) NOT NULL,
  `Date` date NOT NULL,
  `Diagnosis` text DEFAULT NULL,
  `Status` enum('Scheduled','Pending','Treated','Cancelled') DEFAULT 'Scheduled',
  `DoctorID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `booked_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`EnrollmentID`, `Date`, `Diagnosis`, `Status`, `DoctorID`, `PatientID`, `booked_by`) VALUES
(1, '2026-06-02', 'Diabetes Follow-up', 'Scheduled', 25, 1, NULL),
(2, '2026-06-02', 'High Blood Pressure', 'Scheduled', 16, 2, NULL),
(3, '2026-06-02', 'High Blood Pressure', 'Scheduled', 4, 3, NULL),
(4, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 34, 4, NULL),
(5, '2026-06-02', 'Routine Blood Test', 'Scheduled', 35, 5, NULL),
(6, '2026-06-02', 'Diabetes Follow-up', 'Scheduled', 14, 6, NULL),
(7, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 26, 7, NULL),
(8, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 26, 8, NULL),
(9, '2026-06-02', 'Flu Symptoms', 'Scheduled', 53, 9, NULL),
(10, '2026-06-02', 'Flu Symptoms', 'Scheduled', 4, 10, NULL),
(11, '2026-06-02', 'Skin Allergy', 'Scheduled', 44, 11, NULL),
(12, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 24, 12, NULL),
(13, '2026-06-02', 'General Checkup', 'Scheduled', 50, 13, NULL),
(14, '2026-06-02', 'High Blood Pressure', 'Scheduled', 57, 14, NULL),
(15, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 16, 15, NULL),
(16, '2026-06-02', 'Back Pain Consultation', 'Scheduled', 29, 16, NULL),
(17, '2026-06-02', 'Back Pain Consultation', 'Scheduled', 35, 17, NULL),
(18, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 27, 18, NULL),
(19, '2026-06-02', 'Diabetes Follow-up', 'Scheduled', 30, 19, NULL),
(20, '2026-06-02', 'General Checkup', 'Scheduled', 7, 20, NULL),
(21, '2026-06-02', 'High Blood Pressure', 'Scheduled', 4, 21, NULL),
(22, '2026-06-02', 'Diabetes Follow-up', 'Scheduled', 1, 22, NULL),
(23, '2026-06-02', 'Back Pain Consultation', 'Scheduled', 50, 23, NULL),
(24, '2026-06-02', 'Diabetes Follow-up', 'Scheduled', 9, 24, NULL),
(25, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 11, 25, NULL),
(26, '2026-06-02', 'Flu Symptoms', 'Scheduled', 31, 26, NULL),
(27, '2026-06-02', 'High Blood Pressure', 'Scheduled', 60, 27, NULL),
(28, '2026-06-02', 'High Blood Pressure', 'Scheduled', 25, 28, NULL),
(29, '2026-06-02', 'Flu Symptoms', 'Scheduled', 7, 29, NULL),
(30, '2026-06-02', 'High Blood Pressure', 'Scheduled', 19, 30, NULL),
(31, '2026-06-02', 'General Checkup', 'Scheduled', 15, 31, NULL),
(32, '2026-06-02', 'General Checkup', 'Scheduled', 18, 32, NULL),
(33, '2026-06-02', 'Flu Symptoms', 'Scheduled', 41, 33, NULL),
(34, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 34, 34, NULL),
(35, '2026-06-02', 'Diabetes Follow-up', 'Scheduled', 46, 35, NULL),
(36, '2026-06-02', 'Skin Allergy', 'Scheduled', 5, 36, NULL),
(37, '2026-06-02', 'Flu Symptoms', 'Scheduled', 10, 37, NULL),
(38, '2026-06-02', 'Skin Allergy', 'Scheduled', 33, 38, NULL),
(39, '2026-06-02', 'Routine Blood Test', 'Scheduled', 13, 39, NULL),
(40, '2026-06-02', 'General Checkup', 'Scheduled', 26, 40, NULL),
(41, '2026-06-02', 'Back Pain Consultation', 'Scheduled', 30, 41, NULL),
(42, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 12, 42, NULL),
(43, '2026-06-02', 'Back Pain Consultation', 'Scheduled', 32, 43, NULL),
(44, '2026-06-02', 'Routine Blood Test', 'Scheduled', 60, 44, NULL),
(45, '2026-06-02', 'High Blood Pressure', 'Scheduled', 25, 45, NULL),
(46, '2026-06-02', 'General Checkup', 'Scheduled', 6, 46, NULL),
(47, '2026-06-02', 'High Blood Pressure', 'Scheduled', 13, 47, NULL),
(48, '2026-06-02', 'High Blood Pressure', 'Scheduled', 47, 48, NULL),
(49, '2026-06-02', 'Routine Blood Test', 'Scheduled', 17, 49, NULL),
(50, '2026-06-02', 'General Checkup', 'Scheduled', 3, 50, NULL),
(51, '2026-06-02', 'Routine Blood Test', 'Scheduled', 25, 51, NULL),
(52, '2026-06-02', 'Routine Blood Test', 'Scheduled', 55, 52, NULL),
(53, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 17, 53, NULL),
(54, '2026-06-02', 'Chest Pain Evaluation', 'Scheduled', 40, 54, NULL),
(55, '2026-06-02', 'Flu Symptoms', 'Scheduled', 31, 55, NULL),
(56, '2026-06-02', 'Back Pain Consultation', 'Scheduled', 32, 56, NULL),
(57, '2026-06-02', 'Diabetes Follow-up', 'Scheduled', 9, 57, NULL),
(58, '2026-06-02', 'Flu Symptoms', 'Scheduled', 5, 58, NULL),
(59, '2026-06-02', 'Skin Allergy', 'Scheduled', 1, 59, NULL),
(60, '2026-06-02', 'Skin Allergy', 'Scheduled', 46, 60, NULL),
(61, '2026-06-09', 'Skin Allergy', 'Pending', 49, 1, NULL),
(62, '2026-06-09', 'Diabetes Follow-up', 'Pending', 47, 2, NULL),
(63, '2026-06-09', 'Routine Blood Test', 'Pending', 28, 3, NULL),
(64, '2026-06-09', 'Back Pain Consultation', 'Pending', 58, 4, NULL),
(65, '2026-06-09', 'Back Pain Consultation', 'Pending', 25, 5, NULL),
(66, '2026-06-09', 'High Blood Pressure', 'Pending', 12, 6, NULL),
(67, '2026-06-09', 'Diabetes Follow-up', 'Pending', 44, 7, NULL),
(68, '2026-06-09', 'Diabetes Follow-up', 'Pending', 4, 8, NULL),
(69, '2026-06-09', 'Skin Allergy', 'Pending', 8, 9, NULL),
(70, '2026-06-09', 'General Checkup', 'Pending', 26, 10, NULL),
(71, '2026-06-09', 'General Checkup', 'Pending', 47, 11, NULL),
(72, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 34, 12, NULL),
(73, '2026-06-09', 'High Blood Pressure', 'Pending', 29, 13, NULL),
(74, '2026-06-09', 'Back Pain Consultation', 'Pending', 41, 14, NULL),
(75, '2026-06-09', 'Skin Allergy', 'Pending', 1, 15, NULL),
(76, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 58, 16, NULL),
(77, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 50, 17, NULL),
(78, '2026-06-09', 'Diabetes Follow-up', 'Pending', 13, 18, NULL),
(79, '2026-06-09', 'Diabetes Follow-up', 'Pending', 36, 19, NULL),
(80, '2026-06-09', 'Skin Allergy', 'Pending', 19, 20, NULL),
(81, '2026-06-09', 'Diabetes Follow-up', 'Pending', 46, 21, NULL),
(82, '2026-06-09', 'Back Pain Consultation', 'Pending', 53, 22, NULL),
(83, '2026-06-09', 'Diabetes Follow-up', 'Pending', 5, 23, NULL),
(84, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 46, 24, NULL),
(85, '2026-06-09', 'Flu Symptoms', 'Pending', 33, 25, NULL),
(86, '2026-06-09', 'Diabetes Follow-up', 'Pending', 29, 26, NULL),
(87, '2026-06-09', 'Skin Allergy', 'Pending', 45, 27, NULL),
(88, '2026-06-09', 'Flu Symptoms', 'Pending', 17, 28, NULL),
(89, '2026-06-09', 'Routine Blood Test', 'Pending', 12, 29, NULL),
(90, '2026-06-09', 'Skin Allergy', 'Pending', 6, 30, NULL),
(91, '2026-06-09', 'Skin Allergy', 'Pending', 54, 31, NULL),
(92, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 14, 32, NULL),
(93, '2026-06-09', 'Routine Blood Test', 'Pending', 25, 33, NULL),
(94, '2026-06-09', 'Diabetes Follow-up', 'Pending', 21, 34, NULL),
(95, '2026-06-09', 'Skin Allergy', 'Pending', 33, 35, NULL),
(96, '2026-06-09', 'Back Pain Consultation', 'Pending', 39, 36, NULL),
(97, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 36, 37, NULL),
(98, '2026-06-09', 'General Checkup', 'Pending', 5, 38, NULL),
(99, '2026-06-09', 'Routine Blood Test', 'Pending', 33, 39, NULL),
(100, '2026-06-09', 'Skin Allergy', 'Pending', 33, 40, NULL),
(101, '2026-06-09', 'Back Pain Consultation', 'Pending', 2, 41, NULL),
(102, '2026-06-09', 'Skin Allergy', 'Pending', 33, 42, NULL),
(103, '2026-06-09', 'Skin Allergy', 'Pending', 39, 43, NULL),
(104, '2026-06-09', 'General Checkup', 'Pending', 34, 44, NULL),
(105, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 52, 45, NULL),
(106, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 39, 46, NULL),
(107, '2026-06-09', 'Routine Blood Test', 'Pending', 40, 47, NULL),
(108, '2026-06-09', 'Skin Allergy', 'Pending', 20, 48, NULL),
(109, '2026-06-09', 'General Checkup', 'Pending', 39, 49, NULL),
(110, '2026-06-09', 'General Checkup', 'Pending', 15, 50, NULL),
(111, '2026-06-09', 'Flu Symptoms', 'Pending', 16, 51, NULL),
(112, '2026-06-09', 'Routine Blood Test', 'Pending', 36, 52, NULL),
(113, '2026-06-09', 'Chest Pain Evaluation', 'Pending', 11, 53, NULL),
(114, '2026-06-09', 'High Blood Pressure', 'Pending', 6, 54, NULL),
(115, '2026-06-09', 'General Checkup', 'Pending', 55, 55, NULL),
(116, '2026-06-09', 'Flu Symptoms', 'Pending', 20, 56, NULL),
(117, '2026-06-09', 'General Checkup', 'Pending', 52, 57, NULL),
(118, '2026-06-09', 'Diabetes Follow-up', 'Pending', 20, 58, NULL),
(119, '2026-06-09', 'Flu Symptoms', 'Pending', 4, 59, NULL),
(120, '2026-06-09', 'Skin Allergy', 'Pending', 18, 60, NULL),
(121, '2026-06-16', 'Routine Blood Test', 'Treated', 20, 1, NULL),
(122, '2026-06-16', 'Flu Symptoms', 'Treated', 44, 2, NULL),
(123, '2026-06-16', 'Flu Symptoms', 'Treated', 41, 3, NULL),
(124, '2026-06-16', 'Diabetes Follow-up', 'Treated', 11, 4, NULL),
(125, '2026-06-16', 'Back Pain Consultation', 'Treated', 52, 5, NULL),
(126, '2026-06-16', 'General Checkup', 'Treated', 47, 6, NULL),
(127, '2026-06-16', 'Skin Allergy', 'Treated', 19, 7, NULL),
(128, '2026-06-16', 'Back Pain Consultation', 'Treated', 13, 8, NULL),
(129, '2026-06-16', 'Skin Allergy', 'Treated', 6, 9, NULL),
(130, '2026-06-16', 'Routine Blood Test', 'Treated', 53, 10, NULL),
(131, '2026-06-16', 'Back Pain Consultation', 'Treated', 5, 11, NULL),
(132, '2026-06-16', 'Diabetes Follow-up', 'Treated', 46, 12, NULL),
(133, '2026-06-16', 'High Blood Pressure', 'Treated', 36, 13, NULL),
(134, '2026-06-16', 'Flu Symptoms', 'Treated', 40, 14, NULL),
(135, '2026-06-16', 'Routine Blood Test', 'Treated', 31, 15, NULL),
(136, '2026-06-16', 'Skin Allergy', 'Treated', 33, 16, NULL),
(137, '2026-06-16', 'Back Pain Consultation', 'Treated', 14, 17, NULL),
(138, '2026-06-16', 'Diabetes Follow-up', 'Treated', 30, 18, NULL),
(139, '2026-06-16', 'High Blood Pressure', 'Treated', 48, 19, NULL),
(140, '2026-06-16', 'High Blood Pressure', 'Treated', 28, 20, NULL),
(141, '2026-06-16', 'Skin Allergy', 'Treated', 55, 21, NULL),
(142, '2026-06-16', 'Diabetes Follow-up', 'Treated', 11, 22, NULL),
(143, '2026-06-16', 'Diabetes Follow-up', 'Treated', 9, 23, NULL),
(144, '2026-06-16', 'Diabetes Follow-up', 'Treated', 14, 24, NULL),
(145, '2026-06-16', 'General Checkup', 'Treated', 42, 25, NULL),
(146, '2026-06-16', 'Routine Blood Test', 'Treated', 46, 26, NULL),
(147, '2026-06-16', 'High Blood Pressure', 'Treated', 45, 27, NULL),
(148, '2026-06-16', 'Skin Allergy', 'Treated', 25, 28, NULL),
(149, '2026-06-16', 'Skin Allergy', 'Treated', 51, 29, NULL),
(150, '2026-06-16', 'Flu Symptoms', 'Treated', 58, 30, NULL),
(151, '2026-06-16', 'Flu Symptoms', 'Treated', 15, 31, NULL),
(152, '2026-06-16', 'Flu Symptoms', 'Treated', 22, 32, NULL),
(153, '2026-06-16', 'Diabetes Follow-up', 'Treated', 4, 33, NULL),
(154, '2026-06-16', 'Routine Blood Test', 'Treated', 13, 34, NULL),
(155, '2026-06-16', 'Chest Pain Evaluation', 'Treated', 52, 35, NULL),
(156, '2026-06-16', 'Flu Symptoms', 'Treated', 42, 36, NULL),
(157, '2026-06-16', 'Chest Pain Evaluation', 'Treated', 54, 37, NULL),
(158, '2026-06-16', 'High Blood Pressure', 'Treated', 24, 38, NULL),
(159, '2026-06-16', 'Chest Pain Evaluation', 'Treated', 19, 39, NULL),
(160, '2026-06-16', 'Routine Blood Test', 'Treated', 20, 40, NULL),
(161, '2026-06-16', 'High Blood Pressure', 'Treated', 41, 41, NULL),
(162, '2026-06-16', 'Diabetes Follow-up', 'Treated', 28, 42, NULL),
(163, '2026-06-16', 'High Blood Pressure', 'Treated', 15, 43, NULL),
(164, '2026-06-16', 'Diabetes Follow-up', 'Treated', 52, 44, NULL),
(165, '2026-06-16', 'Routine Blood Test', 'Treated', 35, 45, NULL),
(166, '2026-06-16', 'Routine Blood Test', 'Treated', 16, 46, NULL),
(167, '2026-06-16', 'Routine Blood Test', 'Treated', 35, 47, NULL),
(168, '2026-06-16', 'Skin Allergy', 'Treated', 5, 48, NULL),
(169, '2026-06-16', 'Skin Allergy', 'Treated', 42, 49, NULL),
(170, '2026-06-16', 'Chest Pain Evaluation', 'Treated', 12, 50, NULL),
(171, '2026-06-16', 'Skin Allergy', 'Treated', 55, 51, NULL),
(172, '2026-06-16', 'Back Pain Consultation', 'Treated', 59, 52, NULL),
(173, '2026-06-16', 'High Blood Pressure', 'Treated', 8, 53, NULL),
(174, '2026-06-16', 'Back Pain Consultation', 'Treated', 42, 54, NULL),
(175, '2026-06-16', 'Chest Pain Evaluation', 'Treated', 5, 55, NULL),
(176, '2026-06-16', 'High Blood Pressure', 'Treated', 21, 56, NULL),
(177, '2026-06-16', 'Chest Pain Evaluation', 'Treated', 29, 57, NULL),
(178, '2026-06-16', 'Flu Symptoms', 'Treated', 20, 58, NULL),
(179, '2026-06-16', 'General Checkup', 'Treated', 15, 59, NULL),
(180, '2026-06-16', 'Chest Pain Evaluation', 'Treated', 14, 60, NULL),
(256, '2026-06-03', 'I have Headache from many days', 'Scheduled', 8, 61, 4),
(257, '2026-06-03', 'I have cough from many days', 'Scheduled', 8, 62, 4),
(259, '2026-06-04', 'I  stucked with a  heart problem', 'Treated', 15, 62, NULL),
(260, '2026-06-10', 'I have flu', 'Scheduled', 15, 62, NULL),
(261, '2026-06-03', 'I have Heart  problem from several days', 'Scheduled', 15, 63, 4);

--
-- Triggers `appointments`
--
DELIMITER $$
CREATE TRIGGER `trg_appointment_update` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN
  IF OLD.Status <> NEW.Status THEN
    INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
    VALUES ('appointments', NEW.EnrollmentID, 'UPDATE', 'Status', OLD.Status, NEW.Status);
  END IF;
  IF IFNULL(OLD.Diagnosis,'') <> IFNULL(NEW.Diagnosis,'') THEN
    INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
    VALUES ('appointments', NEW.EnrollmentID, 'UPDATE', 'Diagnosis', OLD.Diagnosis, NEW.Diagnosis);
  END IF;
  IF OLD.Status <> 'Cancelled' AND NEW.Status = 'Cancelled' THEN
    UPDATE bills SET PaymentStatus = 'Partial'
    WHERE EnrollmentID = NEW.EnrollmentID AND PaymentStatus = 'Pending';
    INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
    VALUES ('bills', NEW.EnrollmentID, 'UPDATE', 'PaymentStatus (auto-void)', 'Pending', 'Partial');
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_auto_bill_on_appointment` AFTER INSERT ON `appointments` FOR EACH ROW BEGIN
  DECLARE v_fee DECIMAL(10,2) DEFAULT 0;
  SELECT COALESCE(appointment_fee, 0) INTO v_fee
  FROM doctors WHERE id = NEW.DoctorID;
  IF v_fee > 0 THEN
    INSERT INTO bills (TotalAmount, PaymentStatus, BillDate, EnrollmentID, PatientID)
    VALUES (v_fee, 'Pending', CURDATE(), NEW.EnrollmentID, NEW.PatientID);
    INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
    VALUES ('bills', NEW.EnrollmentID, 'INSERT', 'auto_bill_created', NULL,
            CONCAT('Auto bill Rs.', v_fee, ' for Appt#', NEW.EnrollmentID));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_auto_room_available_on_discharge` AFTER UPDATE ON `appointments` FOR EACH ROW BEGIN
  IF OLD.Status NOT IN ('Treated','Cancelled') AND NEW.Status IN ('Treated','Cancelled') THEN
    UPDATE rooms SET RoomStatus = 'Available', assignedTo = NULL
    WHERE assignedTo = NEW.PatientID AND RoomStatus = 'Occupied';
    IF ROW_COUNT() > 0 THEN
      INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
      VALUES ('rooms', NEW.PatientID, 'UPDATE', 'RoomStatus (auto-freed)',
              'Occupied', CONCAT('Available — Pat#', NEW.PatientID));
    END IF;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validate_appointment_date` BEFORE INSERT ON `appointments` FOR EACH ROW BEGIN
  IF NEW.Date < DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Appointment date is too far in the past (max 30 days back).';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `table_name` varchar(50) NOT NULL COMMENT 'Which table was affected',
  `record_id` int(11) DEFAULT NULL COMMENT 'PK of the affected row',
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `field_changed` varchar(100) DEFAULT NULL COMMENT 'Column that changed',
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `triggered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auto-populated by database triggers — do not edit manually';

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `table_name`, `record_id`, `action`, `field_changed`, `old_value`, `new_value`, `triggered_at`) VALUES
(1, 'bills', 256, 'INSERT', 'auto_bill', NULL, 'Fee=800.00 for EnrollmentID=256', '2026-06-02 16:44:53'),
(2, 'bills', 271, 'UPDATE', 'PaymentStatus', 'Pending (Rs.800.00)', 'Paid (Rs.800.00)', '2026-06-02 17:21:58'),
(3, 'bills', 266, 'UPDATE', 'PaymentStatus', 'Pending (Rs.19200.00)', 'Paid (Rs.19200.00)', '2026-06-02 17:22:07'),
(4, 'bills', 268, 'UPDATE', 'PaymentStatus', 'Pending (Rs.84000.00)', 'Paid (Rs.84000.00)', '2026-06-02 17:22:14'),
(5, 'bills', 270, 'UPDATE', 'PaymentStatus', 'Pending (Rs.44000.00)', 'Paid (Rs.44000.00)', '2026-06-02 17:22:34'),
(6, 'bills', 267, 'UPDATE', 'PaymentStatus', 'Pending (Rs.25500.00)', 'Paid (Rs.25500.00)', '2026-06-02 17:23:43'),
(7, 'patients', 62, 'INSERT', 'New patient registered', NULL, 'Name:Tayyab | Gender:M | Phone:03492898538', '2026-06-02 17:27:23'),
(8, 'bills', 257, 'INSERT', 'auto_bill_created', NULL, 'Auto bill of Rs.800.00 for EnrollmentID=257', '2026-06-02 17:28:08'),
(9, 'rooms', 1, 'UPDATE', 'RoomStatus', 'Occupied [General]', 'Available [General]', '2026-06-02 21:53:53'),
(10, 'rooms', 4, 'UPDATE', 'RoomStatus', 'Occupied [Semi-Private]', 'Available [Semi-Private]', '2026-06-02 21:54:00'),
(11, 'bills', 272, 'UPDATE', 'PaymentStatus', 'Pending Rs.800.00', 'Paid Rs.800.00', '2026-06-02 21:54:13'),
(12, 'bills', 265, 'UPDATE', 'PaymentStatus', 'Pending Rs.65000.00', 'Partial Rs.65000.00', '2026-06-02 21:54:32'),
(13, 'bills', 264, 'UPDATE', 'PaymentStatus', 'Pending Rs.15000.00', 'Partial Rs.15000.00', '2026-06-02 21:54:36'),
(14, 'bills', 262, 'UPDATE', 'PaymentStatus', 'Pending Rs.28500.00', 'Partial Rs.28500.00', '2026-06-02 21:54:40'),
(15, 'bills', 261, 'UPDATE', 'PaymentStatus', 'Pending Rs.33000.00', 'Partial Rs.33000.00', '2026-06-02 21:54:41'),
(16, 'bills', 260, 'UPDATE', 'PaymentStatus', 'Pending Rs.12000.00', 'Partial Rs.12000.00', '2026-06-02 21:54:43'),
(17, 'bills', 259, 'INSERT', 'auto_bill_created', NULL, 'Auto bill Rs.1000.00 for Appt#259', '2026-06-03 05:12:31'),
(26, 'appointments', 259, 'UPDATE', 'Status', 'Scheduled', 'Treated', '2026-06-03 05:41:30'),
(27, 'appointments', 259, 'UPDATE', 'Status (auto via prescription)', 'Scheduled', 'Treated', '2026-06-03 05:41:30'),
(28, 'prescription_items', 5, 'INSERT', 'Medicine prescribed', NULL, 'Rx#5 Med:Pantoprazole Dosage:Thrice a day', '2026-06-03 05:41:30'),
(29, 'bills', 260, 'INSERT', 'auto_bill_created', NULL, 'Auto bill Rs.1000.00 for Appt#260', '2026-06-03 05:42:44'),
(30, 'patients', 63, 'INSERT', 'New patient registered', NULL, 'Name:Naseer Gender:M Phone:03123456789', '2026-06-03 05:51:23'),
(31, 'bills', 261, 'INSERT', 'auto_bill_created', NULL, 'Auto bill Rs.1000.00 for Appt#261', '2026-06-03 05:52:48'),
(32, 'rooms', 18, 'UPDATE', 'RoomStatus', 'Available [Semi-Private]', 'Occupied [Semi-Private] Pat#63', '2026-06-03 05:53:06'),
(33, 'rooms', 11, 'UPDATE', 'RoomStatus', 'Occupied [General]', 'Available [General]', '2026-06-03 05:53:17'),
(34, 'bills', 276, 'UPDATE', 'PaymentStatus', 'Pending Rs.7200.00', 'Paid Rs.7200.00', '2026-06-03 05:53:25'),
(35, 'rooms', 3, 'UPDATE', 'RoomStatus', 'Occupied [ICU]', 'Available [ICU]', '2026-06-03 05:54:04'),
(36, 'rooms', 15, 'UPDATE', 'RoomStatus', 'Occupied [Emergency]', 'Available [Emergency]', '2026-06-03 06:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `BillID` int(11) NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `PaymentStatus` enum('Pending','Paid','Partial','Unpaid') DEFAULT 'Pending',
  `BillDate` date NOT NULL,
  `EnrollmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `RoomID` int(11) DEFAULT NULL,
  `nights` int(11) DEFAULT 1,
  `generated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`BillID`, `TotalAmount`, `PaymentStatus`, `BillDate`, `EnrollmentID`, `PatientID`, `RoomID`, `nights`, `generated_by`) VALUES
(1, 500.00, 'Pending', '2026-06-02', 22, 22, NULL, 1, NULL),
(2, 500.00, 'Pending', '2026-06-02', 59, 59, NULL, 1, NULL),
(3, 500.00, 'Pending', '2026-06-09', 75, 15, NULL, 1, NULL),
(4, 750.00, 'Pending', '2026-06-09', 101, 41, NULL, 1, NULL),
(5, 850.00, 'Pending', '2026-06-02', 50, 50, NULL, 1, NULL),
(6, 700.00, 'Pending', '2026-06-02', 3, 3, NULL, 1, NULL),
(7, 700.00, 'Pending', '2026-06-02', 10, 10, NULL, 1, NULL),
(8, 700.00, 'Pending', '2026-06-02', 21, 21, NULL, 1, NULL),
(9, 700.00, 'Pending', '2026-06-09', 68, 8, NULL, 1, NULL),
(10, 700.00, 'Pending', '2026-06-09', 119, 59, NULL, 1, NULL),
(11, 700.00, 'Pending', '2026-06-16', 153, 33, NULL, 1, NULL),
(12, 900.00, 'Pending', '2026-06-02', 36, 36, NULL, 1, NULL),
(13, 900.00, 'Pending', '2026-06-02', 58, 58, NULL, 1, NULL),
(14, 900.00, 'Pending', '2026-06-09', 83, 23, NULL, 1, NULL),
(15, 900.00, 'Pending', '2026-06-09', 98, 38, NULL, 1, NULL),
(16, 900.00, 'Pending', '2026-06-16', 131, 11, NULL, 1, NULL),
(17, 900.00, 'Pending', '2026-06-16', 168, 48, NULL, 1, NULL),
(18, 900.00, 'Pending', '2026-06-16', 175, 55, NULL, 1, NULL),
(19, 650.00, 'Pending', '2026-06-02', 46, 46, NULL, 1, NULL),
(20, 650.00, 'Pending', '2026-06-09', 90, 30, NULL, 1, NULL),
(21, 650.00, 'Pending', '2026-06-09', 114, 54, NULL, 1, NULL),
(22, 650.00, 'Pending', '2026-06-16', 129, 9, NULL, 1, NULL),
(23, 550.00, 'Pending', '2026-06-02', 20, 20, NULL, 1, NULL),
(24, 550.00, 'Pending', '2026-06-02', 29, 29, NULL, 1, NULL),
(25, 800.00, 'Pending', '2026-06-09', 69, 9, NULL, 1, NULL),
(26, 800.00, 'Pending', '2026-06-16', 173, 53, NULL, 1, NULL),
(27, 750.00, 'Pending', '2026-06-02', 24, 24, NULL, 1, NULL),
(28, 750.00, 'Pending', '2026-06-02', 57, 57, NULL, 1, NULL),
(29, 750.00, 'Pending', '2026-06-16', 143, 23, NULL, 1, NULL),
(30, 600.00, 'Pending', '2026-06-02', 37, 37, NULL, 1, NULL),
(31, 850.00, 'Pending', '2026-06-02', 25, 25, NULL, 1, NULL),
(32, 850.00, 'Pending', '2026-06-09', 113, 53, NULL, 1, NULL),
(33, 850.00, 'Pending', '2026-06-16', 124, 4, NULL, 1, NULL),
(34, 850.00, 'Pending', '2026-06-16', 142, 22, NULL, 1, NULL),
(35, 700.00, 'Pending', '2026-06-02', 42, 42, NULL, 1, NULL),
(36, 700.00, 'Pending', '2026-06-09', 66, 6, NULL, 1, NULL),
(37, 700.00, 'Pending', '2026-06-09', 89, 29, NULL, 1, NULL),
(38, 700.00, 'Pending', '2026-06-16', 170, 50, NULL, 1, NULL),
(39, 900.00, 'Pending', '2026-06-02', 39, 39, NULL, 1, NULL),
(40, 900.00, 'Pending', '2026-06-02', 47, 47, NULL, 1, NULL),
(41, 900.00, 'Pending', '2026-06-09', 78, 18, NULL, 1, NULL),
(42, 900.00, 'Pending', '2026-06-16', 128, 8, NULL, 1, NULL),
(43, 900.00, 'Pending', '2026-06-16', 154, 34, NULL, 1, NULL),
(44, 950.00, 'Pending', '2026-06-02', 6, 6, NULL, 1, NULL),
(45, 950.00, 'Pending', '2026-06-09', 92, 32, NULL, 1, NULL),
(46, 950.00, 'Pending', '2026-06-16', 137, 17, NULL, 1, NULL),
(47, 950.00, 'Pending', '2026-06-16', 144, 24, NULL, 1, NULL),
(48, 950.00, 'Pending', '2026-06-16', 180, 60, NULL, 1, NULL),
(49, 1000.00, 'Pending', '2026-06-02', 31, 31, NULL, 1, NULL),
(50, 1000.00, 'Pending', '2026-06-09', 110, 50, NULL, 1, NULL),
(51, 1000.00, 'Pending', '2026-06-16', 151, 31, NULL, 1, NULL),
(52, 1000.00, 'Pending', '2026-06-16', 163, 43, NULL, 1, NULL),
(53, 1000.00, 'Pending', '2026-06-16', 179, 59, NULL, 1, NULL),
(54, 800.00, 'Pending', '2026-06-02', 2, 2, NULL, 1, NULL),
(55, 800.00, 'Pending', '2026-06-02', 15, 15, NULL, 1, NULL),
(56, 800.00, 'Pending', '2026-06-09', 111, 51, NULL, 1, NULL),
(57, 800.00, 'Pending', '2026-06-16', 166, 46, NULL, 1, NULL),
(58, 500.00, 'Pending', '2026-06-02', 49, 49, NULL, 1, NULL),
(59, 500.00, 'Pending', '2026-06-02', 53, 53, NULL, 1, NULL),
(60, 500.00, 'Pending', '2026-06-09', 88, 28, NULL, 1, NULL),
(61, 750.00, 'Pending', '2026-06-02', 32, 32, NULL, 1, NULL),
(62, 750.00, 'Pending', '2026-06-09', 120, 60, NULL, 1, NULL),
(63, 500.00, 'Pending', '2026-06-02', 30, 30, NULL, 1, NULL),
(64, 500.00, 'Pending', '2026-06-09', 80, 20, NULL, 1, NULL),
(65, 500.00, 'Pending', '2026-06-16', 127, 7, NULL, 1, NULL),
(66, 500.00, 'Pending', '2026-06-16', 159, 39, NULL, 1, NULL),
(67, 600.00, 'Pending', '2026-06-09', 108, 48, NULL, 1, NULL),
(68, 600.00, 'Pending', '2026-06-09', 116, 56, NULL, 1, NULL),
(69, 600.00, 'Pending', '2026-06-09', 118, 58, NULL, 1, NULL),
(70, 600.00, 'Pending', '2026-06-16', 121, 1, NULL, 1, NULL),
(71, 600.00, 'Pending', '2026-06-16', 160, 40, NULL, 1, NULL),
(72, 600.00, 'Pending', '2026-06-16', 178, 58, NULL, 1, NULL),
(73, 550.00, 'Pending', '2026-06-09', 94, 34, NULL, 1, NULL),
(74, 550.00, 'Pending', '2026-06-16', 176, 56, NULL, 1, NULL),
(75, 850.00, 'Pending', '2026-06-16', 152, 32, NULL, 1, NULL),
(76, 700.00, 'Pending', '2026-06-02', 12, 12, NULL, 1, NULL),
(77, 700.00, 'Pending', '2026-06-16', 158, 38, NULL, 1, NULL),
(78, 900.00, 'Pending', '2026-06-02', 1, 1, NULL, 1, NULL),
(79, 900.00, 'Pending', '2026-06-02', 28, 28, NULL, 1, NULL),
(80, 900.00, 'Pending', '2026-06-02', 45, 45, NULL, 1, NULL),
(81, 900.00, 'Pending', '2026-06-02', 51, 51, NULL, 1, NULL),
(82, 900.00, 'Pending', '2026-06-09', 65, 5, NULL, 1, NULL),
(83, 900.00, 'Pending', '2026-06-09', 93, 33, NULL, 1, NULL),
(84, 900.00, 'Pending', '2026-06-16', 148, 28, NULL, 1, NULL),
(85, 900.00, 'Pending', '2026-06-02', 7, 7, NULL, 1, NULL),
(86, 900.00, 'Pending', '2026-06-02', 8, 8, NULL, 1, NULL),
(87, 900.00, 'Pending', '2026-06-02', 40, 40, NULL, 1, NULL),
(88, 900.00, 'Pending', '2026-06-09', 70, 10, NULL, 1, NULL),
(89, 750.00, 'Pending', '2026-06-02', 18, 18, NULL, 1, NULL),
(90, 950.00, 'Pending', '2026-06-09', 63, 3, NULL, 1, NULL),
(91, 950.00, 'Pending', '2026-06-16', 140, 20, NULL, 1, NULL),
(92, 950.00, 'Pending', '2026-06-16', 162, 42, NULL, 1, NULL),
(93, 500.00, 'Pending', '2026-06-02', 16, 16, NULL, 1, NULL),
(94, 500.00, 'Pending', '2026-06-09', 73, 13, NULL, 1, NULL),
(95, 500.00, 'Pending', '2026-06-09', 86, 26, NULL, 1, NULL),
(96, 500.00, 'Pending', '2026-06-16', 177, 57, NULL, 1, NULL),
(97, 1000.00, 'Pending', '2026-06-02', 19, 19, NULL, 1, NULL),
(98, 1000.00, 'Pending', '2026-06-02', 41, 41, NULL, 1, NULL),
(99, 1000.00, 'Pending', '2026-06-16', 138, 18, NULL, 1, NULL),
(100, 500.00, 'Pending', '2026-06-02', 26, 26, NULL, 1, NULL),
(101, 500.00, 'Pending', '2026-06-02', 55, 55, NULL, 1, NULL),
(102, 500.00, 'Pending', '2026-06-16', 135, 15, NULL, 1, NULL),
(103, 800.00, 'Pending', '2026-06-02', 43, 43, NULL, 1, NULL),
(104, 800.00, 'Pending', '2026-06-02', 56, 56, NULL, 1, NULL),
(105, 850.00, 'Pending', '2026-06-02', 38, 38, NULL, 1, NULL),
(106, 850.00, 'Pending', '2026-06-09', 85, 25, NULL, 1, NULL),
(107, 850.00, 'Pending', '2026-06-09', 95, 35, NULL, 1, NULL),
(108, 850.00, 'Pending', '2026-06-09', 99, 39, NULL, 1, NULL),
(109, 850.00, 'Pending', '2026-06-09', 100, 40, NULL, 1, NULL),
(110, 850.00, 'Pending', '2026-06-09', 102, 42, NULL, 1, NULL),
(111, 850.00, 'Pending', '2026-06-16', 136, 16, NULL, 1, NULL),
(112, 750.00, 'Pending', '2026-06-02', 4, 4, NULL, 1, NULL),
(113, 750.00, 'Pending', '2026-06-02', 34, 34, NULL, 1, NULL),
(114, 750.00, 'Pending', '2026-06-09', 72, 12, NULL, 1, NULL),
(115, 750.00, 'Pending', '2026-06-09', 104, 44, NULL, 1, NULL),
(116, 550.00, 'Pending', '2026-06-02', 5, 5, NULL, 1, NULL),
(117, 550.00, 'Pending', '2026-06-02', 17, 17, NULL, 1, NULL),
(118, 550.00, 'Pending', '2026-06-16', 165, 45, NULL, 1, NULL),
(119, 550.00, 'Pending', '2026-06-16', 167, 47, NULL, 1, NULL),
(120, 700.00, 'Pending', '2026-06-09', 79, 19, NULL, 1, NULL),
(121, 700.00, 'Pending', '2026-06-09', 97, 37, NULL, 1, NULL),
(122, 700.00, 'Pending', '2026-06-09', 112, 52, NULL, 1, NULL),
(123, 700.00, 'Pending', '2026-06-16', 133, 13, NULL, 1, NULL),
(124, 900.00, 'Pending', '2026-06-09', 96, 36, NULL, 1, NULL),
(125, 900.00, 'Pending', '2026-06-09', 103, 43, NULL, 1, NULL),
(126, 900.00, 'Pending', '2026-06-09', 106, 46, NULL, 1, NULL),
(127, 900.00, 'Pending', '2026-06-09', 109, 49, NULL, 1, NULL),
(128, 600.00, 'Pending', '2026-06-02', 54, 54, NULL, 1, NULL),
(129, 600.00, 'Pending', '2026-06-09', 107, 47, NULL, 1, NULL),
(130, 600.00, 'Pending', '2026-06-16', 134, 14, NULL, 1, NULL),
(131, 500.00, 'Pending', '2026-06-02', 33, 33, NULL, 1, NULL),
(132, 500.00, 'Pending', '2026-06-09', 74, 14, NULL, 1, NULL),
(133, 500.00, 'Pending', '2026-06-16', 123, 3, NULL, 1, NULL),
(134, 500.00, 'Pending', '2026-06-16', 161, 41, NULL, 1, NULL),
(135, 950.00, 'Pending', '2026-06-16', 145, 25, NULL, 1, NULL),
(136, 950.00, 'Pending', '2026-06-16', 156, 36, NULL, 1, NULL),
(137, 950.00, 'Pending', '2026-06-16', 169, 49, NULL, 1, NULL),
(138, 950.00, 'Pending', '2026-06-16', 174, 54, NULL, 1, NULL),
(139, 850.00, 'Pending', '2026-06-02', 11, 11, NULL, 1, NULL),
(140, 850.00, 'Pending', '2026-06-09', 67, 7, NULL, 1, NULL),
(141, 850.00, 'Pending', '2026-06-16', 122, 2, NULL, 1, NULL),
(142, 1000.00, 'Pending', '2026-06-09', 87, 27, NULL, 1, NULL),
(143, 1000.00, 'Pending', '2026-06-16', 147, 27, NULL, 1, NULL),
(144, 750.00, 'Pending', '2026-06-02', 35, 35, NULL, 1, NULL),
(145, 750.00, 'Pending', '2026-06-02', 60, 60, NULL, 1, NULL),
(146, 750.00, 'Pending', '2026-06-09', 81, 21, NULL, 1, NULL),
(147, 750.00, 'Pending', '2026-06-09', 84, 24, NULL, 1, NULL),
(148, 750.00, 'Pending', '2026-06-16', 132, 12, NULL, 1, NULL),
(149, 750.00, 'Pending', '2026-06-16', 146, 26, NULL, 1, NULL),
(150, 500.00, 'Pending', '2026-06-02', 48, 48, NULL, 1, NULL),
(151, 500.00, 'Pending', '2026-06-09', 62, 2, NULL, 1, NULL),
(152, 500.00, 'Pending', '2026-06-09', 71, 11, NULL, 1, NULL),
(153, 500.00, 'Pending', '2026-06-16', 126, 6, NULL, 1, NULL),
(154, 700.00, 'Pending', '2026-06-16', 139, 19, NULL, 1, NULL),
(155, 550.00, 'Pending', '2026-06-09', 61, 1, NULL, 1, NULL),
(156, 600.00, 'Pending', '2026-06-02', 13, 13, NULL, 1, NULL),
(157, 600.00, 'Pending', '2026-06-02', 23, 23, NULL, 1, NULL),
(158, 600.00, 'Pending', '2026-06-09', 77, 17, NULL, 1, NULL),
(159, 850.00, 'Pending', '2026-06-16', 149, 29, NULL, 1, NULL),
(160, 900.00, 'Pending', '2026-06-09', 105, 45, NULL, 1, NULL),
(161, 900.00, 'Pending', '2026-06-09', 117, 57, NULL, 1, NULL),
(162, 900.00, 'Pending', '2026-06-16', 125, 5, NULL, 1, NULL),
(163, 900.00, 'Pending', '2026-06-16', 155, 35, NULL, 1, NULL),
(164, 900.00, 'Pending', '2026-06-16', 164, 44, NULL, 1, NULL),
(165, 500.00, 'Pending', '2026-06-02', 9, 9, NULL, 1, NULL),
(166, 500.00, 'Pending', '2026-06-09', 82, 22, NULL, 1, NULL),
(167, 500.00, 'Pending', '2026-06-16', 130, 10, NULL, 1, NULL),
(168, 750.00, 'Pending', '2026-06-09', 91, 31, NULL, 1, NULL),
(169, 750.00, 'Pending', '2026-06-16', 157, 37, NULL, 1, NULL),
(170, 850.00, 'Pending', '2026-06-02', 52, 52, NULL, 1, NULL),
(171, 850.00, 'Pending', '2026-06-09', 115, 55, NULL, 1, NULL),
(172, 850.00, 'Pending', '2026-06-16', 141, 21, NULL, 1, NULL),
(173, 850.00, 'Pending', '2026-06-16', 171, 51, NULL, 1, NULL),
(174, 850.00, 'Pending', '2026-06-02', 14, 14, NULL, 1, NULL),
(175, 750.00, 'Pending', '2026-06-09', 64, 4, NULL, 1, NULL),
(176, 750.00, 'Pending', '2026-06-09', 76, 16, NULL, 1, NULL),
(177, 750.00, 'Pending', '2026-06-16', 150, 30, NULL, 1, NULL),
(178, 500.00, 'Pending', '2026-06-16', 172, 52, NULL, 1, NULL),
(179, 1000.00, 'Pending', '2026-06-02', 27, 27, NULL, 1, NULL),
(180, 1000.00, 'Pending', '2026-06-02', 44, 44, NULL, 1, NULL),
(256, 15000.00, 'Pending', '2026-06-02', 3, 3, 1, 3, NULL),
(257, 45000.00, 'Pending', '2026-06-02', 7, 7, 2, 5, NULL),
(258, 30000.00, 'Pending', '2026-06-02', 12, 12, 3, 2, NULL),
(259, 28000.00, 'Pending', '2026-06-02', 18, 18, 4, 4, NULL),
(260, 12000.00, 'Partial', '2026-06-02', 22, 22, 5, 1, NULL),
(261, 33000.00, 'Partial', '2026-06-02', 25, 25, 6, 6, NULL),
(262, 28500.00, 'Partial', '2026-06-02', 31, 31, 7, 3, NULL),
(263, 112000.00, 'Pending', '2026-06-02', 35, 35, 8, 7, NULL),
(264, 15000.00, 'Partial', '2026-06-02', 40, 40, 9, 2, NULL),
(265, 65000.00, 'Partial', '2026-06-02', 44, 44, 10, 5, NULL),
(266, 19200.00, 'Paid', '2026-06-02', 48, 48, 11, 4, NULL),
(267, 25500.00, 'Paid', '2026-06-02', 51, 51, 12, 3, NULL),
(269, 13000.00, 'Pending', '2026-06-02', 58, 58, 14, 2, NULL),
(272, 800.00, 'Paid', '2026-06-02', 257, 62, NULL, 1, NULL),
(273, 1000.00, 'Pending', '2026-06-03', 259, 62, NULL, 1, NULL),
(274, 1000.00, 'Pending', '2026-06-03', 260, 62, NULL, 1, NULL),
(275, 1000.00, 'Pending', '2026-06-03', 261, 63, NULL, 1, NULL),
(276, 7200.00, 'Paid', '2026-06-03', 261, 63, 18, 1, 4);

--
-- Triggers `bills`
--
DELIMITER $$
CREATE TRIGGER `trg_log_bill_payment` AFTER UPDATE ON `bills` FOR EACH ROW BEGIN
  IF OLD.PaymentStatus <> NEW.PaymentStatus THEN
    INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
    VALUES ('bills', NEW.BillID, 'UPDATE', 'PaymentStatus',
            CONCAT(OLD.PaymentStatus, ' Rs.', OLD.TotalAmount),
            CONCAT(NEW.PaymentStatus, ' Rs.', NEW.TotalAmount));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_prevent_modify_paid_bill` BEFORE UPDATE ON `bills` FOR EACH ROW BEGIN
  IF OLD.PaymentStatus = 'Paid' AND NEW.TotalAmount <> OLD.TotalAmount THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot modify amount of a bill that is already Paid.';
  END IF;
  IF OLD.PaymentStatus = 'Paid' AND NEW.PaymentStatus = 'Pending' THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot revert a Paid bill back to Pending.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `location`) VALUES
(1, 'Emergency Department (ER)', 'Ground Floor, Main Entrance'),
(2, 'Cardiology', 'Block A, Floor 1'),
(3, 'Neurology', 'Block A, Floor 2'),
(4, 'Oncology', 'Block B, Floor 1'),
(5, 'Pediatrics', 'Block B, Floor 2'),
(6, 'Orthopedics', 'Block C, Floor 1'),
(7, 'Intensive Care Unit (ICU)', 'Block A, Floor 3'),
(8, 'Radiology & Imaging', 'Basement, Block A'),
(9, 'General Surgery', 'Block C, Floor 2'),
(10, 'Dermatology', 'Outpatient Wing, Floor 1'),
(11, 'Psychiatry', 'Block D, Floor 1'),
(12, 'Obstetrics & Gynecology', 'Block B, Floor 3'),
(13, 'Urology', 'Block C, Floor 3'),
(14, 'ENT (Otolaryngology)', 'Outpatient Wing, Floor 2'),
(15, 'Ophthalmology', 'Outpatient Wing, Floor 2'),
(16, 'Gastroenterology', 'Block A, Floor 4'),
(17, 'Endocrinology', 'Outpatient Wing, Floor 3'),
(18, 'Nephrology & Dialysis', 'Block A, Floor 4'),
(19, 'Pulmonology', 'Block C, Floor 4'),
(20, 'Rheumatology', 'Outpatient Wing, Floor 3'),
(21, 'Hematology', 'Block B, Floor 4'),
(22, 'Anesthesiology', 'Block C, Floor 2'),
(23, 'Pathology & Laboratory', 'Basement, Block B'),
(24, 'Inpatient Pharmacy', 'Ground Floor, Lobby'),
(25, 'Physiotherapy & Rehab', 'Block D, Ground Floor'),
(26, 'Nutrition & Dietetics', 'Block D, Floor 2'),
(27, 'General Outpatient (OPD)', 'Main Wing, Ground Floor'),
(28, 'Infectious Diseases', 'Isolation Wing, Block E'),
(29, 'Burn Center', 'Block E, Floor 1'),
(30, 'Neonatal ICU (NICU)', 'Block B, Floor 3');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT 0.00,
  `appointment_fee` decimal(10,2) DEFAULT 0.00,
  `dept_id` int(11) DEFAULT NULL,
  `Passwords` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `specialization`, `phone`, `salary`, `appointment_fee`, `dept_id`, `Passwords`) VALUES
(1, 'Kathryn Norman', 'General Physician', '03100928371', 75000.00, 500.00, 27, '$2y$10$rep4gcLrBG8cCdHeMNis0.YNviCQELC7YAWu4C.BYO9ist1MkKgQ2'),
(2, 'Sarah Harper DVM', 'General Surgeon', '03101856742', 89000.00, 750.00, 9, '$2y$10$cGFAAFugOthc8FmBUI4LveKCDdQz.zwNxEtYLZmIRQqSDxaAHv8NW'),
(3, 'Jessica Nelson', 'Nephrologist', '03102785113', 94000.00, 850.00, 18, '$2y$10$EfMxa30amMkys/sbze2Xzu3SUuzRFv9crIZoRZNvcOcuLQ4vyW3IC'),
(4, 'Jennifer Guzman', 'Pulmonologist', '03103713484', 91000.00, 700.00, 19, '$2y$10$VlUkxwlztJ7OSBqpL0wPUupfedrxI8tRK/I6/B9PEqVUxYmka9xca'),
(5, 'Kyle Randolph', 'Gastroenterologist', '03104641855', 96000.00, 900.00, 16, '$2y$10$EEKh2r2UaVaRZTkne05Xt.SJFx78.TNqp4dWIlUaIZzOcSV0gg7yK'),
(6, 'Cheryl Hodge', 'Ophthalmologist', '03105570226', 88000.00, 650.00, 15, '$2y$10$xRJTwElOfY4CthjQXR7Okuygg/Ee/ZzK.rO7JzJZSbhdLmpxpElEu'),
(7, 'Krista Nelson', 'ENT Specialist', '03106498597', 87000.00, 550.00, 14, '$2y$10$DWs9IuFgCs4uLmdBRtEe.uij3Hq0J.75bBaULBBr1p5h8vwcyncg.'),
(8, 'Robin Brock', 'Gynecologist', '03107426968', 93000.00, 800.00, 12, '$2y$10$5xRk6NtQjzhGImyK5mkOW.ZW9.p3e01IZCEykzasW2Hh8jmBJTMLe'),
(9, 'Darin Lewis', 'Psychiatrist', '03108355339', 92000.00, 750.00, 11, '$2y$10$yzMqjLsA/9WgR9t/o/jk5uGYonCfuiXZgqB2H8h1WclM90xw0HXWe'),
(10, 'Michael Wolf', 'Dermatologist', '03109283710', 85000.00, 600.00, 10, '$2y$10$LKZ94ceFBt8DCWMslMgp9.ZX36MmNyYei/rTO09V8bCNCSo4JEBk2'),
(11, 'Robert Taylor', 'Orthopedic Surgeon', '03110212081', 95000.00, 850.00, 6, '$2y$10$2Ewu23.hYz6hvRaMbUSfRO8TTPBnReIRn6W.jWKMyS/.EZ18POqO.'),
(12, 'Terri Flores', 'Pediatrician', '03111140452', 90000.00, 700.00, 5, '$2y$10$l.M/SLaFQJFVOD98YSn1Ye4PHL6ZOLfrPJi13WAqBTVxqVO6KF8gG'),
(13, 'Jamie Ball', 'Oncologist', '03112068823', 97000.00, 900.00, 4, '$2y$10$Nxgn7umjWPEeUxjfQ1tBoO0C37EayJFSH9LxRRQxBN2u432W7BQGy'),
(14, 'Crystal Collins', 'Neurologist', '03112997194', 98000.00, 950.00, 3, '$2y$10$Djd0rRt5HvZzWxYqqrwOBOKtaqFbamoTspWBiym5i0Untsl52Kmkm'),
(15, 'Omar Calderon', 'Cardiologist', '03113925565', 100000.00, 1000.00, 2, '$2y$10$FTb9npLgUsKlyaQ2IaUG0et9pR3PQe/OwByP7cMrN.vVufV42JyFy'),
(16, 'Amanda Johnson', 'Gynecologist', '03114853936', 93000.00, 800.00, 12, '$2y$10$wh85NryEaHi.Q2o5aN2w/uyVhMSHUpykMhty8JhVmy5zj.jR4GZwi'),
(17, 'Jenna Taylor', 'General Physician', '03115782307', 75000.00, 500.00, 27, '$2y$10$4AGgkOIIzsBaAESBEwx6NepKjwKgvW2DeZ0NutgD.pREC4JqnNbiu'),
(18, 'Kimberly Gomez', 'Psychiatrist', '03116710678', 92000.00, 750.00, 11, '$2y$10$W2vFNenjKK0agHF.gft8l.D7pYd/6D3lAAwEgVw/4B98BueABvQy6'),
(19, 'Luis Jones', 'General Physician', '03117639049', 75000.00, 500.00, 27, '$2y$10$3hAmB1LcnIdzIgv1Yq0C3.Eifu.qShCeNfeli4QjIWFuVeakLo.16'),
(20, 'Jennifer Robertson', 'Dermatologist', '03118567420', 85000.00, 600.00, 10, '$2y$10$MGmNnruQuzZBEg0hnWqcjOgN0orwRfOom3B142g7PYj0VK3W7jYkC'),
(21, 'Chad Lopez', 'ENT Specialist', '03119495791', 87000.00, 550.00, 14, '$2y$10$G15xrVR.WwVlzrTlr3hGqePirj8Teka/PZsEMcFcr7u3uu8bAl4cq'),
(22, 'Morgan Harris', 'Orthopedic Surgeon', '03120424162', 95000.00, 850.00, 6, '$2y$10$Yp0rrkU.5cm0XwPfT9/aYuV2ZERJYCUccSHpykI/rRjK2iw3.7PsW'),
(23, 'Donna Campbell', 'General Physician', '03121352533', 75000.00, 500.00, 27, '$2y$10$K0srYtzoRAPPBMnsfAsKnOAWntP3I/kY70GHptv0dPRhD4abO92nK'),
(24, 'Tammy Murphy', 'Pediatrician', '03122280904', 90000.00, 700.00, 5, '$2y$10$rXd9xVH0X0VukXmTuPjcbOotXZXmeWVTGuXlMWf0oKYJAuQCrsU5y'),
(25, 'Jennifer Patel', 'Gastroenterologist', '03123209275', 96000.00, 900.00, 16, '$2y$10$Tn2WDfyTYkhIgh62qfYdWurq32Fe.dF.IwIAOsu1axTbpxJQEgLqe'),
(26, 'Donald Coleman', 'Oncologist', '03124137646', 97000.00, 900.00, 4, '$2y$10$23JYSLke501yovUpI5RwoOyiUXTGxbvUNq59ts1T1Z9QWz8imARPO'),
(27, 'Austin Figueroa', 'Psychiatrist', '03125066017', 92000.00, 750.00, 11, '$2y$10$QfmrhmXqYzIwas85Tln/E.uKw.ygbyfSTn29tHNBUEvgjsHHE3fqa'),
(28, 'Lauren Mack', 'Neurologist', '03125994388', 98000.00, 950.00, 3, '$2y$10$rUd2/rqTwF1FSAegOHPQf.oRancj4ikads.bVb70PB05uvdH.qkkW'),
(29, 'Erik Campbell', 'General Physician', '03126922759', 75000.00, 500.00, 27, '$2y$10$r365BBszMouESENCJh1zJuyfN/OyikM32NwbiCJsJ7wQViDYJiEjy'),
(30, 'Phillip Johnson', 'Cardiologist', '03127851130', 100000.00, 1000.00, 2, '$2y$10$1gCESID57AqCkUHWnbhCFexq6GFv0qh7ZPZDBKP/xMNokCxKrQBC2'),
(31, 'Jennifer Smith', 'General Physician', '03128779501', 75000.00, 500.00, 27, '$2y$10$vgj.UDb2gI6ItlgTVAE2OOEXqdHsjftkUwhQD4YxxN71zEFA6VwHu'),
(32, 'Joseph Hicks', 'Gynecologist', '03129707872', 93000.00, 800.00, 12, '$2y$10$F/6QL9ZUpKSLquEQ0/jX2eX0vHTkr9hknjf1o9vxxfmBKVzREVvba'),
(33, 'Kelly Smith', 'Orthopedic Surgeon', '03130636243', 95000.00, 850.00, 6, '$2y$10$EFwci1uf5Nj9KYcFnrjRguuRzJs/flpLgT5MmHRyt0BMLqDiumrZS'),
(34, 'Rachel Harrell', 'General Surgeon', '03131564614', 89000.00, 750.00, 9, '$2y$10$myBqdMs/WUieF7SuYEizq./HJaH38dn0ZINEfDToxAkPFSfsvhyUC'),
(35, 'Kevin Warren', 'ENT Specialist', '03132492985', 87000.00, 550.00, 14, '$2y$10$.Baj6HFKTsmtW0sFw/4h8ulH5eYMyNHgeQDKK4kbtXTE74MqgS.8S'),
(36, 'Christopher Rivera', 'Pediatrician', '03133421356', 90000.00, 700.00, 5, '$2y$10$l2Np8UgWZqHYdr3kOFwCd.RxC/M3zBX3pn8ZNgNHoGUwLL2jRLfYW'),
(37, 'Frederick Stephens', 'General Physician', '03134349727', 75000.00, 500.00, 27, '$2y$10$W3ulCVIYBAp2lq0PHRtoL.NpXOYIVcXNkmTXjKf.Ok0NKNx4LMkte'),
(38, 'Justin Holland', 'General Surgeon', '03135278098', 89000.00, 750.00, 9, '$2y$10$tB/2KoR9GGDIAfs41NZ.X.x99cSE2Q4886JNYznI/EN/q/uP0C2r6'),
(39, 'Thomas Ray', 'Oncologist', '03136206469', 97000.00, 900.00, 4, '$2y$10$DMx9cBrQyoCzvPqMgB2yyuljstb2xokAFKuGIfYuR5roxwBKah8k2'),
(40, 'Christopher Powell', 'Dermatologist', '03137134840', 85000.00, 600.00, 10, '$2y$10$8tK.fS/rPhu0WvUpvu7k/u3WgJvtXidnaWHQYHtv7JzkGwn.tFIs2'),
(41, 'Adam Ellis', 'General Physician', '03138063211', 75000.00, 500.00, 27, '$2y$10$vUBB7l46rRURKYfpitFB3.c.V546rG.wObqJBUR6bWeZwBCa5IceG'),
(42, 'Tanya Stanton', 'Neurologist', '03138991582', 98000.00, 950.00, 3, '$2y$10$pmsBMk.8WX1AfQXmmoWi/u5.4w8yq1L3HhmGOVGLjz0IuX7On169W'),
(43, 'Jack Young', 'General Physician', '03139919953', 75000.00, 500.00, 27, '$2y$10$EcuTXoLv3QFyai7kqBESSuAPlSprp7khFCzkT.hlPduQriYP5mxci'),
(44, 'Michelle Martinez', 'Orthopedic Surgeon', '03140848324', 95000.00, 850.00, 6, '$2y$10$czzKbEIe0fTdh8IMlN37wumj1oYX7Sk3uIIFgBvkYRFFc6UCG8VgK'),
(45, 'Jay Mullins', 'Cardiologist', '03141776695', 100000.00, 1000.00, 2, '$2y$10$.Zq8I86UcPLtfORYOyjrXOqlwz5NbO.T5nr9Bkv368vQV/XHrqucO'),
(46, 'Amanda Watson', 'General Surgeon', '03142705066', 89000.00, 750.00, 9, '$2y$10$cD9j7fOngkFb6U2Woo7E/.sBQ1ZckBxSeaCK5eIw8oshynuQfMMQ2'),
(47, 'Emily Henry', 'General Physician', '03143633437', 75000.00, 500.00, 27, '$2y$10$P8qnR3QKVHaCh1ghTYGxUecabkG7U6LISh65cfs5dsIhRcsR8ed1G'),
(48, 'Thomas Fernandez', 'Pediatrician', '03144561808', 90000.00, 700.00, 5, '$2y$10$xYBk6IBllBzwwWEx21LPj.2f8Axwr2Od9z5FZUxM8zoU2ExAjauN.'),
(49, 'Judy Tran', 'ENT Specialist', '03145490179', 87000.00, 550.00, 14, '$2y$10$y84c1I9wPoLxdKvL0dxGZ.LBvOGUiIwNJO/iXgDC6SUMrbkwqEpnC'),
(50, 'Adriana Jackson', 'Dermatologist', '03146418550', 85000.00, 600.00, 10, '$2y$10$r.bGclLUn/zqLHPHCEY24OM72HM41guRbtGzGDBzGWUAmU4A0y32y'),
(51, 'Travis Fisher', 'Nephrologist', '03147346921', 94000.00, 850.00, 18, '$2y$10$QHoXz.l4WM142gkWpEfQXOrSAMKGZhE51Ez35ztxOjJjwypPpi7zO'),
(52, 'Lisa Glover', 'Oncologist', '03148275292', 97000.00, 900.00, 4, '$2y$10$JpapToeyXTYU8Wc.RJ7twupTf17ILiNSua3nTmigd4rLnVW9naIgm'),
(53, 'Holly Taylor', 'General Physician', '03149203663', 75000.00, 500.00, 27, '$2y$10$D.ulL7bDYl7TnuADUFP2GunfO1DUfmw.JZoCO3XccXFOqNMpcJik6'),
(54, 'Robert Long', 'Psychiatrist', '03150132034', 92000.00, 750.00, 11, '$2y$10$i6Nil5AfioKjBfFOAFSW6.V5yySAjLR/CgkMr.i.9B56qit1W2dNm'),
(55, 'Isaac Smith', 'Orthopedic Surgeon', '03151060405', 95000.00, 850.00, 6, '$2y$10$Nm64cryeP/fLdnlcAc.UMexLQmQx0yO5KzwjlPJkNlKt8PWanD1x6'),
(56, 'Christina Kelly', 'Neurologist', '03151988776', 98000.00, 950.00, 3, '$2y$10$ehXbbkUwTqp/3mBTVuip5e2xLDefdqEvCSD1ewteJy.jskgJBQep6'),
(57, 'Lauren Rice', 'Nephrologist', '03152917147', 94000.00, 850.00, 18, '$2y$10$bp/j7CnOlOQzAGR74obAEOiOOYY5HIsK6S0m173cowLpANlgsaYg2'),
(58, 'Troy Garza Jr.', 'General Surgeon', '03153845518', 89000.00, 750.00, 9, '$2y$10$AxEBAhPJY6Xwq4qpaFRJJuUG73a2nS6gNCqUqoljJ4kVcOoSaxEB2'),
(59, 'Leslie Wall', 'General Physician', '03154773889', 75000.00, 500.00, 27, '$2y$10$v0s6mmmjN5SO/DduFp.C8OZd8/ABBqjI5LonwNm8Uw8BsAKvDduLm'),
(60, 'Cynthia Anthony', 'Cardiologist', '03155702260', 100000.00, 1000.00, 2, '$2y$10$MxT.Ao5rXpo/4CzGidEtmuON2gsp8VXF06o2zed3zk8WdLIdSYSOe');

--
-- Triggers `doctors`
--
DELIMITER $$
CREATE TRIGGER `trg_log_doctor_fee_change` AFTER UPDATE ON `doctors` FOR EACH ROW BEGIN
  IF IFNULL(OLD.appointment_fee,0) <> IFNULL(NEW.appointment_fee,0) THEN
    INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
    VALUES ('doctors', NEW.id, 'UPDATE', 'appointment_fee',
            CONCAT('Rs.', COALESCE(OLD.appointment_fee,0), ' Dr.', OLD.name),
            CONCAT('Rs.', COALESCE(NEW.appointment_fee,0), ' Dr.', NEW.name));
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_prevent_delete_active_doctor` BEFORE DELETE ON `doctors` FOR EACH ROW BEGIN
  DECLARE v_count INT DEFAULT 0;
  SELECT COUNT(*) INTO v_count FROM appointments
  WHERE DoctorID = OLD.id AND Status = 'Scheduled';
  IF v_count > 0 THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot delete doctor — they have active Scheduled appointments.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `med_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`med_id`, `name`, `category`) VALUES
(1, 'Paracetamol', 'Pain Relief'),
(2, 'Ibuprofen', 'NSAID / Pain Relief'),
(3, 'Amoxicillin', 'Antibiotic'),
(4, 'Cetirizine', 'Antihistamine'),
(5, 'Omeprazole', 'Antacid / PPI'),
(6, 'Metformin', 'Antidiabetic'),
(7, 'Lisinopril', 'Antihypertensive'),
(8, 'Atorvastatin', 'Cholesterol Lowering'),
(9, 'Aspirin', 'Pain Relief / Blood Thinner'),
(10, 'Azithromycin', 'Antibiotic'),
(11, 'Loratadine', 'Antihistamine'),
(12, 'Pantoprazole', 'Antacid / PPI'),
(13, 'Amlodipine', 'Antihypertensive'),
(14, 'Losartan', 'Antihypertensive'),
(15, 'Levothyroxine', 'Thyroid Hormone'),
(16, 'Albuterol', 'Bronchodilator'),
(17, 'Gabapentin', 'Nerve Pain / Anticonvulsant'),
(18, 'Ciprofloxacin', 'Antibiotic'),
(19, 'Sertraline', 'Antidepressant'),
(20, 'Escitalopram', 'Antidepressant'),
(21, 'Fluoxetine', 'Antidepressant'),
(22, 'Tramadol', 'Pain Relief'),
(23, 'Clopidogrel', 'Blood Thinner'),
(24, 'Montelukast', 'Asthma / Allergy'),
(25, 'Furosemide', 'Diuretic'),
(26, 'Meloxicam', 'NSAID / Pain Relief'),
(27, 'Citalopram', 'Antidepressant'),
(28, 'Metoprolol', 'Beta Blocker'),
(29, 'Prednisone', 'Corticosteroid'),
(30, 'Trazodone', 'Antidepressant / Sleep Aid'),
(31, 'Doxycycline', 'Antibiotic'),
(32, 'Rosuvastatin', 'Cholesterol Lowering'),
(33, 'Venlafaxine', 'Antidepressant'),
(34, 'Diclofenac', 'NSAID / Pain Relief'),
(35, 'Ranitidine', 'Antacid'),
(36, 'Simvastatin', 'Cholesterol Lowering'),
(37, 'Hydrochlorothiazide', 'Diuretic'),
(38, 'Naproxen', 'NSAID / Pain Relief'),
(39, 'Glipizide', 'Antidiabetic'),
(40, 'Cephalexin', 'Antibiotic'),
(41, 'Tamsulosin', 'Prostate Treatment'),
(42, 'Alprazolam', 'Anti-anxiety'),
(43, 'Clonazepam', 'Anti-anxiety'),
(44, 'Ondansetron', 'Antiemetic / Anti-nausea'),
(45, 'Famotidine', 'Antacid'),
(46, 'Allopurinol', 'Gout Treatment'),
(47, 'Fluticasone', 'Corticosteroid'),
(48, 'Atenolol', 'Beta Blocker'),
(49, 'Sulfamethoxazole', 'Antibiotic'),
(50, 'Spironolactone', 'Diuretic'),
(51, 'Paracitamol', 'Tablet');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `PatientID` int(11) NOT NULL,
  `Name` varchar(100) NOT NULL,
  `DateOfBirth` date DEFAULT NULL,
  `Gender` enum('M','F','O') DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Street` varchar(255) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `ZipCode` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `registered_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`PatientID`, `Name`, `DateOfBirth`, `Gender`, `Phone`, `Street`, `City`, `ZipCode`, `password`, `registered_by`) VALUES
(1, 'Isaac Butler', '1996-06-02', 'F', '03100731291', NULL, NULL, NULL, '$2y$10$Nlzo0aqKjinltpThZ9PpFe5x64b4z9RrY2SqhZqdXK/xcCq6xthA6', NULL),
(2, 'Brian Taylor', '2016-06-02', 'M', '03101462582', NULL, NULL, NULL, '$2y$10$PzL0bOj5vO/dEQYK.q1tI.fQcjMdMAF3EIlypfIa3MQyZ9/vaHtfG', NULL),
(3, 'Leslie Moore', '2010-06-02', 'F', '03102193873', NULL, NULL, NULL, '$2y$10$sdgQIaZFjnLnna35ixmdGOorxP4q1YLinVSkXtxwi2gh4e9yZRhtu', NULL),
(4, 'Ashley Pena', '2004-06-02', 'M', '03102925164', NULL, NULL, NULL, '$2y$10$k4UsnvWGbbocbQ3LnWKo1eCgLp3JXNBaB.6mJ20pZr2E7MDF4z1o2', NULL),
(5, 'John Hunt', '1998-06-02', 'F', '03103656455', NULL, NULL, NULL, '$2y$10$Vq3.QH/Np78EYVa3CNvrk.E.nSfCiJnSeN1MOHrnPnpt4IAfVn2oi', NULL),
(6, 'Samuel Fuentes', '1991-06-02', 'M', '03104387746', NULL, NULL, NULL, '$2y$10$jaMdgL1HlYgSODMU430LTeci1dyuJWMLGzW.b2c5ccKDNnPWQlFDa', NULL),
(7, 'Jonathan Bowman', '1984-06-02', 'F', '03105119037', NULL, NULL, NULL, '$2y$10$pLbLSybCWuD74XyAsaQKTeplOD0VOH8kZ0EYatnhTBTf7k1.v/dqa', NULL),
(8, 'Alexander Mcgrath', '1976-06-02', 'M', '03105850328', NULL, NULL, NULL, '$2y$10$mnPSMezDxuJI9Czwz6Wz1uy8nf4im.pJXJT1eLSaKslJ4UA.WMsSu', NULL),
(9, 'Robert Wolfe', '1968-06-02', 'F', '03106581619', NULL, NULL, NULL, '$2y$10$e7Y/UCOFST0Ytl1WG7XH1ObNOG.gcIvHOGRU8hlBDeB/EZN./I8/2', NULL),
(10, 'Cole Martinez', '1961-06-02', 'M', '03107312910', NULL, NULL, NULL, '$2y$10$c0owVqUUxrdoRUjgEXUPGu8bcVcnOfhX2s9fi6nCSav2bAIuMlLhG', NULL),
(11, 'Sarah Montes', '1996-06-02', 'F', '03108044201', NULL, NULL, NULL, '$2y$10$jLXu3WWo5jjHxVhZZFRziuuEZuKyROLwVYbyohAiGXgZ0v9mJtASi', NULL),
(12, 'Melissa Nelson', '1991-06-02', 'M', '03108775492', NULL, NULL, NULL, '$2y$10$KCSVg1MpTb1ONvGZgBXIy.fFlc5rUlPruuBy2dFRZ9zOIstUtrbcO', NULL),
(13, 'Julia Obrien', '1996-06-02', 'F', '03109506783', NULL, NULL, NULL, '$2y$10$xrqzzANHn/mWhV9rAwYPk.bWjDDKbfiIiaemjSJl1yBWc/gE82.BC', NULL),
(14, 'Joshua Harris', '1984-06-02', 'M', '03110238074', NULL, NULL, NULL, '$2y$10$u2B1I4FsBfvJLhdSYpPR8O6OcAgxWbQDxZcyG4OS/cWoeam7IGTGm', NULL),
(15, 'Joseph Allen', '1998-06-02', 'F', '03110969365', NULL, NULL, NULL, '$2y$10$kTH.sFaQIeNr9sXLq2VZUeomYm.jdQOESf88shZdzBN4sCb1WPJaC', NULL),
(16, 'Jason Johnson', '1976-06-02', 'M', '03111700656', NULL, NULL, NULL, '$2y$10$nX9NSKJZaUa.MueBYVDZ2e8y/CMZRxqY1Vc32s4jC292IkqBeHDue', NULL),
(17, 'Nichole Ayers', '1996-06-02', 'F', '03112431947', NULL, NULL, NULL, '$2y$10$b1a.PHMXoJjZN1hqicZ/auA0sIbTrlWEAWMdNi5vmJvm7iTeHsye6', NULL),
(18, 'Gabrielle Bishop', '1968-06-02', 'M', '03113163238', NULL, NULL, NULL, '$2y$10$zs1uTd8u.SMfmgDuNCNcweAi6Lgdw010Z572PJ4sxsJNtflehpkue', NULL),
(19, 'Daniel Mcgee', '1996-06-02', 'F', '03113894529', NULL, NULL, NULL, '$2y$10$mZJBuIVOTzPxo0hCntUgzOD70tUxqgfGZO5bCsieEZTIS1YTNWNRq', NULL),
(20, 'Angela Evans', '1961-06-02', 'M', '03114625820', NULL, NULL, NULL, '$2y$10$N54ra9VVnm.LJBYPD3YmIuCoEpLQo1e/KQVVNe99CxVJ1aWPnX4a.', NULL),
(21, 'Kristen Jones MD', '1984-06-02', 'F', '03115357111', NULL, NULL, NULL, '$2y$10$kNKvMTPVN1ewPPAY5poFi.ziMzsWwM3yifVJ2tT5Z9Rg3oPTF6Upq', NULL),
(22, 'Tammy Carter', '2016-06-02', 'M', '03116088402', NULL, NULL, NULL, '$2y$10$MGCycScdfSg2pU7k0YDkg.toEnvaXvaGVCsWR.Fhyfns6vtqnFw4y', NULL),
(23, 'Michael Cox', '1996-06-02', 'F', '03116819693', NULL, NULL, NULL, '$2y$10$kXeg3fAenI8zHVlhq/D2LOqN0kmGaNo.1SbeElLPtQWZZMqeEUrUq', NULL),
(24, 'Christopher Patterson', '1976-06-02', 'M', '03117550984', NULL, NULL, NULL, '$2y$10$DbKL.Mpfpv8FUqrgH4GRYuUyC29lu3xtMAWWn.tRWvXxjhTQMt2AK', NULL),
(25, 'Susan Hill', '1998-06-02', 'F', '03118282275', NULL, NULL, NULL, '$2y$10$xl7YlEuJth0WaESzfN0nh.ntEULOAe1zAAaws3o/vOxZHh7UjU852', NULL),
(26, 'Jeffrey Mckay', '2016-06-02', 'M', '03119013566', NULL, NULL, NULL, '$2y$10$uywpxGgWcybUlWJoORmzWOfvdC7L.kvDqAziNnEsmdtqpPbayLIuu', NULL),
(27, 'Carol Ellis', '1968-06-02', 'F', '03119744857', NULL, NULL, NULL, '$2y$10$gGLDNYvk1D6HKSNqfOuP7.RgdV3x31JIFEXrgb4.DoPlU1hinnK7y', NULL),
(28, 'Cynthia Hardy', '1984-06-02', 'M', '03120476148', NULL, NULL, NULL, '$2y$10$TbW4KpydHLKTsSHuziGRPu3S4W0kwQFwlbGlfdRSPDUQBcZKn/N.S', NULL),
(29, 'John Miller', '1996-06-02', 'F', '03121207439', NULL, NULL, NULL, '$2y$10$4nOhCPQ7UxskbZOeDhpogurr3.AfBNbx3gqLk1dh6avKvvsIdCeLG', NULL),
(30, 'Joseph Hall', '1961-06-02', 'M', '03121938730', NULL, NULL, NULL, '$2y$10$v40f8uqwoglWszvjkeEzs.ziw9wl2UWfOCUem.UwQZ6T6PCnkpB3W', NULL),
(31, 'Stephanie Evans', '1996-06-02', 'F', '03122670021', NULL, NULL, NULL, '$2y$10$cWKyqxROELrkAnmluj4Vj.XIUl0Jzu9esrPTavwpgHt/Yh2tQkyK2', NULL),
(32, 'James Mcpherson', '1976-06-02', 'M', '03123401312', NULL, NULL, NULL, '$2y$10$Yuqi08J2MjcegzbGpIbYKufUmCyHZxFoQvYEUBWmPnK32mU4m7gpW', NULL),
(33, 'Thomas Perez', '2010-06-02', 'F', '03124132603', NULL, NULL, NULL, '$2y$10$KksYes.IF7MbkVGVXFnx1.oHbr5Kynmxr0k3t3ppWMbVf30nFbNm2', NULL),
(34, 'Michelle Hawkins', '2016-06-02', 'M', '03124863894', NULL, NULL, NULL, '$2y$10$NL8FkpmR5E3u3ZjA/LLAN.AATuj3RbFCCZOTDpuIBovaNtgHML3Gm', NULL),
(35, 'Douglas Manning', '1984-06-02', 'F', '03125595185', NULL, NULL, NULL, '$2y$10$TfZOVQcQIzfIIc9F0fvcMu2M3mej3e.mQw.uJGJxF0SthmH.YcRLa', NULL),
(36, 'Christina Williams', '1968-06-02', 'M', '03126326476', NULL, NULL, NULL, '$2y$10$5JGm9kfE6MDln3Rj2zBsFuzKvRgQnFuQxhhoBTNB2qtXnRhOrqKAO', NULL),
(37, 'Jennifer Bright', '1996-06-02', 'F', '03127057767', NULL, NULL, NULL, '$2y$10$kCPjNh/RX5gUeaa2yGjxu.gCADhYfbgBUV39IK22GgB2.hNbqaZ0u', NULL),
(38, 'Mrs. Lisa Mendez', '2016-06-02', 'M', '03127789058', NULL, NULL, NULL, '$2y$10$gYtWI9otz03qjf3LrtgSyueb02STkf6rS3MpbQ7CnA7IM3sIecOru', NULL),
(39, 'Mr. James Rogers', '2010-06-02', 'F', '03128520349', NULL, NULL, NULL, '$2y$10$iJ2lVA6TSc6WsfjypcoaAuQmz.kh1AnMbPpcGUkhnOo00/yKnxahu', NULL),
(40, 'Christopher Dean', '1961-06-02', 'M', '03129251640', NULL, NULL, NULL, '$2y$10$EblwENsIl4MW18pIhp6OruNNC1aCh8yqh1sDWSzpahQzPv5dRkUSy', NULL),
(41, 'Ronald Zamora', '1996-06-02', 'F', '03129982931', NULL, NULL, NULL, '$2y$10$2Jsmfd9miaNMfEVlNTpTuO4NTskTEQXpj3q6Q4O3XGOAQRygZhdEe', NULL),
(42, 'Cheryl Humphrey', '1984-06-02', 'M', '03130714222', NULL, NULL, NULL, '$2y$10$4AE9G3TxFTU4DViKNLW56elT3uz/v4PhSXr7f1cEJ/1vVW7Jc9Si.', NULL),
(43, 'Tiffany Ayala', '1996-06-02', 'F', '03131445513', NULL, NULL, NULL, '$2y$10$WEyti0SQfyWCRc/D7ExgyeSkdqfhRGDuY7tEy24XfR7/UnY.ZSlw2', NULL),
(44, 'Tracy Barnes', '2004-06-02', 'M', '03132176804', NULL, NULL, NULL, '$2y$10$YEh4q3FB9FyCcs63I69MiOn3xejjzcy6.wxCjHVJz.tKvmNxDzrqW', NULL),
(45, 'Kevin Lane', '1968-06-02', 'F', '03132908095', NULL, NULL, NULL, '$2y$10$iua57Zg1kFlSpIvU2RoQyeYLX.lsfPZWZvTzKfU7wuLRbZd7vq2NC', NULL),
(46, 'Steven Cunningham', '2016-06-02', 'M', '03133639386', NULL, NULL, NULL, '$2y$10$nNvYv625oyy5.52lqV5t..jyHMDJ0kWhh1kf7dmdnOGhcrd0I.Qxi', NULL),
(47, 'Carrie Marsh', '1996-06-02', 'F', '03134370677', NULL, NULL, NULL, '$2y$10$VWVf/jbL/9j.FWTWTz.ty.povmbDfh4pmGzx.fedpk8vbdgB0OJ6W', NULL),
(48, 'John Eaton', '1976-06-02', 'M', '03135101968', NULL, NULL, NULL, '$2y$10$HA.HGhyAF76R2eF1H6uDAuk6YKb9hD.WIWqyylGpvf7j9w95PbGB2', NULL),
(49, 'Christopher Wilcox', '1984-06-02', 'F', '03135833259', NULL, NULL, NULL, '$2y$10$J0aJ57di5.SZcnewqSZWVepbDsEw/IeHgySf51sVRP33u0p8Hi1yy', NULL),
(50, 'Emily Johnson', '1961-06-02', 'M', '03136564550', NULL, NULL, NULL, '$2y$10$Nh/Q0LJ4UiJ5Rzy5GG/ph.iAiRWDOcR23IAfVIMbl.uND24/f2TyS', NULL),
(51, 'Meghan Berg', '2010-06-02', 'F', '03137295841', NULL, NULL, NULL, '$2y$10$S613fsL5OY9GrVDEzi3Ebe6vrVAu/SVrSN/48zrprhJpzjAOAOb9a', NULL),
(52, 'Brandon Lewis', '2004-06-02', 'M', '03138027132', NULL, NULL, NULL, '$2y$10$5.BDO5DJcQpZqN0.IOXb0uW589g2GloyOxOOiD5FE8kIKTEid6DSW', NULL),
(53, 'Jessica Morris', '1996-06-02', 'F', '03138758423', NULL, NULL, NULL, '$2y$10$SGSG403B.IWAAN282R/xVOBnRo1NglIo39p8E2ofvuLB/J0hnAUGe', NULL),
(54, 'Amy Atkins', '1968-06-02', 'M', '03139489714', NULL, NULL, NULL, '$2y$10$LcKD8tuo0JFcvgGqHmWQIuWwgtBrKXsAnNJBM0xb1k9era9MAy4Gq', NULL),
(55, 'Todd Weaver', '1998-06-02', 'F', '03140221005', NULL, NULL, NULL, '$2y$10$ULwBfogznDu926xXJhJMouoaKqWfHq6bA7Wu0zbZ.nmVyXHTWLeXS', NULL),
(56, 'Robert Henderson', '1976-06-02', 'M', '03140952296', NULL, NULL, NULL, '$2y$10$gW8sUQB6bLITApL1lLWAquPmXGiYgmVoNDZJQdOzbtTvQEOstO2EK', NULL),
(57, 'Vanessa Boone', '2010-06-02', 'F', '03141683587', NULL, NULL, NULL, '$2y$10$5e6uNM9m/xUpD7GfO2wTUOYPoPgRoFmeibjaULVCvoWIIEjrfT1YO', NULL),
(58, 'Pamela Matthews', '2016-06-02', 'M', '03142414878', NULL, NULL, NULL, '$2y$10$sbAWp9ZzwUSkfd9C15UJXONf4FyT6AY78EnOVsQWbkUgpENhm479a', NULL),
(59, 'Charles Davis', '1996-06-02', 'F', '03143146169', NULL, NULL, NULL, '$2y$10$oXSTVlQVI.tnr7G5f3mJseF6Fz1Tw1iP9AQpkPSnXPF.9qxh1WBu2', NULL),
(60, 'Luis Miranda', '1961-06-02', 'M', '03143877460', NULL, NULL, NULL, '$2y$10$tNZ9Cov7CM5exHpAoG/2T.qVqQ0UAkucwv8uIr8Ve4I8Uu74UvKUW', NULL),
(61, 'Seerat Fatima', '2026-06-25', 'F', '0349072385', 'Lahore Gulbarg', 'Lahore', '50', '', 4),
(62, 'Tayyab', '2003-11-18', 'M', '03492898538', 'Multan DHA', 'Multan', '20', '', 4),
(63, 'Naseer', '2026-06-04', 'M', '03123456789', 'Lahore Bakat Market', 'Lahore', '10', '', 4);

--
-- Triggers `patients`
--
DELIMITER $$
CREATE TRIGGER `trg_log_new_patient` AFTER INSERT ON `patients` FOR EACH ROW BEGIN
  INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
  VALUES ('patients', NEW.PatientID, 'INSERT', 'New patient registered', NULL,
          CONCAT('Name:', NEW.Name, ' Gender:', COALESCE(NEW.Gender,'N/A'),
                 ' Phone:', COALESCE(NEW.Phone,'N/A')));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_log_patient_deletion` BEFORE DELETE ON `patients` FOR EACH ROW BEGIN
  INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
  VALUES ('patients', OLD.PatientID, 'DELETE', 'Patient deleted (cascade)',
          CONCAT('Name:', OLD.Name, ' DOB:', COALESCE(OLD.DateOfBirth,'N/A'),
                 ' Phone:', COALESCE(OLD.Phone,'N/A')), NULL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`) VALUES
(1, 'delete_bills', 'Can delete financial billing records'),
(2, 'delete_patients', 'Can completely remove a patient from the system'),
(3, 'manage_departments', 'Can add or edit hospital departments'),
(4, 'override_rooms', 'Can force-discharge or maintain rooms');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `rx_id` int(11) NOT NULL,
  `EnrollmentID` int(11) NOT NULL,
  `PatientID` int(11) NOT NULL,
  `DoctorID` int(11) NOT NULL,
  `instructions` text DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`rx_id`, `EnrollmentID`, `PatientID`, `DoctorID`, `instructions`, `date`) VALUES
(5, 259, 62, 15, 'Take some rest and Dont take oily things', '2026-06-03');

--
-- Triggers `prescriptions`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_treated_on_prescription` AFTER INSERT ON `prescriptions` FOR EACH ROW BEGIN
  UPDATE appointments SET Status = 'Treated'
  WHERE EnrollmentID = NEW.EnrollmentID AND Status NOT IN ('Treated', 'Cancelled');
  INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
  VALUES ('appointments', NEW.EnrollmentID, 'UPDATE',
          'Status (auto via prescription)', 'Scheduled', 'Treated');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `id` int(11) NOT NULL,
  `rx_id` int(11) NOT NULL,
  `med_id` int(11) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription_items`
--

INSERT INTO `prescription_items` (`id`, `rx_id`, `med_id`, `dosage`) VALUES
(5, 5, 12, 'Thrice a day');

--
-- Triggers `prescription_items`
--
DELIMITER $$
CREATE TRIGGER `trg_log_prescription_medicine` AFTER INSERT ON `prescription_items` FOR EACH ROW BEGIN
  DECLARE v_med_name VARCHAR(200) DEFAULT 'Unknown';
  SELECT COALESCE(name,'Unknown') INTO v_med_name FROM medicines WHERE med_id = NEW.med_id;
  INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
  VALUES ('prescription_items', NEW.id, 'INSERT', 'Medicine prescribed', NULL,
          CONCAT('Rx#', NEW.rx_id, ' Med:', v_med_name, ' Dosage:', COALESCE(NEW.dosage,'N/A')));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `receptionists`
--

CREATE TABLE `receptionists` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salary` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receptionists`
--

INSERT INTO `receptionists` (`id`, `name`, `password`, `salary`) VALUES
(1, 'Bradley Roberts', '$2y$10$XU3IMBPk0VKyhbwQ5kpI0e2JcHgQ/My6MUVfQWBbswUSHEWSc13fK', 45000.00),
(2, 'Martin Compton', '$2y$10$Az0Nwfo0VEgJ2fW5fSz3DenR7RGREF6kjtxw/JIfTLasKzJgkni8u', 50000.00),
(3, 'Nicole Bowers', '$2y$10$FPhi3miEdR15pt3Xjk0iieocmwXULALPEaIPHqlldh1FtU.Nck5uS', 54000.00),
(4, 'Sara Brown', '$2y$10$ehCh4tHrptBPqQjuyZBg4Opc0CbX1jpexZR0ggxTCbjgYq9pmkk1G', 58000.00),
(5, 'Yvonne Ayers', '$2y$10$1OYgaG.eIEcva8ZRaAauBuIiCSTCQIo.OEg.3lTncGXSHrHxs9sii', 62000.00),
(6, 'Jerry Nichols', '$2y$10$2Q68ju/V7WzE0F6B7QYrUeZdmNSIsk8qEL4RUbqyLdwq95/oAvIAO', 65000.00),
(7, 'Julie Vazquez', '$2y$10$KDmsDtgVZMtHoqHrc0RAUOBm66M0urEsTtFnCwdPE80TkgyPjTZOG', 69000.00),
(8, 'Brittany Patterson', '$2y$10$P3BrldhZFCEfqwce4jg5ae3PtWYT6BSmehsQ2LJfvQmwVc8GxUd86', 72000.00),
(9, 'Ryan Lee', '$2y$10$tWkXSe508g8LML7QGnnF8OcPhXNUR/09cKp9ZGpKrc/7RkUDyqubS', 76000.00),
(10, 'Jessica Boyd', '$2y$10$XcPBo.X3nUFfhAKhch5HfO.Q6erWHnhygWFSfVfYbm58WhnJJiEVq', 80000.00),
(11, 'Kevin Nash', '$2y$10$C.VyJas65KFxPFYcxEXdLuevwa6HR8RHrDbyArXUu6kBNUrKfv42C', 45000.00),
(12, 'Jennifer Miller', '$2y$10$bA1e.TVbuzbZ.p6z4FEA/eobDPNVOkwW11BuHHO51uFiKZst/dbk6', 65000.00),
(13, 'Robert Jenkins', '$2y$10$7psgzHJPJyiCvsagT.s0kusPEDDK/Di8fx5GbqWPopSqQeLVq8AE6', 45000.00),
(14, 'Tammy Garcia', '$2y$10$LB1RFvjCkf6E8SRMnbpvPujkssyNXbCUvtbSjhpiMj8AMB8I8oACO', 69000.00),
(15, 'Nicole Martin', '$2y$10$Kxw.k56mcFgLybShbrQmwei/l.z/uR8EgewbTQOXG8i5R7Cpk7k6y', 62000.00),
(16, 'Alicia Ryan', '$2y$10$tVhtyiH83VYxp1.x4xZtiOaem1.L8mPfHvlxd07gjSFqDl26z6YOq', 72000.00),
(17, 'Sheryl Randall', '$2y$10$63ke5TZowG4gvvea9CgjbuHll7OkPI4M4Pi6Mbw/jo1dyGqS56wze', 45000.00),
(18, 'Lisa Elliott', '$2y$10$sHmBy8icg3Gy3CPuMq/.Ve5.ByHSnZcVoursULrM.Y.c3DCSEnOIe', 76000.00),
(19, 'Paul Henry', '$2y$10$UNk9v6dD4sVjw3gsU14CjeQKXKNno2dGzNVMJJ85ufRQCVDdciE3S', 45000.00),
(20, 'Mary Alvarez', '$2y$10$CW9YkRuVlrAagNsnInjMX.wRPUPxQV/A6UHZBjQ6ehqh0FqoQEe1G', 80000.00),
(21, 'Juan Lozano', '$2y$10$21CesinXwJD657UZNAl6GeEabT28tx7AP4e62GZ28O4oNusPSvt/K', 69000.00),
(22, 'Gloria Gomez', '$2y$10$RRJeZvPYPsrMz3Gz5rCei.uxqECW0SgdYN03i/pYTIBZK.hMoBR6W', 50000.00),
(23, 'Sharon Brooks', '$2y$10$fudbP1pR7lE7yw6wKgsU4OENtRlT3lZ/qQzm5lSLn2Od2M4SsIMzW', 45000.00),
(24, 'Dr. Steven Newton', '$2y$10$sFVmxhY2Ke.e1rImUdzmN.UbKw.AHzhV2azvi6Po4hW7l6eEwjsFu', 72000.00),
(25, 'Caleb Holt', '$2y$10$MvMrqtpu3aSS0XmjL0mLmuBTdbqfqNdVFwN7aASLT.IjzNwrOuZVq', 62000.00),
(26, 'Kenneth Franco', '$2y$10$INK5UV/c.PREb6f7vfx.4O1ltZwMA.AFStUSHJmlXG67ggFs1Vj3G', 50000.00),
(27, 'Wanda Smith', '$2y$10$umd54r0dUjJRaJU3DQy/9u63fQM5w91xwaf0bqMQ9u/xh.WqWIs76', 76000.00),
(28, 'Colleen Moore', '$2y$10$N.OHbZbnE51nIgfAfFkzjeHeufbP7E6O1D5r6jiFq6ZaZ7tj28lHK', 69000.00),
(29, 'Sheri Snyder', '$2y$10$J4kO66.0Jfu/o1WsLbiIpu7UTL7whlqkq2KSUhK.ilPvPqQ/GdioO', 45000.00),
(30, 'Christopher Avery', '$2y$10$h92ddmarlcIxs3bqaTOvl.4kiXKwF36/3HDhmOq9eUUe/QzCBQrwu', 80000.00),
(31, 'Jamie Thomas', '$2y$10$6TUznj.DDJNbgU0wEaU1e.hOk13XLWmwqIM6VXRJhZm2a88wpBq/a', 45000.00),
(32, 'Melissa Anderson', '$2y$10$WJSxLzR6smuSsu.czeD3ne4Cb2mHiB63DvQ5UZKjJir5ByjRjgvWa', 72000.00),
(33, 'Marissa Boyer', '$2y$10$iusLIlZzrI9FSuSHj1mlb.LrexLJ4BGF55Q6C1RO7/1rnaVyAw6Ba', 54000.00),
(34, 'Ryan Russell III', '$2y$10$IYvaGMbcPeQDBUEnvVh.4e.X2padFpIo/eFaAy/NNThIgULqhyT0i', 50000.00),
(35, 'Christopher Sullivan', '$2y$10$smgwEut7Q5Y541fropLrQOM4DrMeQWHFQ6pNTMkgnYlZ7DK3rMlzG', 69000.00),
(36, 'Alan Williams', '$2y$10$VJFsyHdOQDxzinymlSBCwOPplQWUBrJO8AF0a7STUE55Xgofgsr3O', 76000.00),
(37, 'Carrie Johnson', '$2y$10$DZUwjCeiWXeM0s2Xs3daO.fLEMikxLTcApI9FXW1cspBI6SWShkOG', 45000.00),
(38, 'Steven Delacruz', '$2y$10$HJlE0xrZQtE7XcKUssL5ouVShCEsb08tmKmM4jG6VcNeHrqRaY2l.', 50000.00),
(39, 'Jerry Phillips', '$2y$10$/7FKbNKE9kxiRL/4VIZ2peJ0J6HDiR8jVZjSbaQ/h5QveJ4cbwBDO', 54000.00),
(40, 'Robert Johnson', '$2y$10$xLneWUykI5Q/A98ZUgY8J.yAU3lp.Uuuy/TaGGWiYz4ORepnHPZj6', 80000.00),
(41, 'Mitchell Phillips', '$2y$10$MYjcWsM0qWUAOpDHXYxrVuQkY00.6U1B0zMfm6P.owkVfDsywgYra', 45000.00),
(42, 'Jeff Young', '$2y$10$ybXpJewwhNaBpapXwOxnve3bJSOSUowG4.DYjqKaNd5QiAxy.sz0G', 69000.00),
(43, 'Kevin Cox', '$2y$10$gd..hwuX3a/CtRtbwVLBau4DnHau2VHUNgCqbA628l0i8l0uVYjPq', 45000.00),
(44, 'Gregory Richards', '$2y$10$5swU4seunwLDb.sGdLSE6OdS7rlLx8HavhcLzU5LRCzKuqkz3at46', 58000.00),
(45, 'Jesse Black', '$2y$10$/ssrTlJeotL8qLOEdjfXj.gmi5eZTLATzMwdKKs6tnkQ.b2aivDRu', 76000.00),
(46, 'Matthew Chambers', '$2y$10$nFwSB8Uu0mlhQ/k7HNJ8X.cnnMRsYahgimkZkYsoStt1VXJNNtfty', 50000.00),
(47, 'Mrs. Elaine Johnson', '$2y$10$l6GXFYkjBpweJzBXF.fuMeApro9y6AB/rwLOAkqPtcEVUqKMyOLWK', 45000.00),
(48, 'Elijah Lowe', '$2y$10$vkAYtB/lO3FdLv.0k8afMuNVB6Ci9wjdEdpAD9sAsh9BMuqbxAYf2', 72000.00),
(49, 'Robin Cruz', '$2y$10$Aub7.CfPhkIPC4DtZ/y09unJgT9sKdT0fi8y.zwUY7I8JRiiuJH0y', 69000.00),
(50, 'Kayla Lawrence', '$2y$10$jRk3wqk6XZkKYztS6H4.ruh1dFYT/JrctWsrEH9rECHmBqHL7IGqW', 80000.00),
(51, 'Kimberly Kirby', '$2y$10$QqQe4JQfuXf.ch2zrn1tTOaR7iwOgvrYeXtw846tkiQeL8ui.RBNK', 54000.00),
(52, 'Lindsay Hardy', '$2y$10$7iykx5OLFPOkMkkYz.8S2e4iDtjf0M6H.zjgkERE3P/5iaYBkPgwy', 58000.00),
(53, 'Mary Higgins', '$2y$10$lSQ.eZ96lJVtzREodLTfJOoVrk9M8hukVGkGhpZm31Bvco1soYMjS', 45000.00),
(54, 'Crystal Kelley', '$2y$10$n3.fPHUfNBWeV8y/sN/KvO3ebhGgh2obi1pE9SNR1ZHUc8Eb2ukiC', 76000.00),
(55, 'Austin Jacobs', '$2y$10$q7bmGfTJm2tG0WgVj8mN0.n8qOSs4LBlFjMe/O8DlSxoFIUDGOUHS', 62000.00),
(56, 'Dr. Christopher Glenn MD', '$2y$10$BT3WpI5aJdsiHmEi31ip5.JoTw7LzUA6RhtbxYdPyJoLLiszthIUS', 72000.00),
(57, 'Joseph Thomas MD', '$2y$10$E1.IEGsI0WPkQHX72nmu8e0cCb1CdmSBeHrdNYzDYVFRJjSzkbo1G', 54000.00),
(58, 'Eric Johnson', '$2y$10$YFFHo0iYvqGGH1Lz0q2VDuH/9YeMBhkVS8NlGfMmH237U19de2eBW', 50000.00),
(59, 'Juan Hernandez', '$2y$10$.7dsVyvVUTAXBWaXMzLPEOruaj50GgnekQ31AqkIByCXWrJDJqBV.', 45000.00),
(60, 'Richard Duncan', '$2y$10$rdpJQ4lyN6XBVuFVF0DGuOv2oGYRZYKCqF9qGhvJnTLpnQjQrIFT.', 80000.00);

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `RoomID` int(11) NOT NULL,
  `RoomType` varchar(50) NOT NULL,
  `RoomStatus` enum('Available','Occupied','Maintenance') DEFAULT 'Available',
  `price` decimal(10,2) NOT NULL,
  `assignedTo` int(11) DEFAULT NULL,
  `enrollmentId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`RoomID`, `RoomType`, `RoomStatus`, `price`, `assignedTo`, `enrollmentId`) VALUES
(1, 'General', 'Available', 5000.00, NULL, NULL),
(2, 'Private', 'Occupied', 9000.00, 7, 7),
(3, 'ICU', 'Available', 15000.00, NULL, NULL),
(4, 'Semi-Private', 'Available', 7000.00, NULL, NULL),
(5, 'Emergency', 'Occupied', 12000.00, 22, 22),
(6, 'General', 'Occupied', 5500.00, 25, 25),
(7, 'Private', 'Occupied', 9500.00, 31, 31),
(8, 'ICU', 'Occupied', 16000.00, 35, 35),
(9, 'Semi-Private', 'Occupied', 7500.00, 40, 40),
(10, 'Emergency', 'Occupied', 13000.00, 44, 44),
(11, 'General', 'Available', 4800.00, NULL, NULL),
(12, 'Private', 'Occupied', 8500.00, 51, 51),
(13, 'ICU', 'Occupied', 14000.00, 55, 55),
(14, 'Semi-Private', 'Occupied', 6500.00, 58, 58),
(15, 'Emergency', 'Available', 11000.00, NULL, NULL),
(16, 'General', 'Available', 5200.00, NULL, NULL),
(17, 'Private', 'Available', 9200.00, NULL, NULL),
(18, 'Semi-Private', 'Occupied', 7200.00, 63, 261),
(19, 'General', 'Available', 4900.00, NULL, NULL),
(20, 'Private', 'Available', 8800.00, NULL, NULL);

--
-- Triggers `rooms`
--
DELIMITER $$
CREATE TRIGGER `trg_log_room_status` AFTER UPDATE ON `rooms` FOR EACH ROW BEGIN
  IF OLD.RoomStatus <> NEW.RoomStatus THEN
    INSERT INTO audit_log (table_name, record_id, action, field_changed, old_value, new_value)
    VALUES ('rooms', NEW.RoomID, 'UPDATE', 'RoomStatus',
            CONCAT(OLD.RoomStatus, ' [', OLD.RoomType, ']'),
            CONCAT(NEW.RoomStatus, ' [', NEW.RoomType, ']',
                   IF(NEW.assignedTo IS NOT NULL, CONCAT(' Pat#', NEW.assignedTo), '')));
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `receptionist_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `password`, `role`, `ref_id`, `receptionist_id`) VALUES
(1, 'Admin', '$2y$10$tZ2E7y0sQ3H8rW3o0oW0eu7tWv8t.80p4i8Uu1.g3rX/g8I2p0qO', 'Admin', NULL, NULL),
(2, 'System Administrator', '$2y$10$qLazSLX9044XTmLnqPXoteexxW.MVlA714HHYbzK9m.hqeGGR5x6.', 'Admin', NULL, NULL),
(3, 'Kathryn Norman', '$2y$10$rep4gcLrBG8cCdHeMNis0.YNviCQELC7YAWu4C.BYO9ist1MkKgQ2', 'Doctor', 1, NULL),
(4, 'Sarah Harper DVM', '$2y$10$cGFAAFugOthc8FmBUI4LveKCDdQz.zwNxEtYLZmIRQqSDxaAHv8NW', 'Doctor', 2, NULL),
(5, 'Jessica Nelson', '$2y$10$EfMxa30amMkys/sbze2Xzu3SUuzRFv9crIZoRZNvcOcuLQ4vyW3IC', 'Doctor', 3, NULL),
(6, 'Jennifer Guzman', '$2y$10$VlUkxwlztJ7OSBqpL0wPUupfedrxI8tRK/I6/B9PEqVUxYmka9xca', 'Doctor', 4, NULL),
(7, 'Kyle Randolph', '$2y$10$EEKh2r2UaVaRZTkne05Xt.SJFx78.TNqp4dWIlUaIZzOcSV0gg7yK', 'Doctor', 5, NULL),
(8, 'Cheryl Hodge', '$2y$10$xRJTwElOfY4CthjQXR7Okuygg/Ee/ZzK.rO7JzJZSbhdLmpxpElEu', 'Doctor', 6, NULL),
(9, 'Krista Nelson', '$2y$10$DWs9IuFgCs4uLmdBRtEe.uij3Hq0J.75bBaULBBr1p5h8vwcyncg.', 'Doctor', 7, NULL),
(10, 'Robin Brock', '$2y$10$5xRk6NtQjzhGImyK5mkOW.ZW9.p3e01IZCEykzasW2Hh8jmBJTMLe', 'Doctor', 8, NULL),
(11, 'Darin Lewis', '$2y$10$yzMqjLsA/9WgR9t/o/jk5uGYonCfuiXZgqB2H8h1WclM90xw0HXWe', 'Doctor', 9, NULL),
(12, 'Michael Wolf', '$2y$10$LKZ94ceFBt8DCWMslMgp9.ZX36MmNyYei/rTO09V8bCNCSo4JEBk2', 'Doctor', 10, NULL),
(13, 'Robert Taylor', '$2y$10$2Ewu23.hYz6hvRaMbUSfRO8TTPBnReIRn6W.jWKMyS/.EZ18POqO.', 'Doctor', 11, NULL),
(14, 'Terri Flores', '$2y$10$l.M/SLaFQJFVOD98YSn1Ye4PHL6ZOLfrPJi13WAqBTVxqVO6KF8gG', 'Doctor', 12, NULL),
(15, 'Jamie Ball', '$2y$10$Nxgn7umjWPEeUxjfQ1tBoO0C37EayJFSH9LxRRQxBN2u432W7BQGy', 'Doctor', 13, NULL),
(16, 'Crystal Collins', '$2y$10$Djd0rRt5HvZzWxYqqrwOBOKtaqFbamoTspWBiym5i0Untsl52Kmkm', 'Doctor', 14, NULL),
(17, 'Omar Calderon', '$2y$10$FTb9npLgUsKlyaQ2IaUG0et9pR3PQe/OwByP7cMrN.vVufV42JyFy', 'Doctor', 15, NULL),
(18, 'Amanda Johnson', '$2y$10$wh85NryEaHi.Q2o5aN2w/uyVhMSHUpykMhty8JhVmy5zj.jR4GZwi', 'Doctor', 16, NULL),
(19, 'Jenna Taylor', '$2y$10$4AGgkOIIzsBaAESBEwx6NepKjwKgvW2DeZ0NutgD.pREC4JqnNbiu', 'Doctor', 17, NULL),
(20, 'Kimberly Gomez', '$2y$10$W2vFNenjKK0agHF.gft8l.D7pYd/6D3lAAwEgVw/4B98BueABvQy6', 'Doctor', 18, NULL),
(21, 'Luis Jones', '$2y$10$3hAmB1LcnIdzIgv1Yq0C3.Eifu.qShCeNfeli4QjIWFuVeakLo.16', 'Doctor', 19, NULL),
(22, 'Jennifer Robertson', '$2y$10$MGmNnruQuzZBEg0hnWqcjOgN0orwRfOom3B142g7PYj0VK3W7jYkC', 'Doctor', 20, NULL),
(23, 'Chad Lopez', '$2y$10$G15xrVR.WwVlzrTlr3hGqePirj8Teka/PZsEMcFcr7u3uu8bAl4cq', 'Doctor', 21, NULL),
(24, 'Morgan Harris', '$2y$10$Yp0rrkU.5cm0XwPfT9/aYuV2ZERJYCUccSHpykI/rRjK2iw3.7PsW', 'Doctor', 22, NULL),
(25, 'Donna Campbell', '$2y$10$K0srYtzoRAPPBMnsfAsKnOAWntP3I/kY70GHptv0dPRhD4abO92nK', 'Doctor', 23, NULL),
(26, 'Tammy Murphy', '$2y$10$rXd9xVH0X0VukXmTuPjcbOotXZXmeWVTGuXlMWf0oKYJAuQCrsU5y', 'Doctor', 24, NULL),
(27, 'Jennifer Patel', '$2y$10$Tn2WDfyTYkhIgh62qfYdWurq32Fe.dF.IwIAOsu1axTbpxJQEgLqe', 'Doctor', 25, NULL),
(28, 'Donald Coleman', '$2y$10$23JYSLke501yovUpI5RwoOyiUXTGxbvUNq59ts1T1Z9QWz8imARPO', 'Doctor', 26, NULL),
(29, 'Austin Figueroa', '$2y$10$QfmrhmXqYzIwas85Tln/E.uKw.ygbyfSTn29tHNBUEvgjsHHE3fqa', 'Doctor', 27, NULL),
(30, 'Lauren Mack', '$2y$10$rUd2/rqTwF1FSAegOHPQf.oRancj4ikads.bVb70PB05uvdH.qkkW', 'Doctor', 28, NULL),
(31, 'Erik Campbell', '$2y$10$r365BBszMouESENCJh1zJuyfN/OyikM32NwbiCJsJ7wQViDYJiEjy', 'Doctor', 29, NULL),
(32, 'Phillip Johnson', '$2y$10$1gCESID57AqCkUHWnbhCFexq6GFv0qh7ZPZDBKP/xMNokCxKrQBC2', 'Doctor', 30, NULL),
(33, 'Jennifer Smith', '$2y$10$vgj.UDb2gI6ItlgTVAE2OOEXqdHsjftkUwhQD4YxxN71zEFA6VwHu', 'Doctor', 31, NULL),
(34, 'Joseph Hicks', '$2y$10$F/6QL9ZUpKSLquEQ0/jX2eX0vHTkr9hknjf1o9vxxfmBKVzREVvba', 'Doctor', 32, NULL),
(35, 'Kelly Smith', '$2y$10$EFwci1uf5Nj9KYcFnrjRguuRzJs/flpLgT5MmHRyt0BMLqDiumrZS', 'Doctor', 33, NULL),
(36, 'Rachel Harrell', '$2y$10$myBqdMs/WUieF7SuYEizq./HJaH38dn0ZINEfDToxAkPFSfsvhyUC', 'Doctor', 34, NULL),
(37, 'Kevin Warren', '$2y$10$.Baj6HFKTsmtW0sFw/4h8ulH5eYMyNHgeQDKK4kbtXTE74MqgS.8S', 'Doctor', 35, NULL),
(38, 'Christopher Rivera', '$2y$10$l2Np8UgWZqHYdr3kOFwCd.RxC/M3zBX3pn8ZNgNHoGUwLL2jRLfYW', 'Doctor', 36, NULL),
(39, 'Frederick Stephens', '$2y$10$W3ulCVIYBAp2lq0PHRtoL.NpXOYIVcXNkmTXjKf.Ok0NKNx4LMkte', 'Doctor', 37, NULL),
(40, 'Justin Holland', '$2y$10$tB/2KoR9GGDIAfs41NZ.X.x99cSE2Q4886JNYznI/EN/q/uP0C2r6', 'Doctor', 38, NULL),
(41, 'Thomas Ray', '$2y$10$DMx9cBrQyoCzvPqMgB2yyuljstb2xokAFKuGIfYuR5roxwBKah8k2', 'Doctor', 39, NULL),
(42, 'Christopher Powell', '$2y$10$8tK.fS/rPhu0WvUpvu7k/u3WgJvtXidnaWHQYHtv7JzkGwn.tFIs2', 'Doctor', 40, NULL),
(43, 'Adam Ellis', '$2y$10$vUBB7l46rRURKYfpitFB3.c.V546rG.wObqJBUR6bWeZwBCa5IceG', 'Doctor', 41, NULL),
(44, 'Tanya Stanton', '$2y$10$pmsBMk.8WX1AfQXmmoWi/u5.4w8yq1L3HhmGOVGLjz0IuX7On169W', 'Doctor', 42, NULL),
(45, 'Jack Young', '$2y$10$EcuTXoLv3QFyai7kqBESSuAPlSprp7khFCzkT.hlPduQriYP5mxci', 'Doctor', 43, NULL),
(46, 'Michelle Martinez', '$2y$10$czzKbEIe0fTdh8IMlN37wumj1oYX7Sk3uIIFgBvkYRFFc6UCG8VgK', 'Doctor', 44, NULL),
(47, 'Jay Mullins', '$2y$10$.Zq8I86UcPLtfORYOyjrXOqlwz5NbO.T5nr9Bkv368vQV/XHrqucO', 'Doctor', 45, NULL),
(48, 'Amanda Watson', '$2y$10$cD9j7fOngkFb6U2Woo7E/.sBQ1ZckBxSeaCK5eIw8oshynuQfMMQ2', 'Doctor', 46, NULL),
(49, 'Emily Henry', '$2y$10$P8qnR3QKVHaCh1ghTYGxUecabkG7U6LISh65cfs5dsIhRcsR8ed1G', 'Doctor', 47, NULL),
(50, 'Thomas Fernandez', '$2y$10$xYBk6IBllBzwwWEx21LPj.2f8Axwr2Od9z5FZUxM8zoU2ExAjauN.', 'Doctor', 48, NULL),
(51, 'Judy Tran', '$2y$10$y84c1I9wPoLxdKvL0dxGZ.LBvOGUiIwNJO/iXgDC6SUMrbkwqEpnC', 'Doctor', 49, NULL),
(52, 'Adriana Jackson', '$2y$10$r.bGclLUn/zqLHPHCEY24OM72HM41guRbtGzGDBzGWUAmU4A0y32y', 'Doctor', 50, NULL),
(53, 'Travis Fisher', '$2y$10$QHoXz.l4WM142gkWpEfQXOrSAMKGZhE51Ez35ztxOjJjwypPpi7zO', 'Doctor', 51, NULL),
(54, 'Lisa Glover', '$2y$10$JpapToeyXTYU8Wc.RJ7twupTf17ILiNSua3nTmigd4rLnVW9naIgm', 'Doctor', 52, NULL),
(55, 'Holly Taylor', '$2y$10$D.ulL7bDYl7TnuADUFP2GunfO1DUfmw.JZoCO3XccXFOqNMpcJik6', 'Doctor', 53, NULL),
(56, 'Robert Long', '$2y$10$i6Nil5AfioKjBfFOAFSW6.V5yySAjLR/CgkMr.i.9B56qit1W2dNm', 'Doctor', 54, NULL),
(57, 'Isaac Smith', '$2y$10$Nm64cryeP/fLdnlcAc.UMexLQmQx0yO5KzwjlPJkNlKt8PWanD1x6', 'Doctor', 55, NULL),
(58, 'Christina Kelly', '$2y$10$ehXbbkUwTqp/3mBTVuip5e2xLDefdqEvCSD1ewteJy.jskgJBQep6', 'Doctor', 56, NULL),
(59, 'Lauren Rice', '$2y$10$bp/j7CnOlOQzAGR74obAEOiOOYY5HIsK6S0m173cowLpANlgsaYg2', 'Doctor', 57, NULL),
(60, 'Troy Garza Jr.', '$2y$10$AxEBAhPJY6Xwq4qpaFRJJuUG73a2nS6gNCqUqoljJ4kVcOoSaxEB2', 'Doctor', 58, NULL),
(61, 'Leslie Wall', '$2y$10$v0s6mmmjN5SO/DduFp.C8OZd8/ABBqjI5LonwNm8Uw8BsAKvDduLm', 'Doctor', 59, NULL),
(63, 'Bradley Roberts', '$2y$10$XU3IMBPk0VKyhbwQ5kpI0e2JcHgQ/My6MUVfQWBbswUSHEWSc13fK', 'Receptionist', 1, 1),
(64, 'Martin Compton', '$2y$10$Az0Nwfo0VEgJ2fW5fSz3DenR7RGREF6kjtxw/JIfTLasKzJgkni8u', 'Receptionist', 2, 2),
(65, 'Nicole Bowers', '$2y$10$FPhi3miEdR15pt3Xjk0iieocmwXULALPEaIPHqlldh1FtU.Nck5uS', 'Receptionist', 3, 3),
(66, 'Sara Brown', '$2y$10$ehCh4tHrptBPqQjuyZBg4Opc0CbX1jpexZR0ggxTCbjgYq9pmkk1G', 'Receptionist', 4, 4),
(67, 'Yvonne Ayers', '$2y$10$1OYgaG.eIEcva8ZRaAauBuIiCSTCQIo.OEg.3lTncGXSHrHxs9sii', 'Receptionist', 5, 5),
(68, 'Jerry Nichols', '$2y$10$2Q68ju/V7WzE0F6B7QYrUeZdmNSIsk8qEL4RUbqyLdwq95/oAvIAO', 'Receptionist', 6, 6),
(69, 'Julie Vazquez', '$2y$10$KDmsDtgVZMtHoqHrc0RAUOBm66M0urEsTtFnCwdPE80TkgyPjTZOG', 'Receptionist', 7, 7),
(70, 'Brittany Patterson', '$2y$10$P3BrldhZFCEfqwce4jg5ae3PtWYT6BSmehsQ2LJfvQmwVc8GxUd86', 'Receptionist', 8, 8),
(71, 'Ryan Lee', '$2y$10$tWkXSe508g8LML7QGnnF8OcPhXNUR/09cKp9ZGpKrc/7RkUDyqubS', 'Receptionist', 9, 9),
(72, 'Jessica Boyd', '$2y$10$XcPBo.X3nUFfhAKhch5HfO.Q6erWHnhygWFSfVfYbm58WhnJJiEVq', 'Receptionist', 10, 10),
(73, 'Kevin Nash', '$2y$10$C.VyJas65KFxPFYcxEXdLuevwa6HR8RHrDbyArXUu6kBNUrKfv42C', 'Receptionist', 11, 11),
(74, 'Jennifer Miller', '$2y$10$bA1e.TVbuzbZ.p6z4FEA/eobDPNVOkwW11BuHHO51uFiKZst/dbk6', 'Receptionist', 12, 12),
(75, 'Robert Jenkins', '$2y$10$7psgzHJPJyiCvsagT.s0kusPEDDK/Di8fx5GbqWPopSqQeLVq8AE6', 'Receptionist', 13, 13),
(76, 'Tammy Garcia', '$2y$10$LB1RFvjCkf6E8SRMnbpvPujkssyNXbCUvtbSjhpiMj8AMB8I8oACO', 'Receptionist', 14, 14),
(77, 'Nicole Martin', '$2y$10$Kxw.k56mcFgLybShbrQmwei/l.z/uR8EgewbTQOXG8i5R7Cpk7k6y', 'Receptionist', 15, 15),
(78, 'Alicia Ryan', '$2y$10$tVhtyiH83VYxp1.x4xZtiOaem1.L8mPfHvlxd07gjSFqDl26z6YOq', 'Receptionist', 16, 16),
(79, 'Sheryl Randall', '$2y$10$63ke5TZowG4gvvea9CgjbuHll7OkPI4M4Pi6Mbw/jo1dyGqS56wze', 'Receptionist', 17, 17),
(80, 'Lisa Elliott', '$2y$10$sHmBy8icg3Gy3CPuMq/.Ve5.ByHSnZcVoursULrM.Y.c3DCSEnOIe', 'Receptionist', 18, 18),
(81, 'Paul Henry', '$2y$10$UNk9v6dD4sVjw3gsU14CjeQKXKNno2dGzNVMJJ85ufRQCVDdciE3S', 'Receptionist', 19, 19),
(82, 'Mary Alvarez', '$2y$10$CW9YkRuVlrAagNsnInjMX.wRPUPxQV/A6UHZBjQ6ehqh0FqoQEe1G', 'Receptionist', 20, 20),
(83, 'Juan Lozano', '$2y$10$21CesinXwJD657UZNAl6GeEabT28tx7AP4e62GZ28O4oNusPSvt/K', 'Receptionist', 21, 21),
(84, 'Gloria Gomez', '$2y$10$RRJeZvPYPsrMz3Gz5rCei.uxqECW0SgdYN03i/pYTIBZK.hMoBR6W', 'Receptionist', 22, 22),
(85, 'Sharon Brooks', '$2y$10$fudbP1pR7lE7yw6wKgsU4OENtRlT3lZ/qQzm5lSLn2Od2M4SsIMzW', 'Receptionist', 23, 23),
(86, 'Dr. Steven Newton', '$2y$10$sFVmxhY2Ke.e1rImUdzmN.UbKw.AHzhV2azvi6Po4hW7l6eEwjsFu', 'Receptionist', 24, 24),
(87, 'Caleb Holt', '$2y$10$MvMrqtpu3aSS0XmjL0mLmuBTdbqfqNdVFwN7aASLT.IjzNwrOuZVq', 'Receptionist', 25, 25),
(88, 'Kenneth Franco', '$2y$10$INK5UV/c.PREb6f7vfx.4O1ltZwMA.AFStUSHJmlXG67ggFs1Vj3G', 'Receptionist', 26, 26),
(89, 'Wanda Smith', '$2y$10$umd54r0dUjJRaJU3DQy/9u63fQM5w91xwaf0bqMQ9u/xh.WqWIs76', 'Receptionist', 27, 27),
(90, 'Colleen Moore', '$2y$10$N.OHbZbnE51nIgfAfFkzjeHeufbP7E6O1D5r6jiFq6ZaZ7tj28lHK', 'Receptionist', 28, 28),
(91, 'Sheri Snyder', '$2y$10$J4kO66.0Jfu/o1WsLbiIpu7UTL7whlqkq2KSUhK.ilPvPqQ/GdioO', 'Receptionist', 29, 29),
(92, 'Christopher Avery', '$2y$10$h92ddmarlcIxs3bqaTOvl.4kiXKwF36/3HDhmOq9eUUe/QzCBQrwu', 'Receptionist', 30, 30),
(93, 'Jamie Thomas', '$2y$10$6TUznj.DDJNbgU0wEaU1e.hOk13XLWmwqIM6VXRJhZm2a88wpBq/a', 'Receptionist', 31, 31),
(94, 'Melissa Anderson', '$2y$10$WJSxLzR6smuSsu.czeD3ne4Cb2mHiB63DvQ5UZKjJir5ByjRjgvWa', 'Receptionist', 32, 32),
(95, 'Marissa Boyer', '$2y$10$iusLIlZzrI9FSuSHj1mlb.LrexLJ4BGF55Q6C1RO7/1rnaVyAw6Ba', 'Receptionist', 33, 33),
(96, 'Ryan Russell III', '$2y$10$IYvaGMbcPeQDBUEnvVh.4e.X2padFpIo/eFaAy/NNThIgULqhyT0i', 'Receptionist', 34, 34),
(97, 'Christopher Sullivan', '$2y$10$smgwEut7Q5Y541fropLrQOM4DrMeQWHFQ6pNTMkgnYlZ7DK3rMlzG', 'Receptionist', 35, 35),
(98, 'Alan Williams', '$2y$10$VJFsyHdOQDxzinymlSBCwOPplQWUBrJO8AF0a7STUE55Xgofgsr3O', 'Receptionist', 36, 36),
(99, 'Carrie Johnson', '$2y$10$DZUwjCeiWXeM0s2Xs3daO.fLEMikxLTcApI9FXW1cspBI6SWShkOG', 'Receptionist', 37, 37),
(100, 'Steven Delacruz', '$2y$10$HJlE0xrZQtE7XcKUssL5ouVShCEsb08tmKmM4jG6VcNeHrqRaY2l.', 'Receptionist', 38, 38),
(101, 'Jerry Phillips', '$2y$10$/7FKbNKE9kxiRL/4VIZ2peJ0J6HDiR8jVZjSbaQ/h5QveJ4cbwBDO', 'Receptionist', 39, 39),
(102, 'Robert Johnson', '$2y$10$xLneWUykI5Q/A98ZUgY8J.yAU3lp.Uuuy/TaGGWiYz4ORepnHPZj6', 'Receptionist', 40, 40),
(103, 'Mitchell Phillips', '$2y$10$MYjcWsM0qWUAOpDHXYxrVuQkY00.6U1B0zMfm6P.owkVfDsywgYra', 'Receptionist', 41, 41),
(104, 'Jeff Young', '$2y$10$ybXpJewwhNaBpapXwOxnve3bJSOSUowG4.DYjqKaNd5QiAxy.sz0G', 'Receptionist', 42, 42),
(105, 'Kevin Cox', '$2y$10$gd..hwuX3a/CtRtbwVLBau4DnHau2VHUNgCqbA628l0i8l0uVYjPq', 'Receptionist', 43, 43),
(106, 'Gregory Richards', '$2y$10$5swU4seunwLDb.sGdLSE6OdS7rlLx8HavhcLzU5LRCzKuqkz3at46', 'Receptionist', 44, 44),
(107, 'Jesse Black', '$2y$10$/ssrTlJeotL8qLOEdjfXj.gmi5eZTLATzMwdKKs6tnkQ.b2aivDRu', 'Receptionist', 45, 45),
(108, 'Matthew Chambers', '$2y$10$nFwSB8Uu0mlhQ/k7HNJ8X.cnnMRsYahgimkZkYsoStt1VXJNNtfty', 'Receptionist', 46, 46),
(109, 'Mrs. Elaine Johnson', '$2y$10$l6GXFYkjBpweJzBXF.fuMeApro9y6AB/rwLOAkqPtcEVUqKMyOLWK', 'Receptionist', 47, 47),
(110, 'Elijah Lowe', '$2y$10$vkAYtB/lO3FdLv.0k8afMuNVB6Ci9wjdEdpAD9sAsh9BMuqbxAYf2', 'Receptionist', 48, 48),
(111, 'Robin Cruz', '$2y$10$Aub7.CfPhkIPC4DtZ/y09unJgT9sKdT0fi8y.zwUY7I8JRiiuJH0y', 'Receptionist', 49, 49),
(112, 'Kayla Lawrence', '$2y$10$jRk3wqk6XZkKYztS6H4.ruh1dFYT/JrctWsrEH9rECHmBqHL7IGqW', 'Receptionist', 50, 50),
(113, 'Kimberly Kirby', '$2y$10$QqQe4JQfuXf.ch2zrn1tTOaR7iwOgvrYeXtw846tkiQeL8ui.RBNK', 'Receptionist', 51, 51),
(114, 'Lindsay Hardy', '$2y$10$7iykx5OLFPOkMkkYz.8S2e4iDtjf0M6H.zjgkERE3P/5iaYBkPgwy', 'Receptionist', 52, 52),
(115, 'Mary Higgins', '$2y$10$lSQ.eZ96lJVtzREodLTfJOoVrk9M8hukVGkGhpZm31Bvco1soYMjS', 'Receptionist', 53, 53),
(116, 'Crystal Kelley', '$2y$10$n3.fPHUfNBWeV8y/sN/KvO3ebhGgh2obi1pE9SNR1ZHUc8Eb2ukiC', 'Receptionist', 54, 54),
(117, 'Austin Jacobs', '$2y$10$q7bmGfTJm2tG0WgVj8mN0.n8qOSs4LBlFjMe/O8DlSxoFIUDGOUHS', 'Receptionist', 55, 55),
(118, 'Dr. Christopher Glenn MD', '$2y$10$BT3WpI5aJdsiHmEi31ip5.JoTw7LzUA6RhtbxYdPyJoLLiszthIUS', 'Receptionist', 56, 56),
(119, 'Joseph Thomas MD', '$2y$10$E1.IEGsI0WPkQHX72nmu8e0cCb1CdmSBeHrdNYzDYVFRJjSzkbo1G', 'Receptionist', 57, 57),
(120, 'Eric Johnson', '$2y$10$YFFHo0iYvqGGH1Lz0q2VDuH/9YeMBhkVS8NlGfMmH237U19de2eBW', 'Receptionist', 58, 58),
(121, 'Juan Hernandez', '$2y$10$.7dsVyvVUTAXBWaXMzLPEOruaj50GgnekQ31AqkIByCXWrJDJqBV.', 'Receptionist', 59, 59),
(122, 'Richard Duncan', '$2y$10$rdpJQ4lyN6XBVuFVF0DGuOv2oGYRZYKCqF9qGhvJnTLpnQjQrIFT.', 'Receptionist', 60, 60),
(123, 'Isaac Butler', '$2y$10$Nlzo0aqKjinltpThZ9PpFe5x64b4z9RrY2SqhZqdXK/xcCq6xthA6', 'Patient', 1, NULL),
(124, 'Brian Taylor', '$2y$10$PzL0bOj5vO/dEQYK.q1tI.fQcjMdMAF3EIlypfIa3MQyZ9/vaHtfG', 'Patient', 2, NULL),
(125, 'Leslie Moore', '$2y$10$sdgQIaZFjnLnna35ixmdGOorxP4q1YLinVSkXtxwi2gh4e9yZRhtu', 'Patient', 3, NULL),
(126, 'Ashley Pena', '$2y$10$k4UsnvWGbbocbQ3LnWKo1eCgLp3JXNBaB.6mJ20pZr2E7MDF4z1o2', 'Patient', 4, NULL),
(127, 'John Hunt', '$2y$10$Vq3.QH/Np78EYVa3CNvrk.E.nSfCiJnSeN1MOHrnPnpt4IAfVn2oi', 'Patient', 5, NULL),
(128, 'Samuel Fuentes', '$2y$10$jaMdgL1HlYgSODMU430LTeci1dyuJWMLGzW.b2c5ccKDNnPWQlFDa', 'Patient', 6, NULL),
(129, 'Jonathan Bowman', '$2y$10$pLbLSybCWuD74XyAsaQKTeplOD0VOH8kZ0EYatnhTBTf7k1.v/dqa', 'Patient', 7, NULL),
(130, 'Alexander Mcgrath', '$2y$10$mnPSMezDxuJI9Czwz6Wz1uy8nf4im.pJXJT1eLSaKslJ4UA.WMsSu', 'Patient', 8, NULL),
(131, 'Robert Wolfe', '$2y$10$e7Y/UCOFST0Ytl1WG7XH1ObNOG.gcIvHOGRU8hlBDeB/EZN./I8/2', 'Patient', 9, NULL),
(132, 'Cole Martinez', '$2y$10$c0owVqUUxrdoRUjgEXUPGu8bcVcnOfhX2s9fi6nCSav2bAIuMlLhG', 'Patient', 10, NULL),
(133, 'Sarah Montes', '$2y$10$jLXu3WWo5jjHxVhZZFRziuuEZuKyROLwVYbyohAiGXgZ0v9mJtASi', 'Patient', 11, NULL),
(134, 'Melissa Nelson', '$2y$10$KCSVg1MpTb1ONvGZgBXIy.fFlc5rUlPruuBy2dFRZ9zOIstUtrbcO', 'Patient', 12, NULL),
(135, 'Julia Obrien', '$2y$10$xrqzzANHn/mWhV9rAwYPk.bWjDDKbfiIiaemjSJl1yBWc/gE82.BC', 'Patient', 13, NULL),
(136, 'Joshua Harris', '$2y$10$u2B1I4FsBfvJLhdSYpPR8O6OcAgxWbQDxZcyG4OS/cWoeam7IGTGm', 'Patient', 14, NULL),
(137, 'Joseph Allen', '$2y$10$kTH.sFaQIeNr9sXLq2VZUeomYm.jdQOESf88shZdzBN4sCb1WPJaC', 'Patient', 15, NULL),
(138, 'Jason Johnson', '$2y$10$nX9NSKJZaUa.MueBYVDZ2e8y/CMZRxqY1Vc32s4jC292IkqBeHDue', 'Patient', 16, NULL),
(139, 'Nichole Ayers', '$2y$10$b1a.PHMXoJjZN1hqicZ/auA0sIbTrlWEAWMdNi5vmJvm7iTeHsye6', 'Patient', 17, NULL),
(140, 'Gabrielle Bishop', '$2y$10$zs1uTd8u.SMfmgDuNCNcweAi6Lgdw010Z572PJ4sxsJNtflehpkue', 'Patient', 18, NULL),
(141, 'Daniel Mcgee', '$2y$10$mZJBuIVOTzPxo0hCntUgzOD70tUxqgfGZO5bCsieEZTIS1YTNWNRq', 'Patient', 19, NULL),
(142, 'Angela Evans', '$2y$10$N54ra9VVnm.LJBYPD3YmIuCoEpLQo1e/KQVVNe99CxVJ1aWPnX4a.', 'Patient', 20, NULL),
(143, 'Kristen Jones MD', '$2y$10$kNKvMTPVN1ewPPAY5poFi.ziMzsWwM3yifVJ2tT5Z9Rg3oPTF6Upq', 'Patient', 21, NULL),
(144, 'Tammy Carter', '$2y$10$MGCycScdfSg2pU7k0YDkg.toEnvaXvaGVCsWR.Fhyfns6vtqnFw4y', 'Patient', 22, NULL),
(145, 'Michael Cox', '$2y$10$kXeg3fAenI8zHVlhq/D2LOqN0kmGaNo.1SbeElLPtQWZZMqeEUrUq', 'Patient', 23, NULL),
(146, 'Christopher Patterson', '$2y$10$DbKL.Mpfpv8FUqrgH4GRYuUyC29lu3xtMAWWn.tRWvXxjhTQMt2AK', 'Patient', 24, NULL),
(147, 'Susan Hill', '$2y$10$xl7YlEuJth0WaESzfN0nh.ntEULOAe1zAAaws3o/vOxZHh7UjU852', 'Patient', 25, NULL),
(148, 'Jeffrey Mckay', '$2y$10$uywpxGgWcybUlWJoORmzWOfvdC7L.kvDqAziNnEsmdtqpPbayLIuu', 'Patient', 26, NULL),
(149, 'Carol Ellis', '$2y$10$gGLDNYvk1D6HKSNqfOuP7.RgdV3x31JIFEXrgb4.DoPlU1hinnK7y', 'Patient', 27, NULL),
(150, 'Cynthia Hardy', '$2y$10$TbW4KpydHLKTsSHuziGRPu3S4W0kwQFwlbGlfdRSPDUQBcZKn/N.S', 'Patient', 28, NULL),
(151, 'John Miller', '$2y$10$4nOhCPQ7UxskbZOeDhpogurr3.AfBNbx3gqLk1dh6avKvvsIdCeLG', 'Patient', 29, NULL),
(152, 'Joseph Hall', '$2y$10$v40f8uqwoglWszvjkeEzs.ziw9wl2UWfOCUem.UwQZ6T6PCnkpB3W', 'Patient', 30, NULL),
(153, 'Stephanie Evans', '$2y$10$cWKyqxROELrkAnmluj4Vj.XIUl0Jzu9esrPTavwpgHt/Yh2tQkyK2', 'Patient', 31, NULL),
(154, 'James Mcpherson', '$2y$10$Yuqi08J2MjcegzbGpIbYKufUmCyHZxFoQvYEUBWmPnK32mU4m7gpW', 'Patient', 32, NULL),
(155, 'Thomas Perez', '$2y$10$KksYes.IF7MbkVGVXFnx1.oHbr5Kynmxr0k3t3ppWMbVf30nFbNm2', 'Patient', 33, NULL),
(156, 'Michelle Hawkins', '$2y$10$NL8FkpmR5E3u3ZjA/LLAN.AATuj3RbFCCZOTDpuIBovaNtgHML3Gm', 'Patient', 34, NULL),
(157, 'Douglas Manning', '$2y$10$TfZOVQcQIzfIIc9F0fvcMu2M3mej3e.mQw.uJGJxF0SthmH.YcRLa', 'Patient', 35, NULL),
(158, 'Christina Williams', '$2y$10$5JGm9kfE6MDln3Rj2zBsFuzKvRgQnFuQxhhoBTNB2qtXnRhOrqKAO', 'Patient', 36, NULL),
(159, 'Jennifer Bright', '$2y$10$kCPjNh/RX5gUeaa2yGjxu.gCADhYfbgBUV39IK22GgB2.hNbqaZ0u', 'Patient', 37, NULL),
(160, 'Mrs. Lisa Mendez', '$2y$10$gYtWI9otz03qjf3LrtgSyueb02STkf6rS3MpbQ7CnA7IM3sIecOru', 'Patient', 38, NULL),
(161, 'Mr. James Rogers', '$2y$10$iJ2lVA6TSc6WsfjypcoaAuQmz.kh1AnMbPpcGUkhnOo00/yKnxahu', 'Patient', 39, NULL),
(162, 'Christopher Dean', '$2y$10$EblwENsIl4MW18pIhp6OruNNC1aCh8yqh1sDWSzpahQzPv5dRkUSy', 'Patient', 40, NULL),
(163, 'Ronald Zamora', '$2y$10$2Jsmfd9miaNMfEVlNTpTuO4NTskTEQXpj3q6Q4O3XGOAQRygZhdEe', 'Patient', 41, NULL),
(164, 'Cheryl Humphrey', '$2y$10$4AE9G3TxFTU4DViKNLW56elT3uz/v4PhSXr7f1cEJ/1vVW7Jc9Si.', 'Patient', 42, NULL),
(165, 'Tiffany Ayala', '$2y$10$WEyti0SQfyWCRc/D7ExgyeSkdqfhRGDuY7tEy24XfR7/UnY.ZSlw2', 'Patient', 43, NULL),
(166, 'Tracy Barnes', '$2y$10$YEh4q3FB9FyCcs63I69MiOn3xejjzcy6.wxCjHVJz.tKvmNxDzrqW', 'Patient', 44, NULL),
(167, 'Kevin Lane', '$2y$10$iua57Zg1kFlSpIvU2RoQyeYLX.lsfPZWZvTzKfU7wuLRbZd7vq2NC', 'Patient', 45, NULL),
(168, 'Steven Cunningham', '$2y$10$nNvYv625oyy5.52lqV5t..jyHMDJ0kWhh1kf7dmdnOGhcrd0I.Qxi', 'Patient', 46, NULL),
(169, 'Carrie Marsh', '$2y$10$VWVf/jbL/9j.FWTWTz.ty.povmbDfh4pmGzx.fedpk8vbdgB0OJ6W', 'Patient', 47, NULL),
(170, 'John Eaton', '$2y$10$HA.HGhyAF76R2eF1H6uDAuk6YKb9hD.WIWqyylGpvf7j9w95PbGB2', 'Patient', 48, NULL),
(171, 'Christopher Wilcox', '$2y$10$J0aJ57di5.SZcnewqSZWVepbDsEw/IeHgySf51sVRP33u0p8Hi1yy', 'Patient', 49, NULL),
(172, 'Emily Johnson', '$2y$10$Nh/Q0LJ4UiJ5Rzy5GG/ph.iAiRWDOcR23IAfVIMbl.uND24/f2TyS', 'Patient', 50, NULL),
(173, 'Meghan Berg', '$2y$10$S613fsL5OY9GrVDEzi3Ebe6vrVAu/SVrSN/48zrprhJpzjAOAOb9a', 'Patient', 51, NULL),
(174, 'Brandon Lewis', '$2y$10$5.BDO5DJcQpZqN0.IOXb0uW589g2GloyOxOOiD5FE8kIKTEid6DSW', 'Patient', 52, NULL),
(175, 'Jessica Morris', '$2y$10$SGSG403B.IWAAN282R/xVOBnRo1NglIo39p8E2ofvuLB/J0hnAUGe', 'Patient', 53, NULL),
(176, 'Amy Atkins', '$2y$10$LcKD8tuo0JFcvgGqHmWQIuWwgtBrKXsAnNJBM0xb1k9era9MAy4Gq', 'Patient', 54, NULL),
(177, 'Todd Weaver', '$2y$10$ULwBfogznDu926xXJhJMouoaKqWfHq6bA7Wu0zbZ.nmVyXHTWLeXS', 'Patient', 55, NULL),
(178, 'Robert Henderson', '$2y$10$gW8sUQB6bLITApL1lLWAquPmXGiYgmVoNDZJQdOzbtTvQEOstO2EK', 'Patient', 56, NULL),
(179, 'Vanessa Boone', '$2y$10$5e6uNM9m/xUpD7GfO2wTUOYPoPgRoFmeibjaULVCvoWIIEjrfT1YO', 'Patient', 57, NULL),
(180, 'Pamela Matthews', '$2y$10$sbAWp9ZzwUSkfd9C15UJXONf4FyT6AY78EnOVsQWbkUgpENhm479a', 'Patient', 58, NULL),
(181, 'Charles Davis', '$2y$10$oXSTVlQVI.tnr7G5f3mJseF6Fz1Tw1iP9AQpkPSnXPF.9qxh1WBu2', 'Patient', 59, NULL),
(182, 'Luis Miranda', '$2y$10$tNZ9Cov7CM5exHpAoG/2T.qVqQ0UAkucwv8uIr8Ve4I8Uu74UvKUW', 'Patient', 60, NULL),
(183, 'Seerat Fatima', '$2y$10$o.VjGwOoJwAYnGASxzlZRuk0qpYzuo1H99P04oU6zMst0Nn6kKwCK', 'Patient', 61, NULL),
(184, 'Tayyab', '$2y$10$L0QVBhULrSckzBIkSdfBCunFI6h7E3ipnt5MfvmR30N6vZdB2hfpa', 'Patient', 62, NULL),
(185, 'Naseer', '$2y$10$CnVps54vm2lKydpjhmNbBO5tBpNt1RCEeBco.USuqqHorfbjKNWH.', 'Patient', 63, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`user_id`, `permission_id`) VALUES
(63, 1),
(63, 2),
(63, 4),
(66, 1),
(66, 2),
(66, 4),
(70, 1),
(70, 2),
(78, 1),
(78, 2),
(78, 4),
(87, 1),
(87, 4),
(98, 1),
(98, 2),
(98, 4),
(99, 2),
(100, 1),
(100, 2),
(100, 4),
(117, 1),
(117, 2),
(117, 4);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_appointments`
-- (See below for the actual view)
--
CREATE TABLE `v_appointments` (
`EnrollmentID` int(11)
,`Date` date
,`Diagnosis` text
,`Status` enum('Scheduled','Pending','Treated','Cancelled')
,`DoctorID` int(11)
,`PatientID` int(11)
,`PatientName` varchar(100)
,`DoctorName` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_bills`
-- (See below for the actual view)
--
CREATE TABLE `v_bills` (
`BillID` int(11)
,`TotalAmount` decimal(10,2)
,`PaymentStatus` enum('Pending','Paid','Partial','Unpaid')
,`BillDate` date
,`EnrollmentID` int(11)
,`PatientID` int(11)
,`RoomID` int(11)
,`nights` int(11)
,`PatientName` varchar(100)
,`RoomType` varchar(50)
);

-- --------------------------------------------------------

--
-- Structure for view `v_appointments`
--
DROP TABLE IF EXISTS `v_appointments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_appointments`  AS SELECT `a`.`EnrollmentID` AS `EnrollmentID`, `a`.`Date` AS `Date`, `a`.`Diagnosis` AS `Diagnosis`, `a`.`Status` AS `Status`, `a`.`DoctorID` AS `DoctorID`, `a`.`PatientID` AS `PatientID`, `p`.`Name` AS `PatientName`, `d`.`name` AS `DoctorName` FROM ((`appointments` `a` left join `patients` `p` on(`a`.`PatientID` = `p`.`PatientID`)) left join `doctors` `d` on(`a`.`DoctorID` = `d`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_bills`
--
DROP TABLE IF EXISTS `v_bills`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bills`  AS SELECT `b`.`BillID` AS `BillID`, `b`.`TotalAmount` AS `TotalAmount`, `b`.`PaymentStatus` AS `PaymentStatus`, `b`.`BillDate` AS `BillDate`, `b`.`EnrollmentID` AS `EnrollmentID`, `b`.`PatientID` AS `PatientID`, `b`.`RoomID` AS `RoomID`, `b`.`nights` AS `nights`, `p`.`Name` AS `PatientName`, `r`.`RoomType` AS `RoomType` FROM ((`bills` `b` left join `patients` `p` on(`b`.`PatientID` = `p`.`PatientID`)) left join `rooms` `r` on(`b`.`RoomID` = `r`.`RoomID`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`EnrollmentID`),
  ADD KEY `booked_by` (`booked_by`),
  ADD KEY `idx_appointments_status` (`Status`),
  ADD KEY `idx_appointments_date` (`Date`),
  ADD KEY `idx_appt_doctor_status` (`DoctorID`,`Status`),
  ADD KEY `idx_appt_patient_status` (`PatientID`,`Status`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_table_time` (`table_name`,`triggered_at`),
  ADD KEY `idx_audit_action` (`action`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`BillID`),
  ADD KEY `EnrollmentID` (`EnrollmentID`),
  ADD KEY `RoomID` (`RoomID`),
  ADD KEY `generated_by` (`generated_by`),
  ADD KEY `idx_bills_payment_status` (`PaymentStatus`),
  ADD KEY `idx_bills_bill_date` (`BillDate`),
  ADD KEY `idx_bills_patient_status` (`PatientID`,`PaymentStatus`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `idx_doctors_specialization` (`specialization`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`med_id`),
  ADD KEY `idx_medicines_name` (`name`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`PatientID`),
  ADD KEY `registered_by` (`registered_by`),
  ADD KEY `idx_patients_name` (`Name`),
  ADD KEY `idx_patients_city` (`City`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`rx_id`),
  ADD KEY `EnrollmentID` (`EnrollmentID`),
  ADD KEY `PatientID` (`PatientID`),
  ADD KEY `DoctorID` (`DoctorID`),
  ADD KEY `idx_prescriptions_date` (`date`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rx_id` (`rx_id`),
  ADD KEY `med_id` (`med_id`);

--
-- Indexes for table `receptionists`
--
ALTER TABLE `receptionists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`RoomID`),
  ADD KEY `assignedTo` (`assignedTo`),
  ADD KEY `enrollmentId` (`enrollmentId`),
  ADD KEY `idx_rooms_status` (`RoomStatus`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receptionist_id` (`receptionist_id`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_name_role` (`name`,`role`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `EnrollmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `BillID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=277;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `med_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `PatientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `rx_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `receptionists`
--
ALTER TABLE `receptionists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `RoomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`booked_by`) REFERENCES `receptionists` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`EnrollmentID`) REFERENCES `appointments` (`EnrollmentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`RoomID`) REFERENCES `rooms` (`RoomID`) ON DELETE SET NULL,
  ADD CONSTRAINT `bills_ibfk_4` FOREIGN KEY (`generated_by`) REFERENCES `receptionists` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `receptionists` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`EnrollmentID`) REFERENCES `appointments` (`EnrollmentID`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`PatientID`) REFERENCES `patients` (`PatientID`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`DoctorID`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`rx_id`) REFERENCES `prescriptions` (`rx_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescription_items_ibfk_2` FOREIGN KEY (`med_id`) REFERENCES `medicines` (`med_id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`assignedTo`) REFERENCES `patients` (`PatientID`) ON DELETE SET NULL,
  ADD CONSTRAINT `rooms_ibfk_2` FOREIGN KEY (`enrollmentId`) REFERENCES `appointments` (`EnrollmentID`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`receptionist_id`) REFERENCES `receptionists` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
