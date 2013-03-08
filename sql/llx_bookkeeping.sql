-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 27, 2013 at 08:30 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `jeffinfoerp2`
--

-- --------------------------------------------------------

--
-- Table structure for table `llx_bookkeeping`
--

CREATE TABLE IF NOT EXISTS `llx_bookkeeping` (
  `rowid` int(11) NOT NULL AUTO_INCREMENT,
  `doc_date` date NOT NULL,
  `doc_type` varchar(30) NOT NULL,			-- facture_client/reglement_client/facture_fournisseur/reglement_fournisseur
  `doc_ref` varchar(30) NOT NULL,			-- facture_client/reglement_client/... reference number
  `fk_doc` int(11) NOT NULL,                -- facture_client/reglement_client/... rowid
  `fk_docdet` int(11) NOT NULL,             -- facture_client/reglement_client/... line rowid
  `code_tiers` varchar(24),                 -- code tiers
  `numero_compte` varchar(50) DEFAULT NULL,
  `label_compte` varchar(128) CHARACTER SET utf8 NOT NULL,
  `debit` double NOT NULL,
  `credit` double NOT NULL,
  `montant` double NOT NULL,
  `sens` varchar(1) DEFAULT NULL,
  `fk_user_author` int(11) NOT NULL,
  import_key			varchar(14),
  PRIMARY KEY (`rowid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
