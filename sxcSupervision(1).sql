-- phpMyAdmin SQL Dump
-- version 4.7.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 21, 2018 at 05:39 AM
-- Server version: 10.1.26-MariaDB
-- PHP Version: 7.1.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sxcSupervision`
--

-- --------------------------------------------------------

--
-- Table structure for table `complains`
--

CREATE TABLE `complains` (
  `id` int(11) NOT NULL,
  `complainBy` varchar(255) NOT NULL,
  `Details` text NOT NULL,
  `status` varchar(255) NOT NULL,
  `handledBy` varchar(255) NOT NULL,
  `complainDate` varchar(255) NOT NULL,
  `handledDate` varchar(255) DEFAULT NULL,
  `Remarks` varchar(255) NOT NULL,
  `roomNo` varchar(255) NOT NULL,
  `roomName` varchar(255) NOT NULL,
  `compNo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `complains`
--

INSERT INTO `complains` (`id`, `complainBy`, `Details`, `status`, `handledBy`, `complainDate`, `handledDate`, `Remarks`, `roomNo`, `roomName`, `compNo`) VALUES
(1, 'saugat', 'this is a test message', 'pending', '', '1513092215', NULL, '', '456', 'test', 'test'),
(2, 'saugat', 'this is a beta application testing message', 'pending', '', '1513092214', NULL, '', '79987', 'test', 'test'),
(3, 'saugat', 'this is a beta application testing message', 'pending', '', '1513092213', NULL, '', '79987', 'test', 'test'),
(4, 'saugat', 'this is a beta application testing message', 'pending', '', '1513092212', NULL, '', '79987', 'test', 'test'),
(5, 'saugat', 'This is another Final Beta test', 'pending', '', '1513092211', NULL, '', '45678', 'a level', '7985');

-- --------------------------------------------------------

--
-- Table structure for table `hods`
--

CREATE TABLE `hods` (
  `id` int(10) NOT NULL,
  `department` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hods`
--

INSERT INTO `hods` (`id`, `department`, `name`, `email`) VALUES
(1, 'Computer', 'Saugat Sigdel', 'saugatdai@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `postName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `postName`) VALUES
(1, 'Lecturer');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `officeId` varchar(255) NOT NULL,
  `authority` varchar(255) NOT NULL,
  `post` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `department`, `officeId`, `authority`, `post`, `username`, `password`, `email`) VALUES
(16, 'Saugat Sigdel', 'Computer', '2014STAFF317', 'admin', 'lecturer', 'saugat', 'Nepal\'123', 'saugatsigdel@sxc.edu.np'),
(17, 'Rajan Karmacharya', 'Computer', '2008STAFF108', 'admin', 'lecturer', 'rajanKarmacharya', 'Rajan\'Karmacharya', 'rajankarmacharya@sxc.edu.np'),
(18, 'Jitendra Manandhar', 'Computer', '1999STAFF020', 'admin', 'lecturer', 'jitendra', 'Jitendra\'Manandhar', 'jeetendra@sxc.edu.np'),
(19, 'Sanjay Kumar Yadav', 'Computer', '213456', 'cds', 'Lecturer', 'sanjay', 'nothing21', 'sanjay@sxc.edu.np'),
(20, 'Samyam Maskey', 'Computer', '456', 'cds', 'Lecturer', 'samyam', 'nothing21', 'samyammaskey@sxc.edu.np'),
(21, 'Bibek Konda', 'Computer', '456', 'cds', 'Lecturer', 'bibek', 'nothing21', 'bibekkonda@gmail.com'),
(22, 'Manish Maharjan', 'Computer', '456', 'cds', 'Lecturer', 'manish', 'nothing21', 'moonishcobain@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complains`
--
ALTER TABLE `complains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hods`
--
ALTER TABLE `hods`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complains`
--
ALTER TABLE `complains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `hods`
--
ALTER TABLE `hods`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
