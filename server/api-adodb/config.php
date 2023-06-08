<?php
    // defined('HTML_IMAGE')  ? null : define('HTML_IMAGE', 'http://localhost:8888/cmsgen2/web/');// add the / at the end
    defined('DS')           ? null : define('DS', '/');

    //Constant to declare whether you want URL re-writing in the admin area or not
    defined('REWRITE_ENABLED') ? null : define('REWRITE_ENABLED', false);
    //Whether to hash the table name in urls or not
    defined('HASH_TABLE_NAME') ? null : define('HASH_TABLE_NAME', false);
    //Enable or disable live edit
    defined('LIVE_EDIT') ? null : define('LIVE_EDIT', false);
    //Display a "View on website" link in the list.php page
    defined('VIEW_ON_WEBSITE_LINK')     ? null : define('VIEW_ON_WEBSITE_LINK', false );

    //    if($_SERVER["HTTP_HOST"] == 'localhost'){
    if(strpos($_SERVER["HTTP_HOST"],'local') !== false){
        /************************
        * OFFLINE SITE CONSTANTS*
        ***********************/

        error_reporting(E_ERROR);

        // Database Constants
        defined('DB_SERVER') ? null : define("DB_SERVER", "localhost");
        defined('DB_USER')   ? null : define("DB_USER", "fmconstructions");
        defined('DB_PASS')   ? null : define("DB_PASS", 'ikkcmf32cz46ciuf3inj');
        defined('DB_NAME')   ? null : define('DB_NAME', "fmconstructions");
        defined('EMAIL_FROM')   ? null : define('EMAIL_FROM','Site Name');

        defined('SITE_ROOT')    ? null : define('SITE_ROOT', str_replace("\\", "/", dirname(dirname(dirname(__FILE__)))) );

        defined('HTML_SITE')  ? null : define('HTML_SITE', '');
        defined('PUBLIC_HTML_SITE')  ? null : define('PUBLIC_HTML_SITE', HTML_SITE);


        defined('HTML_ROOT')  ? null : define('HTML_ROOT', '/fmc_cms/www'); //This is used for generating thumbs. it must start with a / and must NOT end with a /
        defined('WEB_ROOT')  ? null : define('WEB_ROOT', '/fmc_cms/www'); //This is used for generating thumbs. it must start with a / and must NOT end with a /

        //Where server files are placed
        defined('LIB_PATH')     ? null : define('LIB_PATH', SITE_ROOT.DS.'server');
        defined('WEB_PATH')     ? null : define('WEB_PATH', SITE_ROOT.DS.'www');
        defined('PUBLIC_FOLDER')     ? null : define('PUBLIC_FOLDER', '');
        //Where admin area is located
        defined('ADMIN_PATH')     ? null : define('ADMIN_PATH', SITE_ROOT.DS.'www'.DS.'tablesl');
        //Where the web docs are placed
        defined('PUBLIC_PATH')  ? null : define('PUBLIC_PATH', SITE_ROOT.DS.'www');
        defined('IMAGES_PATH')  ? null : define('IMAGES_PATH', SITE_ROOT.DS.'www'); // where to upload images
        defined('ADMIN_PATH_HTML')     ? null : define('ADMIN_PATH_HTML',HTML_SITE.'/tablesl');
        defined('DEBUG')     ? null : define('DEBUG',0);

    }else{
        // GRANT ALL PRIVILEGES ON your_database.* TO 'your_user'@'172.18.0.1'; IDENTIFIED BY 'your_password';

        // GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'172.18.0.1';


        /************************
        * ONLINE SITE CONSTANTS*
        ***********************/
        error_reporting(0);

    // Database Constants

       defined('DB_SERVER') ? null : define("DB_SERVER", "localhost");
        defined('DB_USER')   ? null : define("DB_USER", "fmconstructions");
        defined('DB_PASS')   ? null : define("DB_PASS", 'ikkcmf32cz46ciuf3inj');
        defined('DB_NAME')   ? null : define('DB_NAME', "fmconstructions");
        defined('EMAIL_FROM')   ? null : define('EMAIL_FROM','Site Name');

         defined('SITE_ROOT')    ? null : define('SITE_ROOT', dirname(dirname(dirname(__FILE__))) );

        // defined('HTML_SITE')  ? null : define('HTML_SITE', 'https://fmc.techissimple.com/');
        // defined('HTML_SITE')  ? null : define('HTML_SITE', 'http://167.172.164.151');
        // defined('HTML_SITE')  ? null : define('HTML_SITE', 'http://fmconstructions.com');
        defined('HTML_SITE')  ? null : define('HTML_SITE', 'https://fmconstructions.com');

        


        defined('PUBLIC_HTML_SITE')  ? null : define('PUBLIC_HTML_SITE', HTML_SITE);


        defined('HTML_ROOT')  ? null : define('HTML_ROOT', ''); //This is used for generating thumbs. it must start with a / and must NOT end with a /
        defined('WEB_ROOT')  ? null : define('WEB_ROOT', ''); //This is used for generating thumbs. it must start with a / and must NOT end with a /

        //Where server files are placed
        defined('LIB_PATH')     ? null : define('LIB_PATH', SITE_ROOT.DS.'server');
        defined('WEB_PATH')     ? null : define('WEB_PATH', SITE_ROOT.DS.'www');
        defined('PUBLIC_FOLDER')     ? null : define('PUBLIC_FOLDER', '');
        //Where admin area is located
        defined('ADMIN_PATH')     ? null : define('ADMIN_PATH', SITE_ROOT.DS.'www'.DS.'tablesl');
        //Where the web docs are placed
        defined('PUBLIC_PATH')  ? null : define('PUBLIC_PATH', SITE_ROOT.DS.'www');
        defined('IMAGES_PATH')  ? null : define('IMAGES_PATH', SITE_ROOT.DS.'www'); // where to upload images
        defined('ADMIN_PATH_HTML')     ? null : define('ADMIN_PATH_HTML',HTML_SITE.'/tablesl');
        defined('DEBUG')     ? null : define('DEBUG',false);




    }


    //Where the images are saved once uploaded
    defined('IMAGES_DIR')     ? null : define('IMAGES_DIR','images');
    //Where the pdf files are saved once uploaded
    defined('PDF_DIR')     ? null : define('PDF_DIR','images');
    //Number of items per page in listing
    defined('PER_PAGE')     ? null : define('PER_PAGE',1000);
    //Number of emails that can be sent per day for the mailing list
    defined('EMAIL_LIMIT')     ? null : define('EMAIL_LIMIT',10);

    defined('IMAGE_DESTINATION')     ? null : define('IMAGE_DESTINATION','images'); //Folder where web images are present
    //If a website is hosted with LSD => 1 .. else =>0
defined('HOSTED_WITH_US')   ? null : define('HOSTED_WITH_US', 1);

    defined('DISABLE_SELECT2')   ? null : define('DISABLE_SELECT2', false);


    /**
    * Defaut Time Zone
    */
    date_default_timezone_set('Asia/Beirut');

    ##NOTE:##
    ##These tables structure must be added to the system table so that the automatic form generation is generated correctly
    //System tables that users are not allow to modify themselves
    $systemTables = array('backend_structure','system','users','logs','table_options','cmsgen_default_values','cmsgen_statistics','cmsgen_mailing_list_queue','cmsgen_mailing_list_messages','cmsgen_contenthistory');

    //Here are the names of the tables that are only accessible to administrators
    $adminTables = array('cmsgen_cpanel_links','admin_advanced_settings');
    //Tables that have only one rows, or predefined number of rows (NO add/edit/delete)
    $oneRowTables = array('site_options','admin_advanced_settings');

    //Files to require
    require_once(LIB_PATH.DS.'api-adodb'.DS.'db.class.php');
    require_once(LIB_PATH.DS.'api-adodb'.DS.'version.php');
    require_once(LIB_PATH.DS.'functions.php');

    License::notifyUsageByDomain();

?>
