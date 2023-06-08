<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');

if(!empty($_POST['table'])){
		$tableName = $_POST['table'];
}else{
		die("Please select a table!");
}

$table = new Table($tableName);

$action = $_POST['action'];
$order = $_REQUEST['sort'];
$page = (int)$_POST['page'] - 1;
if ($action == "updateRecordsListings"){
		$table->updateRowPositions($order,$page);
}
?>