-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 31, 2025 at 02:22 AM
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
('HEY', '$2y$10$mpowxJvhBN.qOAxiq8fkAuvAUSY.k8wR0fsw7qqO6ocxlNePwF142', 'NULL', 'Normal', 'qv25tnkejyl8ri4zmphu67bdfw1c03sx9oga'),
('HR', '$2y$10$bXwLGhxF/5v/z8fl/.HdVO81r5MfVnzY2BfD9yzJ0QuLB4RnAg7bW', 'NULL', 'Manager', 'zvknj28go9d1ca06rpwbhfx73qity5u4mels'),
('YO', '$2y$10$FZD1NxgWIQ.sqdr2MbPxf.VJMAV3IOym9ovW/5XnfHal5yVEIdEU2', 'NULL', 'Normal', 'vtyu62cebaqopixn9z3h1f4d5ml8kg0wj7rs');

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
('qv25tnkejyl8ri4zmphu67bdfw1c03sx9oga', 'HEY', 'Grade 1'),
('vtyu62cebaqopixn9z3h1f4d5ml8kg0wj7rs', 'YO', 'Grade 2'),
('zvknj28go9d1ca06rpwbhfx73qity5u4mels', 'HR', 'Grade 1');

-- --------------------------------------------------------

--
-- Table structure for table `Employee_Allocation`
--

CREATE TABLE `Employee_Allocation` (
  `EmployeeID` varchar(50) NOT NULL,
  `UsedAllocations` int(11) NOT NULL DEFAULT 1,
  `LeaveCategory` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores allocations to an employee in a leave type';

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
('Grade 1'),
('Grade 2'),
('Grade 3'),
('Grade 4');

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
(16, 'Grade 1', 2, 'Emergency'),
(17, 'Grade 2', 0, 'Emergency'),
(19, 'Grade 3', 2, 'Emergency'),
(20, 'Grade 4', 4, 'Emergency'),
(21, 'Grade 1', 0, 'For Fun'),
(22, 'Grade 2', 2, 'For Fun'),
(23, 'Grade 3', 0, 'For Fun'),
(24, 'Grade 4', 0, 'For Fun');

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
('Emergency'),
('For Fun');

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
  MODIFY `GradeAllocationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `Leave_Application`
--
ALTER TABLE `Leave_Application`
  MODIFY `LeaveApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
