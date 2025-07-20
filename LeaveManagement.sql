-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 20, 2025 at 02:48 AM
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
-- Database: `LeaveManagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `Account`
--

CREATE TABLE `Account` (
  `Username` varchar(50) NOT NULL,
  `Password` varchar(60) NOT NULL,
  `Token` varchar(50) DEFAULT NULL,
  `Privilege` enum('Normal','Manager') NOT NULL,
  `EmployeeID` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores authentication details';

--
-- Dumping data for table `Account`
--

INSERT INTO `Account` (`Username`, `Password`, `Token`, `Privilege`, `EmployeeID`) VALUES
('HEY', '$2y$10$LXiW1ye3duOgkAs41l8U7OFyKsrtqlXM2O7NDJAUbQnuNivYr3j2a', 'NULL', 'Normal', '4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2'),
('HR', '$2y$10$ZbUY9YNXHs6eZuDZrCOpwuotODL4VUUHpYLjw/DuVbEdGTbnA8YTq', '7ijcxh2mybasq6u1938ntwfl5odkg4rez🌑🌕vp0', 'Manager', 'n8vz2dml6xa3fr4e19sc0pgtuyow5qibjhk7');

-- --------------------------------------------------------

--
-- Table structure for table `Employee`
--

CREATE TABLE `Employee` (
  `EmployeeID` varchar(50) NOT NULL,
  `EmployeeName` varchar(50) NOT NULL,
  `EmployeeGrade` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Details of employees';

--
-- Dumping data for table `Employee`
--

INSERT INTO `Employee` (`EmployeeID`, `EmployeeName`, `EmployeeGrade`) VALUES
('4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2', 'HEY', 'Test'),
('n8vz2dml6xa3fr4e19sc0pgtuyow5qibjhk7', 'HR', 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `Employee_Allocation`
--

CREATE TABLE `Employee_Allocation` (
  `EmployeeID` varchar(50) NOT NULL,
  `UsedAllocations` int(11) NOT NULL DEFAULT 1,
  `LeaveCategory` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores allocations to an employee in a leave type';

--
-- Dumping data for table `Employee_Allocation`
--

INSERT INTO `Employee_Allocation` (`EmployeeID`, `UsedAllocations`, `LeaveCategory`) VALUES
('4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2', 1, 'Education'),
('n8vz2dml6xa3fr4e19sc0pgtuyow5qibjhk7', 3, 'Education');

-- --------------------------------------------------------

--
-- Table structure for table `Grade`
--

CREATE TABLE `Grade` (
  `EmployeeGrade` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores grades defined';

--
-- Dumping data for table `Grade`
--

INSERT INTO `Grade` (`EmployeeGrade`) VALUES
('Grade 9'),
('Manager'),
('Test');

-- --------------------------------------------------------

--
-- Table structure for table `Grade_Allocation`
--

CREATE TABLE `Grade_Allocation` (
  `GradeAllocationID` int(11) NOT NULL,
  `EmployeeGrade` varchar(50) NOT NULL,
  `Allocations` int(11) NOT NULL,
  `LeaveCategory` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores allocations to a grade in a leave type';

--
-- Dumping data for table `Grade_Allocation`
--

INSERT INTO `Grade_Allocation` (`GradeAllocationID`, `EmployeeGrade`, `Allocations`, `LeaveCategory`) VALUES
(1, 'Manager', 5, 'Education'),
(2, 'Test', 2, 'Education'),
(3, 'Manager', 10, 'Emergency'),
(4, 'Test', 4, 'Emergency'),
(5, 'Grade 9', 0, 'Education');

-- --------------------------------------------------------

--
-- Table structure for table `LeaveType`
--

CREATE TABLE `LeaveType` (
  `LeaveCategory` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores leave categories';

--
-- Dumping data for table `LeaveType`
--

INSERT INTO `LeaveType` (`LeaveCategory`) VALUES
('Education'),
('Emergency');

-- --------------------------------------------------------

--
-- Table structure for table `Leave_Application`
--

CREATE TABLE `Leave_Application` (
  `LeaveApplicationID` int(11) NOT NULL,
  `EmployeeID` varchar(50) NOT NULL,
  `LeaveCategory` varchar(50) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `LeaveStatus` enum('Approved','Rejected','Pending') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores leave categories';

--
-- Dumping data for table `Leave_Application`
--

INSERT INTO `Leave_Application` (`LeaveApplicationID`, `EmployeeID`, `LeaveCategory`, `StartDate`, `EndDate`, `LeaveStatus`) VALUES
(1, 'n8vz2dml6xa3fr4e19sc0pgtuyow5qibjhk7', 'Education', '2025-07-01', '2025-07-25', 'Approved'),
(2, 'n8vz2dml6xa3fr4e19sc0pgtuyow5qibjhk7', 'Emergency', '2025-07-29', '2025-07-30', 'Approved'),
(3, 'n8vz2dml6xa3fr4e19sc0pgtuyow5qibjhk7', 'Education', '2025-07-30', '2025-07-31', 'Approved'),
(4, 'n8vz2dml6xa3fr4e19sc0pgtuyow5qibjhk7', 'Education', '2025-07-15', '2025-07-24', 'Approved'),
(5, '4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2', 'Education', '2025-07-15', '2025-07-16', 'Approved'),
(6, '4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2', 'Emergency', '2025-07-23', '2025-07-31', 'Pending'),
(7, '4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2', 'Emergency', '2025-07-01', '2025-07-15', 'Pending'),
(8, '4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2', 'Education', '2025-07-02', '2025-07-11', 'Pending'),
(9, '4w36reg5lpoczxvdfqsmhbk0n8a9yiu71tj2', 'Emergency', '2025-07-27', '2025-07-28', 'Pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Account`
--
ALTER TABLE `Account`
  ADD PRIMARY KEY (`Username`),
  ADD KEY `EmployeeGrade` (`EmployeeID`);

