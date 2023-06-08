<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
if(!empty($_GET['table'])){
		$tableName = $_GET['table'];
}else{
		die("Please select a table!");
}

redirect_to("generate.php?table=$tableName");
?>