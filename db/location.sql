-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2024 at 02:05 PM
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
-- Database: `location`
--

-- --------------------------------------------------------

--
-- Table structure for table `locate`
--

CREATE TABLE `locate` (
  `ID` int(11) NOT NULL,
  `drivername` varchar(255) NOT NULL,
  `cnumber` varchar(255) NOT NULL,
  `platenumber` varchar(255) NOT NULL,
  `passenger` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `speed` varchar(255) NOT NULL,
  `resdate` varchar(255) NOT NULL,
  `rotation` varchar(255) NOT NULL,
  `jeepicon` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locate`
--

INSERT INTO `locate` (`ID`, `drivername`, `cnumber`, `platenumber`, `passenger`, `route`, `latitude`, `longitude`, `speed`, `resdate`, `rotation`, `jeepicon`, `address`, `company_name`) VALUES
(1, 'kadas dsfe', '09453423', 'MTR CBD', '4', 'Tabaco to Legazpi', '0.000000', '0.000000', '', '2024-06-23 17:34:08', '', 'jeepsy1.png', 'Purok 1, , fidel, Legazpi City, albay, 4500', 'pblue'),
(2, 'vfse ', '09563452895', 'N35 6432', '21', 'Sto. Domingo to Legazpi', '13.151311', '123.749184', '56', '0000-00-00 00:00:00', '180', 'jeeps2.png', '', ''),
(4, 'abc d efg', '049523', 'KLSF RITT', '', 'Sto.Domingo to Legazpi', '', '', '', '', '', 'jeepsy3.png', 'Purok 1, , fidel, sto.domingo, albay, 4508', '');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `sender_name` varchar(255) NOT NULL,
  `receiver_name` varchar(255) NOT NULL,
  `message` varchar(500) NOT NULL,
  `timestamp` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `sender_name`, `receiver_name`, `message`, `timestamp`) VALUES
(61, '', 'admin123', 'dfsdf', '2024-07-07 11:43:55'),
(62, '', 'admin123', 'dfsdf', '2024-07-07 17:54:08'),
(63, '', 'admin123', 'dfsdf', '2024-07-07 18:35:21'),
(64, '', 'admin123', 'cas', '2024-07-07 18:35:27'),
(65, '', 'admin123', 'asda', '2024-07-07 18:35:32'),
(66, '', 'admin123', 'vvfds', '2024-07-07 18:35:36'),
(67, '', 'admin123', 'hi', '2024-07-07 18:38:02'),
(68, '', 'admin123', 'hi', '2024-07-07 18:39:29'),
(69, '', 'admin123', 'gh', '2024-07-07 18:43:36'),
(70, '', '', 'sad\r\n', '2024-07-07 18:51:58'),
(71, '', 'admin123', 'jj', '2024-07-07 18:52:34'),
(72, '', 'admin123', 'hihi', '2024-07-07 20:02:04'),
(73, '', 'admin123', 'h', '2024-07-07 23:08:04'),
(74, '', 'admin123', 'df', '2024-07-08 00:16:48'),
(75, '', 'admin123', 'hshs', '2024-07-22 23:00:01');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `mname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `conpass` varchar(100) NOT NULL,
  `account` varchar(50) NOT NULL,
  `login_time` varchar(255) NOT NULL,
  `status` varchar(20) NOT NULL,
  `code` varchar(100) NOT NULL,
  `ccode` varchar(100) NOT NULL,
  `login_time_out` varchar(255) NOT NULL,
  `profile` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fname`, `mname`, `lname`, `email`, `user`, `password`, `conpass`, `account`, `login_time`, `status`, `code`, `ccode`, `login_time_out`, `profile`) VALUES
(1, 'Jamz', '', 'Anonuevo', 'undiaxinus@gmail.com', '1', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', 'user', '2024-08-16 08:07:34', 'online', '', '', '2024-08-14 12:47:49', 'pic1.jpg'),
(2, 'Costumer', '', 'Serveice', 'admin@gmail.com', 'admin123', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'admin', '2024-09-11 13:54:49', 'online', '', '', '', 'sbmo.png'),
(3, 'jamz', 'Brecia', 'Anonuevo', 'anonuevojamille@gmail.com', '78a47207d562befdd19ab4fa735dd2a3a52237db3de159562aadbde14c297bf5', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', 'admin', '', 'offline', '', '', '', 'gif.gif'),
(43, 'axie', '', 'axss', 'axie@gmail.com', '2', 'd74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1', 'd74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1', 'admin', '2024-07-07 17:48:29', 'offline', '', '', '', 'pic.jpg'),
(45, 'cla', 'K', 'asdhad', 'cla@gmail.com', '8c932661307de3d566a60b978d90bec11a8a14830db2b1726172b161f25969fc', 'd74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1', 'd74ff0ee8da3b9806b18c877dbf29bbde50b5bd8e4dad7a3a725000feb82e8f1', 'user', '2024-07-07 13:56:30', 'online', '', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `locate`
--
ALTER TABLE `locate`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `locate`
--
ALTER TABLE `locate`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
