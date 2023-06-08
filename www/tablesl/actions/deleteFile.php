<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');

$tableName = $_POST['table_name'];
$fieldName = $_POST['field_name'];
$id = $_POST['id'];

if(empty($tableName)){
		die("Please select a table.");
}
if(empty($fieldName)){
		die("Please select a field.");
}
if(empty($id)){
		die("Please select an id.");
}



try {

		$token = isset($_POST['token']) ? $_POST['token'] : 
        isset($_GET['token']) ? $_GET['token'] : null ;
		//CSRF::validate($token,false);

		$table = new Table($tableName);
		if($table->deleteColumn($id,$fieldName)){
				echo'success';
		} else {
				echo 'fail';
		}

} catch (Exception $e) {
		echo 'Invalid Request!';
}
?>