<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');

if(!empty($_GET['tableName'])){
	$tableName = $_GET['tableName'];
}else{
	die("Please select a table!");
}

$table = new Table($tableName);
$fields = $table->getTableFields();
$json = array();
foreach($fields as $field){
	$json [$field] = printTableName($field);
}
echo json_encode($json);
?>