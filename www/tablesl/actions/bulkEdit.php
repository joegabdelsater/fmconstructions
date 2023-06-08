<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();

try {

		$token = isset($_POST['token']) ? $_POST['token'] : NULL;
		unset($_POST['token']);
		CSRF::validate($token,false);

		if(!empty($_POST['table'])){
				$tableName = $_POST['table'];
		}else{
				die("Please select a table!");
		}

		$table = new Table($tableName);
	 
		if(!isset($_POST['cmsgen_rowsToEdit'])){
				$result = array ('status' => 'fail', 'error' => 'Please select at least one row to edit.');
		}else{
				
				$rowsToEdit = $_POST['cmsgen_rowsToEdit'];

				unset($_POST['cmsgen_rowsToEdit']);
				unset($_POST['table']);
				foreach($_POST as $key=>$value){
						if(empty($value)){
								unset($_POST[$key]);
						}
				}
				$data = $_POST;
				
				if(!empty($data)){
						
						foreach($rowsToEdit as $id){
								$data['id'] = $id;
								if($table->update($data)){
										$result = array('status' => 'success');
								}else{
										$result = array('status' => 'fail', 'error' => 'There was an error saving. Perhaps nothing was changed');
								}
						}
				}else{
					
						$result = array('status' => 'fail', 'error' => 'All fields were left blank.');
				}
		}

		

} catch (Exception $e) {

		$result = array('status' => 'fail', 'error' => 'Invalid Request.');
}

echo json_encode($result);
?>