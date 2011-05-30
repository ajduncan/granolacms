SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `granola`
--

-- --------------------------------------------------------

--
-- Table structure for table `granola_contact_information`
--

CREATE TABLE IF NOT EXISTS `granola_contact_information` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `lname` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `mname` char(1) CHARACTER SET latin1 DEFAULT NULL,
  `phone1` varchar(25) CHARACTER SET latin1 DEFAULT NULL,
  `phone2` varchar(25) CHARACTER SET latin1 DEFAULT NULL,
  `fax` varchar(25) CHARACTER SET latin1 DEFAULT NULL,
  `cell` varchar(25) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_contact_information`
--

INSERT INTO `granola_contact_information` (`id`, `fname`, `lname`, `mname`, `phone1`, `phone2`, `fax`, `cell`) VALUES
(1, 'super', 'admin', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `granola_content`
--

CREATE TABLE IF NOT EXISTS `granola_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `published` enum('0','1') CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `published_revision` int(10) unsigned DEFAULT NULL,
  `approved` enum('0','1') CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `show_last_edited` tinyint(1) NOT NULL DEFAULT '0',
  `show_last_author` tinyint(1) NOT NULL DEFAULT '0',
  `filename` text CHARACTER SET latin1,
  `type` int(2) NOT NULL DEFAULT '1',
  `flags` set('global') DEFAULT NULL,
  `header` int(10) unsigned NOT NULL DEFAULT '0',
  `footer` int(10) unsigned NOT NULL DEFAULT '0',
  `stylesheet` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_content`
--

INSERT INTO `granola_content` (`id`, `owner_id`, `name`, `published`, `published_revision`, `approved`, `show_last_edited`, `show_last_author`, `filename`, `type`, `flags`, `header`, `footer`, `stylesheet`) VALUES
(1, 1, 'index', '1', NULL, '1', 0, 0, '/root/index.inc.php', 1, NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `granola_content_groups`
--

CREATE TABLE IF NOT EXISTS `granola_content_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `access` set('view','update','approve','publish','delete') NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_content_groups`
--

INSERT INTO `granola_content_groups` (`id`, `content_id`, `group_id`, `access`) VALUES
(1, 1, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `granola_content_revisions`
--

CREATE TABLE IF NOT EXISTS `granola_content_revisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `revision` int(5) unsigned NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text` text,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_content_revisions`
--

INSERT INTO `granola_content_revisions` (`id`, `content_id`, `user_id`, `revision`, `timestamp`, `text`) VALUES
(1, 1, 1, 1, '2010-09-11 18:28:35', '');

-- --------------------------------------------------------

--
-- Table structure for table `granola_content_tags`
--

CREATE TABLE IF NOT EXISTS `granola_content_tags` (
  `tag_id` int(10) unsigned NOT NULL,
  `content_id` int(10) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `granola_content_tags`
--


-- --------------------------------------------------------

--
-- Table structure for table `granola_groups`
--

CREATE TABLE IF NOT EXISTS `granola_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` text,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `header` int(10) unsigned NOT NULL DEFAULT '0',
  `footer` int(10) unsigned NOT NULL DEFAULT '0',
  `stylesheet` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_groups`
--

INSERT INTO `granola_groups` (`id`, `name`, `description`, `parent_id`, `header`, `footer`, `stylesheet`) VALUES
(1, 'root', 'The root group', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `granola_group_calendar`
--

CREATE TABLE IF NOT EXISTS `granola_group_calendar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `text` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `granola_group_calendar`
--


-- --------------------------------------------------------

--
-- Table structure for table `granola_group_publishing`
--

CREATE TABLE IF NOT EXISTS `granola_group_publishing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) unsigned NOT NULL,
  `type` int(3) NOT NULL DEFAULT '1',
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `hostname` varchar(50) DEFAULT NULL,
  `directory` varchar(100) DEFAULT NULL,
  `uri` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `granola_group_publishing`
--


-- --------------------------------------------------------

--
-- Table structure for table `granola_help`
--

CREATE TABLE IF NOT EXISTS `granola_help` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_help`
--


-- --------------------------------------------------------

--
-- Table structure for table `granola_help_category`
--

CREATE TABLE IF NOT EXISTS `granola_help_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `order` int(5) NOT NULL DEFAULT '0',
  `description` text,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_help_category`
--


-- --------------------------------------------------------

--
-- Table structure for table `granola_modules`
--

CREATE TABLE IF NOT EXISTS `granola_modules` (
  `key` varchar(255) NOT NULL,
  `status` enum('0','1') NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `granola_modules`
--


-- --------------------------------------------------------

--
-- Table structure for table `granola_tags`
--

CREATE TABLE IF NOT EXISTS `granola_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `granola_tags`
--


-- --------------------------------------------------------

--
-- Table structure for table `granola_users`
--

CREATE TABLE IF NOT EXISTS `granola_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `type` enum('admin','normal') NOT NULL DEFAULT 'normal',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_users`
--

INSERT INTO `granola_users` (`id`, `parent_id`, `email`, `password`, `type`, `contact_id`) VALUES
(1, 0, 'andy@duncaningram.com', 'sillytest', 'admin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `granola_user_content`
--

CREATE TABLE IF NOT EXISTS `granola_user_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `access` set('vcontent','ucontent','pcontent','dcontent') NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `granola_user_content`
--

INSERT INTO `granola_user_content` (`id`, `content_id`, `user_id`, `access`) VALUES
(3, 1, 1, 'vcontent,ucontent');

-- --------------------------------------------------------

--
-- Table structure for table `granola_user_groups`
--

CREATE TABLE IF NOT EXISTS `granola_user_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `access` set('cgroup','dgroup','auser','duser','vcontent','ucontent','pcontent','dcontent') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `granola_user_groups`
--

INSERT INTO `granola_user_groups` (`id`, `user_id`, `group_id`, `access`) VALUES
(1, 1, 1, 'cgroup,dgroup,auser,duser,vcontent,ucontent,pcontent,dcontent');
