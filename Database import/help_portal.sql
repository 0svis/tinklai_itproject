-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 02, 2024 at 05:50 AM
-- Server version: 5.7.35-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `help_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `Consultant`
--

CREATE TABLE `Consultant` (
  `ConsultantID` int(10) UNSIGNED NOT NULL,
  `UserID` int(10) UNSIGNED NOT NULL,
  `Name` varchar(100) NOT NULL,
  `Rating` float UNSIGNED DEFAULT '0',
  `Status` enum('Prisijunges','Uzimtas','Atsijunges') NOT NULL,
  `RatingCount` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Consultant`
--

INSERT INTO `Consultant` (`ConsultantID`, `UserID`, `Name`, `Rating`, `Status`, `RatingCount`) VALUES
(3, 5, 'Inga Konsultaviciute', 5, 'Prisijunges', 2),
(4, 6, 'Valerij Nykcavcij', 2.5, 'Prisijunges', 4),
(5, 8, 'Jonas Kazlauskas', 0, 'Prisijunges', 0),
(6, 9, 'DovilÄ— PetrauskaitÄ—', 5, 'Prisijunges', 1),
(7, 10, 'Tomas Jankauskas', 2, 'Prisijunges', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Consultation`
--

CREATE TABLE `Consultation` (
  `ConsultationID` int(10) UNSIGNED NOT NULL,
  `UserID` int(10) UNSIGNED NOT NULL,
  `ConsultantID` int(10) UNSIGNED DEFAULT NULL,
  `Date` date NOT NULL,
  `Status` enum('Laukiama','Priimta','Atmesta','Uzbaigta') NOT NULL,
  `CreditCost` int(10) UNSIGNED DEFAULT '5',
  `ChatLog` text,
  `Rated` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Consultation`
--

INSERT INTO `Consultation` (`ConsultationID`, `UserID`, `ConsultantID`, `Date`, `Status`, `CreditCost`, `ChatLog`, `Rated`) VALUES
(17, 5, 3, '2024-12-01', 'Uzbaigta', 3, '\nNaudotojas: Sveiki, kaip galiu pasikeisti ekrano vaizda?\nInga Konsultaviciute: Sveiki, pasikeisti galite per nustatymus. Settings > Background settings ir pasirinkite norima ekrano vaizda', 0),
(18, 5, 3, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(19, 1, 3, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(20, 1, 4, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(21, 1, 5, '2024-12-02', 'Atmesta', 5, NULL, 0),
(22, 1, 6, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(23, 1, 7, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(24, 1, 4, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(25, 1, 4, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(26, 1, 4, '2024-12-02', 'Uzbaigta', 5, NULL, 0),
(27, 1, 5, '2024-12-03', 'Laukiama', 5, NULL, 0),
(28, 1, 3, '2024-12-02', 'Priimta', 5, 'Mano kompiuteris pradejo mesti klaidas \"This is a trojan. Give 5 bitcoin\", ka daryti?!', 0),
(29, 1, 7, '2024-12-02', 'Laukiama', 5, 'Mano kompiuteris pradejo mesti klaidas \"This is a trojan. Give 5 bitcoin\", ka daryti?!', 0),
(30, 1, 3, '2024-12-02', 'Priimta', 1, 'Testine zinute', 0),
(31, 7, 3, '2024-12-03', 'Laukiama', 5, 'Ataskaitos testas', 0),
(32, 7, 3, '2024-12-03', 'Laukiama', 5, 'testas', 0);

-- --------------------------------------------------------

--
-- Table structure for table `CreditRequests`
--

CREATE TABLE `CreditRequests` (
  `RequestID` int(10) UNSIGNED NOT NULL,
  `UserID` int(10) UNSIGNED NOT NULL,
  `Amount` int(10) UNSIGNED NOT NULL,
  `Status` enum('Laukiama','Patvirtinta','Atmesta') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `CreditRequests`
--

INSERT INTO `CreditRequests` (`RequestID`, `UserID`, `Amount`, `Status`) VALUES
(11, 1, 1337, 'Laukiama'),
(12, 1, 33, 'Laukiama'),
(13, 1, 212, 'Laukiama'),
(14, 1, 214, 'Laukiama'),
(15, 1, 124, 'Laukiama'),
(16, 5, 3333333333, 'Patvirtinta');

-- --------------------------------------------------------

--
-- Table structure for table `FAQ`
--

CREATE TABLE `FAQ` (
  `FAQ_ID` int(10) UNSIGNED NOT NULL,
  `Question` text NOT NULL,
  `Answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `FAQ`
--

INSERT INTO `FAQ` (`FAQ_ID`, `Question`, `Answer`) VALUES
(1, 'Kaip pridÄ—ti ekrano paveiksliukÄ…, Android telefone?', 'Tiesiog pridÄ—t, hahass'),
(2, 'Kaip pridÄ—ti ekrano paveiksliukÄ…, Android telefone?', 'Tiesiog pridÄ—t, haha'),
(3, 'Kaip pridÄ—ti ekrano paveiksliukÄ…, Android telefone?', 'Tiesiog pridÄ—t, haha'),
(5, 'ff', 'ff'),
(6, 'ff', 'ff'),
(7, 'ff', 'ff'),
(8, 'ff', 'ff'),
(9, 'ff', 'ff'),
(10, 'Kaip suvesti PIN koda?', 'Suvesti PIN koda galima ijungus telefona, pranesimas prasantis suvesti PIN koda issoks iskart ijungus telefona ir idejus SIM kortele'),
(11, 'Kaip ijungti ekrana?', 'Atrakinti telefona'),
(12, 'test', 'test');

-- --------------------------------------------------------

--
-- Table structure for table `Feedback`
--

CREATE TABLE `Feedback` (
  `FeedbackID` int(10) UNSIGNED NOT NULL,
  `UserID` int(10) UNSIGNED NOT NULL,
  `ConsultantID` int(10) UNSIGNED DEFAULT NULL,
  `ConsultationID` int(10) UNSIGNED NOT NULL,
  `Rating` tinyint(3) UNSIGNED NOT NULL,
  `Comments` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Feedback`
--

INSERT INTO `Feedback` (`FeedbackID`, `UserID`, `ConsultantID`, `ConsultationID`, `Rating`, `Comments`) VALUES
(6, 5, 3, 17, 5, 'Viskas puikiai'),
(7, 1, 3, 19, 5, 'test1'),
(8, 1, 4, 20, 4, 'twest1'),
(9, 1, 4, 24, 2, 'blogai...'),
(10, 1, 4, 25, 3, 'lb blogai'),
(11, 1, 6, 22, 5, 'gerai'),
(12, 1, 7, 23, 2, ''),
(13, 1, 4, 26, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `UserID` int(10) UNSIGNED NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` char(60) NOT NULL,
  `Role` enum('Unregistered','Registered','Consultant','Administrator') NOT NULL,
  `Credits` int(10) UNSIGNED DEFAULT '0',
  `Email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`UserID`, `Username`, `Password`, `Role`, `Credits`, `Email`) VALUES
(1, 'stud', '$2y$10$CjuM.2vasc1aWxxL8beMl.pKB7/NUL3KC7ZYNowc2Nf/M88JgkV7C', 'Administrator', 40, ''),
(5, 'inga', '$2y$10$1i1av2bGx4yU02kFCx8pT.fU9H/we77N7iulZyfTJ4PLfWFjj7HWa', 'Consultant', 3333333323, 'inga@helpportal.com'),
(6, 'Valer', '$2y$10$C05gz0mIhFs1VEl16Ji7NevPE9cSm1UAM0nngTS46cOCtDSf2abYm', 'Consultant', 33333, 'valer@helpdesk.com'),
(7, 'user', '$2y$10$9GNGzrzk6j5hXy0AsGFqd.PvJTK0TeFiEXYXu072dB/l6FrGSXQvy', 'Registered', 33333, 'user@helpdesk.com'),
(8, 'jonas', '$2y$10$7ORM63p.GzOkmg54qYOTIOa5iMbg/e/7Ky1KCRwKoyzJWgOsRrJHi', 'Consultant', 0, 'jonas.kazlauskas@helpportal.com'),
(9, 'dovile', '$2y$10$6onO6IQVb9tiDNXIyNha3OFHjQYL7k8fq0b8aE2EOXlyL20dR774e', 'Consultant', 0, 'dovile.petrauskaite@helpportal.com'),
(10, 'tomas', '$2y$10$GqXSb1EEp/NWE/OgfIQmg.dJQ2KJzfvCK1kyH0sANZoLhoDNdlvia', 'Consultant', 0, 'tomas.jankauskas@helpportal.com'),
(11, 'test', '$2y$10$KJ4I14h8BJopeLK1j.odBO/W7AwitPpCOUwgwXqipEjtd9G0Ulqvy', 'Registered', 0, 'test@helpportal.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Consultant`
--
ALTER TABLE `Consultant`
  ADD PRIMARY KEY (`ConsultantID`),
  ADD UNIQUE KEY `UserID` (`UserID`);

--
-- Indexes for table `Consultation`
--
ALTER TABLE `Consultation`
  ADD PRIMARY KEY (`ConsultationID`),
  ADD KEY `FK_Consultation_UserID` (`UserID`),
  ADD KEY `FK_Consultation_ConsultantID` (`ConsultantID`);

--
-- Indexes for table `CreditRequests`
--
ALTER TABLE `CreditRequests`
  ADD PRIMARY KEY (`RequestID`),
  ADD KEY `FK_CreditRequests_UserID` (`UserID`);

--
-- Indexes for table `FAQ`
--
ALTER TABLE `FAQ`
  ADD PRIMARY KEY (`FAQ_ID`);

--
-- Indexes for table `Feedback`
--
ALTER TABLE `Feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `FK_Feedback_UserID` (`UserID`),
  ADD KEY `FK_Feedback_ConsultationID` (`ConsultationID`),
  ADD KEY `FK_Feedback_ConsultantID` (`ConsultantID`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Consultant`
--
ALTER TABLE `Consultant`
  MODIFY `ConsultantID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `Consultation`
--
ALTER TABLE `Consultation`
  MODIFY `ConsultationID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT for table `CreditRequests`
--
ALTER TABLE `CreditRequests`
  MODIFY `RequestID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `FAQ`
--
ALTER TABLE `FAQ`
  MODIFY `FAQ_ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `Feedback`
--
ALTER TABLE `Feedback`
  MODIFY `FeedbackID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `UserID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `Consultant`
--
ALTER TABLE `Consultant`
  ADD CONSTRAINT `FK_UserID` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `Consultation`
--
ALTER TABLE `Consultation`
  ADD CONSTRAINT `FK_Consultation_ConsultantID` FOREIGN KEY (`ConsultantID`) REFERENCES `Consultant` (`ConsultantID`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_Consultation_UserID` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `CreditRequests`
--
ALTER TABLE `CreditRequests`
  ADD CONSTRAINT `FK_CreditRequests_UserID` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE;

--
-- Constraints for table `Feedback`
--
ALTER TABLE `Feedback`
  ADD CONSTRAINT `FK_Feedback_ConsultantID` FOREIGN KEY (`ConsultantID`) REFERENCES `Consultant` (`ConsultantID`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_Feedback_ConsultationID` FOREIGN KEY (`ConsultationID`) REFERENCES `Consultation` (`ConsultationID`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Feedback_UserID` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
