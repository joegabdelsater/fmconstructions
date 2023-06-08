-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 14, 2012 at 03:54 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cmsgentest`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_advanced_settings`
--

CREATE TABLE IF NOT EXISTS `admin_advanced_settings` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `ip_deny` text NOT NULL,
  `ip_allow` text NOT NULL,
    `site_offline` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `admin_advanced_settings`
--

INSERT INTO `admin_advanced_settings` (`id`, `ip_deny`, `ip_allow`) VALUES
(1, '192.168.65.01', '192.168.65.02');

-- --------------------------------------------------------

--
-- Table structure for table `cmsgen_contenthistory`
--
CREATE TABLE `cmsgen_contenthistory` (
`id` INT NOT NULL AUTO_INCREMENT ,
`table_name` VARCHAR( 255 ) NOT NULL ,
`entry_id` INT NOT NULL ,
`edited_by` INT NOT NULL ,
`data` TEXT NOT NULL ,
`created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;

-- --------------------------------------------------------

--
-- Table structure for table `backend_structure`
--

CREATE TABLE IF NOT EXISTS `backend_structure` (
  `field_type` varchar(255) NOT NULL,
  `html` text NOT NULL,
  `common_name` text NOT NULL COMMENT 'diverse name of the table cols that have similar functaionlity',
  `bValidator` varchar(255) NOT NULL,
  UNIQUE KEY `field_type` (`field_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `backend_structure`
--

INSERT INTO `backend_structure` (`field_type`, `html`, `common_name`, `bValidator`) VALUES
('country', '<select @FIELD_INFO@ >\r\n<option value="Syria">Syria</option>\r\n<option value="Lebanon">Lebanon</option>\r\n<option value="Italy">Italy</option>\r\n<option value="Germany">Germany</option>\r\n</select>', 'country,nationality,pob,place_of_birth', ''),
('select', '', '', ''),
('photo_upload', '<input type="file" @FIELD_INFO@ />', 'picture,image,bg,background,icon,icon_path,image_path,photo,photo_path', 'image'),
('position', '', 'pos,position', ''),
('password', '<input type="password" @FIELD_INFO@ />', 'password', ''),
('time', '<select></select>', 'time', ''),
('textarea', '<textarea class="mceEditor" cols="60" rows="6" @FIELD_INFO@ >@FIELD_VALUE@</textarea>', 'desc,description,info,text', ''),
('limited_textarea', '<div class="limitedCharacterText"><textarea cols="60" rows="6" @MAX_LENGTH_COUNT_ATTRIBUTE@ @FIELD_INFO@ >@FIELD_VALUE@</textarea><div class="limitedCharacterCountDisplay">@MAX_LENGTH_COUNT@</div></div>', 'caption', ''),
('textarea_nostyles', '<textarea cols="60" rows="6" @FIELD_INFO@ >@FIELD_VALUE@</textarea>', 'parameters,params', ''),
('date', '<input type="text" @FIELD_INFO@ class="date date-pick" value="@FIELD_VALUE@" autocomplete="off"  />', 'date,dob,date_of_birth', 'date[yyyy-mm-dd]'),
('datetime', '<input type="text" @FIELD_INFO@ class="date datetime-pick" value="@FIELD_VALUE@" autocomplete="off"  />','datetime,date_time',''),
('auto_date', '<input />', 'date_sent,date_added', ''),
('pdf_upload', '<input type="file" @FIELD_INFO@ />', 'pdf,pdf_path', ''),
('mp3_upload', '<input type="file" @FIELD_INFO@ />', 'mp3,mp3_path', ''),
('url', '<input type="text" @FIELD_INFO@ class="url" value="@FIELD_VALUE@" placeholder="http://" />', 'link,url', 'url'),
('textfield', '<input type="text" @FIELD_INFO@ value="@FIELD_VALUE@"  />', 'textfield,name,username', ''),

('thumbnail', '<input type="text" @FIELD_INFO@ value="@FIELD_VALUE@"  />', 'thumbnail,thumb,thumb_path', ''),

('id', '<input type="hidden" @FIELD_INFO@ value="@FIELD_VALUE@"  />', 'id', ''),
('foreign', '', '', ''),
('habtm_foreign', '', '', ''),
('colorpicker', '<input type="text" @FIELD_INFO@ value="@FIELD_VALUE@" class="color"  />', 'color', ''),
('checkbox', '<input type="hidden" @FIELD_INFO@ value="0" /><input type="checkbox" @FIELD_INFO@ @FIELD_VALUE@ />', 'active,highlight,highlighted,published', ''),
('email', '<input type="text" @FIELD_INFO@ value="@FIELD_VALUE@"  />', 'email,mail,e-mail', 'email'),
('slug', '', 'slug', ''),
('enum', '', 'enum,gender', '');

-- --------------------------------------------------------

--
-- Table structure for table `cmsgen_cpanel_links`
--

CREATE TABLE IF NOT EXISTS `cmsgen_cpanel_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `cmsgen_cpanel_links`
--



-- --------------------------------------------------------

--
-- Table structure for table `cmsgen_default_values`
--

CREATE TABLE IF NOT EXISTS `cmsgen_default_values` (
  `field_type` varchar(255) NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `field_type` (`field_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cmsgen_default_values`
--

INSERT INTO `cmsgen_default_values` (`field_type`, `value`) VALUES
('url', 'http://www.example.com'),
('textfield', 'Lorem Ipsum'),
('textarea', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam sollicitudin vulputate enim, at egestas tellus tempus vitae. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam interdum dignissim tortor, ut consequat sem dignissim eu. Vestibulum consequat, justo sit amet commodo tristique, velit justo molestie lectus, ac scelerisque arcu ante et nulla. Fusce lacinia sapien sed sem pharetra volutpat. Mauris condimentum ultricies tempus. Sed mollis urna vitae urna lacinia imperdiet. Nulla facilisi. Quisque sagittis tincidunt orci. Aliquam erat volutpat.'),
('photo_upload', 'images/bulk.jpg'),
('email', 'admin@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(39) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(255) NOT NULL,
  `action` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `logs`
--



-- --------------------------------------------------------

--
-- Table structure for table `site_options`
--

CREATE TABLE IF NOT EXISTS `site_options` (
  `id` int(11) unsigned NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `favicon` varchar(255) NOT NULL,
  `title_bar` varchar(200) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `site_options`
--

INSERT INTO `site_options` (`id`, `project_name`, `favicon`, `title_bar`, `meta_description`, `meta_keywords`, `contact_email`, `from_email`) VALUES
(1, 'CMS', '', '', '', '', 'email@site.com', 'email@site.com');

-- --------------------------------------------------------

--
-- Table structure for table `system`
--

CREATE TABLE IF NOT EXISTS `system` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_type` varchar(255) NOT NULL,
  `foreign_table` varchar(100) NOT NULL,
  `foreign_field` varchar(255) NOT NULL,
  `mandatory` tinyint(1) NOT NULL,
  `parameters` text NOT NULL,
  `tooltip` text NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'determines if the field is visible in the website or not',
  `is_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;

--
-- Dumping data for table `system`
--

INSERT INTO `system` (`id`, `table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
(8, 'site_options', 'project_name', 'textfield', '', '', 1, '', 1),
(9, 'site_options', 'favicon', 'photo_upload', '', '', 0, '', 1),
(10, 'site_options', 'title_bar', 'textfield', '', '', 1, '', 1),
(11, 'site_options', 'meta_description', 'textarea_nostyles', '', '', 0, '', 1),
(12, 'site_options', 'meta_keywords', 'textarea_nostyles', '', '', 0, '', 1),
(13, 'admin_advanced_settings', 'site_offline', 'checkbox', '', '', 0, '', 1),
(14, 'site_options', 'contact_email', 'email', '', '', 0, '', 1),
(15, 'site_options', 'from_email', 'email', '', '', 0, '', 1),
(16, 'admin_advanced_settings', 'id', 'id', '', '', 1, '', 1),
(17, 'admin_advanced_settings', 'ip_deny', 'textarea_nostyles', '', '', 0, '', 1),
(18, 'admin_advanced_settings', 'ip_allow', 'textarea_nostyles', '', '', 0, '', 1),

(38, 'cmsgen_cpanel_links', 'id', 'id', '', '', 1, '', 1),
(39, 'cmsgen_cpanel_links', 'active', 'checkbox', '', '', 0, '', 1),
(40, 'cmsgen_cpanel_links', 'link', 'url', '', '', 1, '', 1),
(41, 'cmsgen_cpanel_links', 'name', 'textfield', '', '', 0, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `table_options`
--

CREATE TABLE IF NOT EXISTS `table_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL,
  `display_fields` varchar(1000) NOT NULL,
  `link_format` varchar(255) NOT NULL,
  `disable_crud` int(1) NOT NULL COMMENT 'disable CReateUpdateDelete',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `table_options`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(60) NOT NULL,
  `email` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_level` int(1) unsigned NOT NULL,
  `disallow` text NOT NULL,
  `verification` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `active`, `last_login`, `user_level`, `disallow`, `verification`) VALUES
(1, 'admin', '$2a$07$w7h7g990jJuNksa8Hsh7H.4XQfiMmn8PBqFxbLYs9gU41xGs7tlGy', 'test@test.com', 1, '2012-11-06 12:44:42', 9, '', '');

CREATE TABLE IF NOT EXISTS `cmsgen_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `browser` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `thedate_visited` date NOT NULL DEFAULT '0000-00-00',
  `page` varchar(70) NOT NULL DEFAULT '',
  `from_page` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
