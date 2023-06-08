<?php
    require_once('api-adodb/config.php');

    class ModuleInstallation extends Table {


        public function __construct(){
            parent::__construct();
        }
        //Grind's Socile Module
        /****
        public function installSocial(){
            $sql = "CREATE TABLE IF NOT EXISTS `social` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `social_network` varchar(100) NOT NULL,
            `url` varchar(255) NOT NULL,
            `icon` varchar(255) NOT NULL COMMENT 'icon path',
            `profile` varchar(255) NOT NULL,
            `active` int(1) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `social_network` (`social_network`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('social', 'id', 'id', '', '', 1, '', 1),
            ('social', 'social_network', 'textfield', '', '', 1, '', 1),
            ('social', 'url', 'url', '', '', 1, '', 1),
            ('social', 'icon', 'photo_upload', '', '', 0, '', 1),
            ('social', 'profile', 'textfield', '', '', 0, '', 1),
            ('social', 'active', 'checkbox', '', '', 0, '', 1);";
            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
        }

                //Grind's NEWS Module
        public function installNews(){
            $sql = "CREATE TABLE IF NOT EXISTS `news` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `text` text NOT NULL,
            `date` timestamp NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('news', 'id', 'id', '', '', 1, '', 1),
            ('news', 'title', 'textfield', '', '', 1, '', 1),
            ('news', 'date', 'date', '', '', 1, '', 1),
            ('news', 'text', 'textarea', '', '', 1, '', 1);";
            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
        }


        Zahi's Old Gallery
        public function installGallery(){
            $sql = "CREATE TABLE `gallery` (
            `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `image` VARCHAR( 255 ) NOT NULL ,
            `caption` VARCHAR( 255 ) NOT NULL
            ) ENGINE = MYISAM ;";

            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('gallery', 'id', 'id', '', '', 1, '', 1),
            ('gallery', 'image', 'photo_upload', '', '', 1, '', 1),
            ('gallery', 'caption', 'textfield', '', '', 0, '', 1);";
            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
        }
               */
        public function installNewsModule(){
            $sql = "CREATE TABLE `news` (
            `id` int(1) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `picture` varchar(255) NOT NULL,
            `text` text NOT NULL,
            `date` date NOT NULL,
            `active` int(1) NOT NULL,
            `highlighted` int(1) NOT NULL,
            `pos` int(11) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;";

            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('news', 'id', 'id', '', '', 1, '', 1),
            ('news', 'name', 'textfield', '', '', 1, '', 1),
            ('news', 'picture', 'photo_upload', '', '', 1, '', 1),
            ('news', 'text', 'textarea', '', '', 1, '', 1),
            ('news', 'date', 'date', '', '', 0, '', 1),
            ('news', 'active', 'checkbox', '', '', 0, '', 1),
            ('news', 'highlighted', 'checkbox', '', '', 0, '', 1),
            ('news', 'pos', 'position', '', '', 0, '', 1);";
            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
        }


        public function installCVModule(){
            $sql = "CREATE TABLE `cvs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(250) NOT NULL,
            `profession` varchar(250) NOT NULL,
            `mobile` varchar(250) NOT NULL,
            `email` varchar(250) NOT NULL,
            `cv` varchar(250) NOT NULL,
            `message` longtext NOT NULL,
            `checked` int(1) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;";

            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('cvs', 'id', 'id', '', '', 1, '', 1),
            ('cvs', 'name', 'textfield', '', '', 0, '', 1),
            ('cvs', 'profession', 'textfield', '', '', 0, '', 1),
            ('cvs', 'mobile', 'textfield', '', '', 0, '', 1),
            ('cvs', 'email', 'email', '', '', 0, '', 1),
            ('cvs', 'cv', 'pdf_upload', '', '', 0, '', 1),
            ('cvs', 'message', 'textarea_nostyles', '', '', 0, '', 1),
            ('cvs', 'checked', 'checkbox', '', '', 0, '', 1);";
            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
        }


        public function installSocialModule(){
            $sql = "CREATE TABLE `social` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `facebook` varchar(250) NOT NULL,
            `twitter` varchar(250) NOT NULL,
            `youtube` varchar(250) NOT NULL,
            `flickr` varchar(250) NOT NULL,
            `linkedin` varchar(250) NOT NULL,
            `myspace` varchar(250) NOT NULL,
            `friendster` varchar(250) NOT NULL,
            `googleplus` varchar(250) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3;";


            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('social', 'id', 'id', '', '', 1, '', 1),
            ('social', 'facebook', 'textfield', '', '', 0, '', 1),
            ('social', 'twitter', 'textfield', '', '', 0, '', 1),
            ('social', 'youtube', 'textfield', '', '', 0, '', 1),
            ('social', 'flickr', 'textfield', '', '', 0, '', 1),
            ('social', 'linkedin', 'textfield', '', '', 0, '', 1),
            ('social', 'myspace', 'textfield', '', '', 0, '', 1),
            ('social', 'friendster', 'textfield', '', '', 0, '', 1),
            ('social', 'googleplus', 'textfield', '', '', 0, '', 1);";

            $sql3 = "INSERT INTO `social` VALUES(1, 'http://www.facebook.com', 'http://www.twitter.com', 'http://www.youtube.com', '', '', '', '', '');";

            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
            $this->adodb->Execute($sql3);
        }


        public function installMainModule(){
            $sql = "CREATE TABLE `main` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `home_text` longtext NOT NULL,
            `copyright_notice` varchar(999) NOT NULL,
            `contact_page_text` longtext NOT NULL,
            `careers_page_text` longtext NOT NULL,
            `google_map_iframe` longtext NOT NULL,
            `corporate_pdf_catalogue` varchar(999) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;";

            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('main', 'id', 'id', '', '', 1, '', 1),
            ('main', 'home_text', 'textarea', '', '', 0, '', 1),
            ('main', 'copyright_notice', 'textfield', '', '', 0, '', 1),
            ('main', 'contact_page_text', 'textarea', '', '', 0, '', 1),
            ('main', 'careers_page_text', 'textarea', '', '', 0, '', 1),
            ('main', 'google_map_iframe', 'textarea_nostyles', '', '', 0, '', 1),
            ('main', 'corporate_pdf_catalogue', 'pdf_upload', '', '', 0, '', 1);";

            $sql3 = "INSERT INTO `main` VALUES(1, '<p>Enthusiastically orchestrate inexpensive interfaces and go forward schemas. Dramatically reintermediate resource-leveling schemas whereas standardized content. Efficiently impact user friendly resources for mission-critical human capital. Holisticly synthesize visionary imperatives via performance based manufactured products. Quickly restore granular channels with web-enabled channels. <br /><br />Synergistically simplify exceptional applications with installed base deliverables. Intrinsicly envisioneer focused networks vis-a-vis standardized resources. Objectively customize process-centric solutions before one-to-one ROI. Quickly initiate enterprise-wide technologies after professional leadership. Completely reconceptualize customer directed networks without distinctive relationships.</p>', '© All Rights Reserved LSD 2012', '<p>Enthusiastically orchestrate inexpensive interfaces and go forward schemas. Dramatically reintermediate resource-leveling schemas whereas standardized content. Efficiently impact user friendly resources for mission-critical human capital. Holisticly synthesize visionary imperatives via performance based manufactured products. Quickly restore granular channels with web-enabled channels. <br /><br /><strong>Tel:</strong> +000 0 000 000<br /><strong>Fax:</strong> +000 0 000 000<br /><br /><strong>email:</strong> info@domain-name.com</p>', '<p>Enthusiastically orchestrate inexpensive interfaces and go forward schemas. Dramatically reintermediate resource-leveling schemas whereas standardized content. Efficiently impact user friendly resources for mission-critical human capital. Holisticly synthesize visionary imperatives via performance based manufactured products. Quickly restore granular channels with web-enabled channels. <br /><br /><strong>Tel:</strong> +000 0 000 000<br /><strong>Fax:</strong> +000 0 000 000<br /><br /><strong>email:</strong> info@domain-name.com</p>', '', '');";


            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
            $this->adodb->Execute($sql3);
        }

        /*
//Zahi's Old Gallery Module
//        public function installGalleryModule(){
//            $sql = "CREATE TABLE `gallery` (
//            `id` int(11) NOT NULL AUTO_INCREMENT,
//            `name` varchar(255) NOT NULL,
//            `main_image` varchar(255) NOT NULL,
//            `description` longtext NOT NULL,
//            `active` int(1) NOT NULL,
//            `highlighted` int(11) NOT NULL,
//            `pos` int(11) NOT NULL,
//            PRIMARY KEY (`id`)
//            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

//            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
//            ('gallery', 'id', 'id', '', '', 1, '', 1),
//            ('gallery', 'name', 'textfield', '', '', 1, '', 1),
//            ('gallery', 'main_image', 'photo_upload', '', '', 0, '', 1),
//            ('gallery', 'description', 'textarea_nostyles', '', '', 0, '', 1),
//            ('gallery', 'active', 'checkbox', '', '', 0, '', 1),
//            ('gallery', 'highlighted', 'checkbox', '', '', 0, '', 1),
//            ('gallery', 'pos', 'position', '', '', 0, '', 1);";
//            $this->adodb->Execute($sql);
//            $this->adodb->Execute($sql2);
//        }

        //Zahi's Old Media Module
        //public function installMediaModule(){
        //            $sql = "CREATE TABLE `media` (
        //            `id` int(111) NOT NULL AUTO_INCREMENT,
        //            `name` varchar(255) NOT NULL,
        //            `image` varchar(255) NOT NULL,
        //            `gallery` varchar(255) NOT NULL,
        //            `date` date NOT NULL,
        //            `description` longtext NOT NULL,
        //            `active` int(1) NOT NULL,
        //            `highlighted` int(1) NOT NULL,
        //            `pos` int(111) NOT NULL,
        //            PRIMARY KEY (`id`)
        //            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

        //            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
        //            ('media', 'id', 'id', '', '', 1, '', 1),
        //            ('media', 'name', 'textfield', '', '', 0, '', 1),
        //            ('media', 'image', 'photo_upload', '', '', 0, '', 1),
        //            ('media', 'gallery', 'foreign', 'gallery', 'name', 0, '', 1),
        //            ('media', 'date', 'date', '', '', 0, '', 1),
        //            ('media', 'description', 'textarea_nostyles', '', '', 0, '', 1),
        //            ('media', 'active', 'checkbox', '', '', 0, '', 1),
        //            ('media', 'highlighted', 'checkbox', '', '', 0, '', 1),
        //            ('media', 'pos', 'position', '', '', 0, '', 1);";
        //            $this->adodb->Execute($sql);
        //            $this->adodb->Execute($sql2);
        //        }

        */

        public function installMediaModule(){
            $sql = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('cmsgen_galleries', 'id', 'id', '', '', 0, '', 1),
            ('cmsgen_galleries', 'name', 'textfield', '', '', 0, '', 1),
            ('cmsgen_galleries', 'description', 'textarea', '', '', 0, '', 1),
            ('cmsgen_galleries', 'main_photo', 'photo_upload', '', '', 0, '', 1),
            ('cmsgen_galleries', 'active', 'checkbox', '', '', 0, '', 1),
            ('cmsgen_galleries', 'highlighted', 'checkbox', '', '', 0, '', 1),
            ('cmsgen_galleries', 'pos', 'position', '', '', 0, '', 1),


            ('cmsgen_gallerypictures', 'id', 'id', '', '', 0, '', 1),
            ('cmsgen_gallerypictures', 'gallery_id', 'foreign', 'cmsgen_galleries', 'name', 0, '', 1),

            ('cmsgen_gallerypictures', 'name', 'textfield', '', '', 0, '', 1),
            ('cmsgen_gallerypictures', 'description', 'textarea', '', '', 0, '', 1),
            ('cmsgen_gallerypictures', 'date', 'date', '', '', 0, '', 1),
            ('cmsgen_gallerypictures', 'active', 'checkbox', '', '', 0, '', 1),
            ('cmsgen_gallerypictures', 'highlighted', 'checkbox', '', '', 0, '', 1),
            ('cmsgen_gallerypictures', 'pos', 'position', '', '', 0, '', 1),

            ('cmsgen_gallerypictures', 'image_path', 'photo_upload', '', '', 0, '', 1)";

            $sql2 = "
            CREATE TABLE IF NOT EXISTS `cmsgen_galleries` (
            `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
            `name` VARCHAR( 255 ) NOT NULL ,
            `main_photo` VARCHAR( 255 ) NOT NULL ,
            `description` TEXT NOT NULL ,
            `active` TINYINT( 1 ) NOT NULL ,
            `highlighted` TINYINT( 1 ) NOT NULL ,
            `pos` INT( 11 ) NOT NULL ,
            PRIMARY KEY ( `id` )
            ) ENGINE = MYISAM ;
            ";

            $sql3 = "
            CREATE TABLE IF NOT EXISTS `cmsgen_gallerypictures` (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `gallery_id` INT NOT NULL ,
            `image_path` VARCHAR( 255 ) NOT NULL ,

             `name` VARCHAR( 255 ) NOT NULL ,
             `date` DATETIME NOT NULL ,
            `description` TEXT NOT NULL ,
            `active` TINYINT( 1 ) NOT NULL ,
            `highlighted` TINYINT( 1 ) NOT NULL ,
            `pos` INT( 11 ) NOT NULL ,

            PRIMARY KEY ( `id` )
            ) ENGINE = MYISAM ;";

            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
            $this->adodb->Execute($sql3);
        }


        public function installInstructionsModule(){
            $sql = "CREATE TABLE `instructions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `text` longtext NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;";

            $sql2 = "INSERT INTO `system` (`table_name`, `field_name`, `field_type`, `foreign_table`, `foreign_field`, `mandatory`, `parameters`, `active`) VALUES
            ('instructions', 'id', 'id', '', '', 1, '', 0),
            ('instructions', 'name', 'textfield', '', '', 0, '', 1),
            ('instructions', 'text', 'textarea_nostyles', '', '', 0, '', 0);";

            $this->adodb->Execute($sql);
            $this->adodb->Execute($sql2);
        }


    }
?>