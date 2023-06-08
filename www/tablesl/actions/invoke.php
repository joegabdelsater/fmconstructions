<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();

if(!empty($_GET['table'])){
		$tableName = $_GET['table'];
}else{
		die("Please select a table!");
}

$table = new Table($tableName);
$allowedActions = array ('');
unset($_GET['table']);
$action = $_GET['action'];
unset($_GET['action']);
$params = isset($_GET['params']) ? (array) $_GET['params'] : array ();

if(!in_array($action,$allowedActions)){
		$result = call_user_func_array(array($table,$action),$params);
}


echo json_encode($result);
?>