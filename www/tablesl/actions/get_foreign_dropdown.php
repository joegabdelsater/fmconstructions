<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();

$tableName = $_POST['table'];
$fieldName = $_POST['fieldname'];
$id = null;
$versionId = null;


$table = new Table($tableName);

$_SERVER['PHP_SELF']="generate.php"; // trick to thinking we're in generate page so getFormElement appends data['tablename']
echo $table->getFormElement($fieldName,$id,$versionId,array()); //Displays the corresponding form element to the following field