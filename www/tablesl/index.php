<?php
require_once('common/custom-config.php');
require_once(CUSTOM_SERVER_CONFIG);

require_once(LIB_PATH.DS.'user.class.php');
require_once(LIB_PATH.DS.'session.class.php');
$user = new User();

// try to login when form submitted

if($session->is_logged_in()) {
        redirect_to("dashboard.php");
}else{
        redirect_to("login.php");
}
?>