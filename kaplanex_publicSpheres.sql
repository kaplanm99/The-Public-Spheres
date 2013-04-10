--
-- Database: `kaplanex_publicSpheres`
--

-- --------------------------------------------------------

--
-- Table structure for table `Context`
--

DROP TABLE IF EXISTS `Context`;
CREATE TABLE IF NOT EXISTS `Context` (
  `responseId` int(10) unsigned NOT NULL,
  `isAgree` int(10) unsigned NOT NULL,
  `parentId` int(10) unsigned NOT NULL,
  `user` varchar(60) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `yesVotes` int(10) NOT NULL DEFAULT '0',
  `noVotes` int(10) NOT NULL DEFAULT '0',
  `aIds` varchar(2000) NOT NULL,
  PRIMARY KEY (`responseId`,`isAgree`,`parentId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `DemographicSurvey`
--

DROP TABLE IF EXISTS `DemographicSurvey`;
CREATE TABLE IF NOT EXISTS `DemographicSurvey` (
  `user` varchar(60) NOT NULL,
  `demographicSurveyAge` int(10) unsigned NOT NULL,
  `demographicSurveyGender` varchar(60) NOT NULL,
  `demographicSurveyEducation` int(10) unsigned NOT NULL,
  `demographicSurveyPoliticalParty` varchar(200) NOT NULL,
  `demographicSurveyInterestInPolitics` int(10) unsigned NOT NULL,
  `demographicSurveyLikertHealth` int(10) unsigned NOT NULL,
  `demographicSurveyOpinionHealth` varchar(1500) NOT NULL,
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Feedback`
--

DROP TABLE IF EXISTS `Feedback`;
CREATE TABLE IF NOT EXISTS `Feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(60) NOT NULL,
  `feedbackInteresting` varchar(1500) NOT NULL,
  `feedbackBest` varchar(1500) NOT NULL,
  `feedbackLeast` varchar(1500) NOT NULL,
  `feedbackChange` varchar(1500) NOT NULL,
  `feedbackContinue` varchar(1500) NOT NULL,
  `feedbackRecommend` varchar(1500) NOT NULL,
  `feedbackLikertHealth` int(10) unsigned NOT NULL,
  `feedbackOpinionHealth` varchar(1500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `InvertedIndex`
--

DROP TABLE IF EXISTS `InvertedIndex`;
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

DROP TABLE IF EXISTS `Responses`;
CREATE TABLE IF NOT EXISTS `Responses` (
  `responseId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `responseText` varchar(1000) DEFAULT NULL,
  `user` varchar(60) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`responseId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=296 ;

-- --------------------------------------------------------

--
-- Table structure for table `ResponseSubpoints`
--

DROP TABLE IF EXISTS `ResponseSubpoints`;
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

DROP TABLE IF EXISTS `Users`;
CREATE TABLE IF NOT EXISTS `Users` (
  `user` varchar(60) NOT NULL DEFAULT '',
  `pass` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Votes`
--

DROP TABLE IF EXISTS `Votes`;
CREATE TABLE IF NOT EXISTS `Votes` (
  `responseId` int(10) NOT NULL,
  `parentId` int(10) NOT NULL,
  `user` varchar(60) NOT NULL,
  `vote` tinyint(1) NOT NULL,
  `aIds` varchar(2000) NOT NULL,
  PRIMARY KEY (`responseId`,`parentId`,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
