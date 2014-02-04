-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 04, 2014 at 10:48 AM
-- Server version: 5.5.34
-- PHP Version: 5.4.6-2~natty+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `collector`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE IF NOT EXISTS `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source` int(11) NOT NULL,
  `hash` char(40) NOT NULL,
  `url` varchar(255) NOT NULL,
  `dateinsert` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datepublish` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `author` varchar(32) NOT NULL,
  `content` text NOT NULL,
  `domain` varchar(128) NOT NULL,
  `kind` char(10) NOT NULL,
  `author_image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `source` (`source`),
  KEY `datepublish` (`datepublish`),
  KEY `domain` (`domain`),
  KEY `url` (`url`),
  FULLTEXT KEY `title_2` (`title`,`content`,`url`,`author`,`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2058 ;

-- --------------------------------------------------------

--
-- Table structure for table `sources`
--

CREATE TABLE IF NOT EXISTS `sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `dateinsert` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateupdate` timestamp NULL DEFAULT NULL,
  `failures` tinyint(1) NOT NULL,
  `kind` char(16) NOT NULL,
  `oauth_key` char(128) NOT NULL,
  `oauth_secret` char(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `dateupdate` (`dateupdate`),
  KEY `failures` (`failures`),
  KEY `kind` (`kind`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=703 ;

-- --------------------------------------------------------

--
-- Table structure for table `suggestions`
--

CREATE TABLE IF NOT EXISTS `suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` char(64) NOT NULL,
  `kind` char(16) NOT NULL,
  `relevance` int(4) NOT NULL,
  `source_id` int(11) NOT NULL,
  `source_name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `relevance` (`relevance`),
  KEY `source_id` (`source_id`),
  FULLTEXT KEY `source_name` (`source_name`,`domain`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3220 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `password` varchar(64) NOT NULL,
  `datesignup` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `datesync` int(11) NOT NULL,
  `data` text NOT NULL,
  `username` varchar(32) NOT NULL,
  `emailwelcome` tinyint(1) NOT NULL,
  `datepull` int(11) NOT NULL,
  `dateweblastlogin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `password` (`password`),
  KEY `datesignup` (`datesignup`),
  KEY `datesync` (`datesync`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1478 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
