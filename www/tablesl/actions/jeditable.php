<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');

ob_start();

if(!empty($_POST['table'])){
		$tableName = $_POST['table'];
}else{
		die("Please select a table!");
}

$table = new Table($tableName);


$values = explode("|", $_POST['id']);
$column = $values[0];
$id = $values[1];
$originalValue = $values[2];

$newValue = $_POST['value'];

try {

		$token = isset($_POST['token']) ? $_POST['token'] : NULL;
		unset($_POST['token']);
		CSRF::validate($token,false);
		$data = array("id"=>$id, $column => $newValue);
		
		
		$id = $table->update($data);
		if($id !== false){
				if ($newValue == 'off') {
					$newValue = "No";
				}
				if ($newValue == 'on') {
					$newValue = "Yes";
				}
				echo $newValue;
				//echo json_encode(array("status"=>"success","value"=>$newValue));
		}else{
				$msg = '';
				if ($newValue == $originalValue) {
						$msg = 'Old value same as new one, please enter a new value.';
				}
				//echo json_encode(array("status"=>"fail","value"=>$originalValue, "msg"=>$msg ));
				echo $originalValue;
		}

} catch (Exception $e) {
		//echo json_encode(array("status"=>"fail","value"=>$originalValue, "msg"=>"Invalid Request" ));
		echo $originalValue;
}
?>