--
-- Indexes for table `Employee`
--
ALTER TABLE `Employee`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD KEY `EmployeeGrade` (`EmployeeGrade`);

--
-- Indexes for table `Employee_Allocation`
--
ALTER TABLE `Employee_Allocation`
  ADD UNIQUE KEY `EmployeeID` (`EmployeeID`),
  ADD KEY `LeaveCategory` (`LeaveCategory`) USING BTREE;

--
-- Indexes for table `Grade`
--
ALTER TABLE `Grade`
  ADD PRIMARY KEY (`EmployeeGrade`);

--
-- Indexes for table `Grade_Allocation`
--
ALTER TABLE `Grade_Allocation`
  ADD PRIMARY KEY (`GradeAllocationID`),
  ADD KEY `LeaveCategory` (`LeaveCategory`),
  ADD KEY `_empGrade` (`EmployeeGrade`);

--
-- Indexes for table `LeaveType`
--
ALTER TABLE `LeaveType`
  ADD PRIMARY KEY (`LeaveCategory`);

--
-- Indexes for table `Leave_Application`
--
ALTER TABLE `Leave_Application`
  ADD PRIMARY KEY (`LeaveApplicationID`),
  ADD KEY `LeaveCategory` (`LeaveCategory`),
  ADD KEY `EmployeeID` (`EmployeeID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Grade_Allocation`
--
ALTER TABLE `Grade_Allocation`
  MODIFY `GradeAllocationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Leave_Application`
--
ALTER TABLE `Leave_Application`
  MODIFY `LeaveApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Account`
--
ALTER TABLE `Account`
  ADD CONSTRAINT `Emp` FOREIGN KEY (`EmployeeID`) REFERENCES `Employee` (`EmployeeID`);

--
-- Constraints for table `Employee`
--
ALTER TABLE `Employee`
  ADD CONSTRAINT `EmpGrade` FOREIGN KEY (`EmployeeGrade`) REFERENCES `Grade` (`EmployeeGrade`);

--
-- Constraints for table `Employee_Allocation`
--
ALTER TABLE `Employee_Allocation`
  ADD CONSTRAINT `Emp2` FOREIGN KEY (`EmployeeID`) REFERENCES `Employee` (`EmployeeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `LeaveType2` FOREIGN KEY (`LeaveCategory`) REFERENCES `LeaveType` (`LeaveCategory`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Grade_Allocation`
--
ALTER TABLE `Grade_Allocation`
  ADD CONSTRAINT `LeaveType` FOREIGN KEY (`LeaveCategory`) REFERENCES `LeaveType` (`LeaveCategory`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `_empGrade` FOREIGN KEY (`EmployeeGrade`) REFERENCES `Grade` (`EmployeeGrade`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Leave_Application`
--
ALTER TABLE `Leave_Application`
  ADD CONSTRAINT `FormLeaveCategory` FOREIGN KEY (`LeaveCategory`) REFERENCES `LeaveType` (`LeaveCategory`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FormSubmitter` FOREIGN KEY (`EmployeeID`) REFERENCES `Employee` (`EmployeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
