-- phpMyAdmin SQL Dump
-- version 3.4.11.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 05, 2013 at 10:35 AM
-- Server version: 5.5.30
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kaplanex_publicSpheres`
--

-- --------------------------------------------------------

--
-- Table structure for table `Context`
--

CREATE TABLE IF NOT EXISTS `Context` (
  `responseId` int(10) unsigned NOT NULL,
  `isAgree` int(10) unsigned NOT NULL,
  `parentId` int(10) unsigned NOT NULL,
  `user` varchar(60) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `yesVotes` int(10) NOT NULL DEFAULT '0',
  `noVotes` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`responseId`,`isAgree`,`parentId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `InvertedIndex`
--

CREATE TABLE IF NOT EXISTS `InvertedIndex` (
  `stemText` varchar(20) NOT NULL,
  `responseId` int(10) unsigned NOT NULL,
  `tf` double unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`stemText`,`responseId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Responses`
--

CREATE TABLE IF NOT EXISTS `Responses` (
  `responseId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `responseText` varchar(1000) DEFAULT NULL,
  `user` varchar(60) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`responseId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=270 ;

-- --------------------------------------------------------

--
-- Table structure for table `ResponseSubpoints`
--

CREATE TABLE IF NOT EXISTS `ResponseSubpoints` (
  `responseId` int(10) unsigned NOT NULL,
  `subpointId` int(10) unsigned NOT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`responseId`,`subpointId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `user` varchar(60) NOT NULL DEFAULT '',
  `pass` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Votes`
--

CREATE TABLE IF NOT EXISTS `Votes` (
  `responseId` int(10) NOT NULL,
  `parentId` int(10) NOT NULL,
  `user` varchar(60) NOT NULL,
  `vote` tinyint(1) NOT NULL,
  PRIMARY KEY (`responseId`,`parentId`,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
