<?php
require_once('common/custom-config.php');
require_once(CUSTOM_SERVER_CONFIG);

require_once(LIB_PATH.DS.'loggedin.php');

$tableName = isset($_GET['table']) ? trim($_GET['table']) : NULL;
if(empty($tableName) || ! ($table = new Table($tableName))){
		die("Table not found!");
}

$table->exportToCsv();
?>
