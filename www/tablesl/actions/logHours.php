<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();

try {

    $table = new Table('project_logs');

    
    $entry = $table->create($_POST);
    if ($entry > 0) {
        $result = array('status' => 'success');
    } else {
        $result = array('status' => 'fail', 'error' => 'All fields were left blank.');
    }


} catch (Exception $e) {

    $result = array('status' => 'fail', 'error' => 'Invalid Request.');
}

echo json_encode($result);
?>