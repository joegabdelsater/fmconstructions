<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();

$tableName = $_POST['tableName'];
$_POST['table'] = $tableName;
$table = new Table($tableName);

$displayField = $table->getDisplayField();

$_POST[$displayField] = $_POST['text'];

$id = $table->save($_POST);

$json['id'] = $id;
$json['text'] = $_POST['text'];

echo json_encode($json);
?>
 