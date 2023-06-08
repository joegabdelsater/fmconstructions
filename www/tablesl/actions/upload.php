<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');
ob_start();


$ajax = false;
if (!empty($_POST['ajax'])) {
	$ajax = true;
	unset($_POST['ajax']);
}

try {

	$token = isset($_POST['token']) ? $_POST['token'] : NULL;
	unset($_POST['token']);
	CSRF::validate($token,false);


	$formDataArray = $_POST['data'];
	$filesDataArray = array (); 



	if(isset($_FILES['data'])){
		$filesDataArray = $_FILES['data'];
	}

	foreach($formDataArray as $tableName => $tableData){



		// if(isset($_POST['id'])){
		//            $id = $_POST['id'];
		//        }
		$_POST = $tableData;
		$_POST['table'] = $tableName;
		// if(isset($id)){
		//            $_POST['id'] = $id;   
		//        }

		//Aggregate the $_FILES array
		$_FILES = array (); 
		foreach($filesDataArray as $index => $array){
			if(isset($array[$tableName])){
				foreach($array[$tableName] as $fieldName => $value){
					$_FILES[$fieldName][$index] = $value;
				}
			}
		}



		if(!empty($_POST['table'])){
			$tableName = $_POST['table'];
		}else{
			die("Please select a table!");
		}


		$table = new Table($tableName);


		if(isPostEmpty()){
			$result = "Please fill in values.";
			echo $result;
		}else{
			try{

				$id = $table->save($_POST);
				$result = 1;



				if($table->isPhotoGallery() && empty($_POST['id'])){
					echo json_encode(array('success' => true));
					die;
				}

				echo $id !== true ? 'Created!' : 'Updated!';

			} catch (Exception $e){
				$result = $e->getMessage();
				if($table->isPhotoGallery() && empty($_POST['id']) ){
					echo json_encode(array('error' => $result));
					die;
				}
				echo $e->getMessage();
			}


			/*
			$id = $table->save($_POST);

			if(is_numeric($id) || $id === true){
			$session->message("Your entry was saved!");
			$result = 1;
			echo $id !== true ? 'Created!' : 'Updated!';
			}elseif($id == 'file_upload_failed'){
			echo $id;
			$result = 'File Upload Failed! Try changing the file name.';
			echo 'File upload failed!';
			}elseif($id == 'invalid_mime_type'){
			$result = 'Your file is an invalid format';
			echo 'Invalid Mime Type!';
			}elseif($id == 'invalid_max_image_height'){

			$result = $session->printMessage();
			echo 'Invalid Max Image Height!';
			}elseif($id == 'invalid_max_image_width'){
			$result = $session->printMessage();
			echo 'Invalid Max Image WIDTH!';
			}elseif($id == 'invalid_image_proportions'){
			$result = 'Your image has invalid proportions';
			echo 'Invalid Image Proportions!';
			}else{

			$result = 'Nothing is saved. Perhaps you did not change anything?';
			echo'Nothing changed!';
			}
			*/
		}

	}

} catch (Exception $e) {
	$result = "Invalid Request. Try refreshing the page and trying again.";
}

if ($ajax) { ?>
	<script language="javascript" type="text/javascript">alert('Saved');</script>
	<?php
}
else {
	?>
	<script language="javascript" type="text/javascript">window.top.window.stopUpload("<?php echo $result; ?>","<?php echo hashTable($tableName); ?>");</script>
	<?php } ?>