<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');

try {

		$token = isset($_POST['token']) ? $_POST['token'] : NULL;
		CSRF::validate($token,false);

		if(!empty($_POST['table'])){
				$tableName = $_POST['table'];
		}else{
				die("Please select a table!");
		}
		$table = new Table($tableName);
		//Delete IDs
		if(!empty($_POST['ids'])){
				try {
						$table->deleteRows($_POST['ids']);
						echo 'Deleted!';
				} catch (Exception $e){
						echo $e->getMessage();
				}

		} else {
				echo 'Please select at least 1 item.';
		}

} catch (Exception $e) {
		echo 'Invalid Request!';
}
?>