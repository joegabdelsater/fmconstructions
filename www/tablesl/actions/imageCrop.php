<?php
require_once('requires.php');
require_once(LIB_PATH.DS.'loggedin.php');

if(!empty($_POST['table'])){
		$tableName = $_POST['table'];
}else{
		die("Please select a table!");
}
$table = new Table($tableName);
//Delete IDs

$id = (int)$_POST['recordId'];
if(empty($id )){

		die("Id not selected!");

}

$record = $table->findItemById($id );

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
		$targ_w = $_POST['w'];
		$targ_h = $_POST['h'];


		try{
				$validation = new Validation($tableName,$_POST['data'][$tableName]);
				$validation->validateFieldParams($_POST['fieldName']);
		} catch (Exception $e){

				echo $e->getMessage();
				exit;
		}

		$jpeg_quality = 80;

		$src = PUBLIC_PATH.DS.$_POST['src'];
		$img_r = imagecreatefromjpeg($src);
		$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
				$targ_w,$targ_h,$_POST['w'],$_POST['h']);

		$saveRel = 'images'.DS.$tableName.DS.'thumb_'.uniqid().basename($record[$_POST['thumbForFieldName']]);
		$saveTo = PUBLIC_PATH.DS.$saveRel;
		$webPath = HTML_SITE.DS.$saveRel.'?rand='.uniqid();
		//header('Content-type: image/jpeg');
		$isCropped = imagejpeg($dst_r,$saveTo,$jpeg_quality);

		try {
		if($table->updateField($id ,$_POST['fieldName'],$saveRel) || ($isCropped && !empty($record[$_POST['fieldName']])) ){
				echo $webPath;
		}else{
				echo '0';
		}

		} catch(Exception $e){
				echo $e->getMessage();
		}
		exit;
}
?>