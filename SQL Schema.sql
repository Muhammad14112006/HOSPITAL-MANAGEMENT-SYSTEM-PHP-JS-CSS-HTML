-- HealthCare HMS Database Schema

-- 1. Core Entity Tables (Departments & Staff)

CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `location` VARCHAR(100)
);

CREATE TABLE `doctors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `specialization` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20),
    `salary` DECIMAL(10,2) DEFAULT 0.00,
    `appointment_fee` DECIMAL(10,2) DEFAULT 0.00,
    `dept_id` INT,
    `Passwords` VARCHAR(255) NOT NULL,
    FOREIGN KEY (`dept_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
);

CREATE TABLE `receptionists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `salary` DECIMAL(10,2) DEFAULT 0.00
);

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL,
    `ref_id` INT DEFAULT NULL
);

-- 2. Patient & Appointment Tables

CREATE TABLE `patients` (
    `PatientID` INT AUTO_INCREMENT PRIMARY KEY,
    `Name` VARCHAR(100) NOT NULL,
    `Age` INT DEFAULT NULL,
    `Gender` ENUM('M', 'F', 'O') DEFAULT NULL,
    `Phone` VARCHAR(20),
    `Address` TEXT,
    `password` VARCHAR(255) NOT NULL
);

CREATE TABLE `appointments` (
    `EnrollmentID` INT AUTO_INCREMENT PRIMARY KEY,
    `Date` DATE NOT NULL,
    `Diagnosis` TEXT,
    `Status` ENUM('Scheduled', 'Pending', 'Treated', 'Cancelled') DEFAULT 'Scheduled',
    `DoctorID` INT NOT NULL,
    `PatientID` INT NOT NULL,
    FOREIGN KEY (`DoctorID`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`PatientID`) REFERENCES `patients`(`PatientID`) ON DELETE CASCADE
);

-- 3. Hospital Operations (Rooms & Billing)

CREATE TABLE `rooms` (
    `RoomID` INT AUTO_INCREMENT PRIMARY KEY,
    `RoomType` VARCHAR(50) NOT NULL,
    `RoomStatus` ENUM('Available', 'Occupied', 'Maintenance') DEFAULT 'Available',
    `price` DECIMAL(10,2) NOT NULL,
    `assignedTo` INT DEFAULT NULL,
    `enrollmentId` INT DEFAULT NULL,
    FOREIGN KEY (`assignedTo`) REFERENCES `patients`(`PatientID`) ON DELETE SET NULL,
    FOREIGN KEY (`enrollmentId`) REFERENCES `appointments`(`EnrollmentID`) ON DELETE SET NULL
);

CREATE TABLE `bills` (
    `BillID` INT AUTO_INCREMENT PRIMARY KEY,
    `TotalAmount` DECIMAL(10,2) NOT NULL,
    `PaymentStatus` ENUM('Pending', 'Paid', 'Partial', 'Unpaid') DEFAULT 'Pending',
    `BillDate` DATE NOT NULL,
    `EnrollmentID` INT NOT NULL,
    `PatientID` INT NOT NULL,
    `RoomID` INT DEFAULT NULL,
    `nights` INT DEFAULT 1,
    FOREIGN KEY (`EnrollmentID`) REFERENCES `appointments`(`EnrollmentID`) ON DELETE CASCADE,
    FOREIGN KEY (`PatientID`) REFERENCES `patients`(`PatientID`) ON DELETE CASCADE,
    FOREIGN KEY (`RoomID`) REFERENCES `rooms`(`RoomID`) ON DELETE SET NULL
);

-- 4. Medical Records (Prescriptions & Medicines)

CREATE TABLE `medicines` (
    `med_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(100)
);

CREATE TABLE `prescriptions` (
    `rx_id` INT AUTO_INCREMENT PRIMARY KEY,
    `EnrollmentID` INT NOT NULL,
    `PatientID` INT NOT NULL,
    `DoctorID` INT NOT NULL,
    `instructions` TEXT,
    `date` DATE NOT NULL,
    FOREIGN KEY (`EnrollmentID`) REFERENCES `appointments`(`EnrollmentID`) ON DELETE CASCADE,
    FOREIGN KEY (`PatientID`) REFERENCES `patients`(`PatientID`) ON DELETE CASCADE,
    FOREIGN KEY (`DoctorID`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
);

CREATE TABLE `prescription_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rx_id` INT NOT NULL,
    `med_id` INT NOT NULL,
    `dosage` VARCHAR(100),
    FOREIGN KEY (`rx_id`) REFERENCES `prescriptions`(`rx_id`) ON DELETE CASCADE,
    FOREIGN KEY (`med_id`) REFERENCES `medicines`(`med_id`) ON DELETE CASCADE
);

-- 5. Required Database Views

CREATE VIEW `v_appointments` AS
SELECT a.*, p.Name AS PatientName, d.name AS DoctorName
FROM appointments a
LEFT JOIN patients p ON a.PatientID = p.PatientID
LEFT JOIN doctors d ON a.DoctorID = d.id;

CREATE VIEW `v_bills` AS
SELECT b.*, p.Name AS PatientName, r.RoomType
FROM bills b
LEFT JOIN patients p ON b.PatientID = p.PatientID
LEFT JOIN rooms r ON b.RoomID = r.RoomID;

-- 6. Initial Admin Setup (Seed Data)

INSERT INTO `users` (`name`, `password`, `role`) 
VALUES ('Admin', '$2y$10$tZ2E7y0sQ3H8rW3o0oW0eu7tWv8t.80p4i8Uu1.g3rX/g8I2p0qO', 'Admin');
