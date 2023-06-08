<?php
    require_once(LIB_PATH.DS.'session.class.php');
    require_once(LIB_PATH.DS.'database.class.php');
    if (!$session->is_logged_in()) {
        redirect_to("login.php");
    }
    $user = User::find_by_id($session->user_id);

    if (empty($user)){
        redirect_to("logout.php");
    }
?>