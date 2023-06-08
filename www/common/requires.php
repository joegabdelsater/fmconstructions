<?php
header("Content-Type: text/html; charset=utf-8");
if(!function_exists("thumbnailLink")){
    require_once('../server/api-adodb/config.php');
}
new Statistics();

require_once(LIB_PATH.DS.'session.class.php');
require_once(LIB_PATH.DS.'database.class.php');
require_once(LIB_PATH.DS.'site.class.php');

$isLoggedIn = false;
if ($session->is_logged_in()) {
    if($user = User::find_by_id($session->user_id)){
        $isLoggedIn = true;
    }
}

//Check if the website is offline!
$site = new Site();
if($site->siteOffline){
    //If offline, display a message or an image or anything
    //die();
    header("Location: offline.html");
}
?>
