<?php
#error_reporting(E_ALL);
require_once('requires.php');
require_once ('image.class.php');
require_once(LIB_PATH.DS.'loggedin.php');

// swf uploadify sends the file here
if (!empty($_FILES)){
	## check for valid extension
	$fileTypeExts = "'*.gif;*.jpg;*.png'";
	if (isset($fileTypeExts)) {
		$file = $_FILES['Filedata']['name'];
		$file = utf8_decode($file);
		$file = preg_replace("/[^a-zA-Z0-9_.\-\[\]]/i", "", strtr($file, "()באגדהיטךכםלמןףעפץצתשûüחְֱֲֳִָֹÊֻּֽ־ֿ׃ׂװױײÚÙÛÜַ% ", "[]aaaaaeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUC__"));
		$file = strtolower($file);
		$fileParts = pathinfo($file);
		$fileExtension = $fileParts['extension'];

		$fileTypes = str_replace('*.','',$fileTypeExts);
		$fileTypes = str_replace(';','|',$fileTypes);
		$fileTypes = str_replace("'",'',$fileTypes);
		$typesArray = explode('|',$fileTypes);

		if (!in_array($fileExtension,$typesArray)) {
			$resultArray = array("status"=>"fail","error"=> "File type must be JPG or PNG.");
			$resultArray = json_encode($resultArray);
			echo $resultArray;
			die;

		}
		else {
			// swfuploader setse file type as application/octet-stream,
			// here update the type to the above matched type
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $_FILES['Filedata']['tmp_name']);

			$_FILES['Filedata']['type'] =$mime;
			//	$_FILES['Filedata']['type'] ='image/jpeg';
		}
	}

	## check for valid size
	/*list($width, $height, $type, $attr) = getimagesize($_FILES ['image']['tmp_name']);
	if ($width != 400 || $height != 300) {
	$resultArray = array("status"=>"fail","error"=> "Image size must be 400x300 pixels.");
	$resultArray = json_encode($resultArray);
	echo $resultArray;
	die;
	}
	*/


	// default max sizes
	$width ='800';
	$height ='800';

	// set the destination folder
	$uploadPath = PUBLIC_PATH.DS."images/photos/";
	$shortPath = "/images/photos/";

	$caption = $_POST['caption'];
	$link_category_id = $_POST['link_category_id'];


	$createDirSuccess = true;
	if (!file_exists($uploadPath)) {
		if(!mkdir($uploadPath)) {
			$createDirSuccess=false;
		}
	}

	if ($createDirSuccess != true) {
		$resultArray = array("status"=>"fail","error"=> "Could not create directory. $uploadPath");
		$resultArray = json_encode($resultArray);
		echo $resultArray;
		die;
	}

	@$handle = new Image (  $_FILES['Filedata'], $uploadPath, $width, $height, '1200K',false );

	if ($handle->error) {
		$resultArray = array("status"=>"fail","error"=> $handle->error);
		$resultArray = json_encode($resultArray);
		echo $resultArray;
		die;
	}

	$handleResult = $handle && $handle->file_dst_name ;
	if( $handleResult){
		$imgData = array("image_path"=>$shortPath.$handle->file_dst_name);

		$photo = new Photo();
		$photo->id = $id;

		$photo->caption = $caption;
		$photo->link_brand_id = 0;
		$photo->link_category_id = $link_category_id;
		$photo->source = $imgData['image_path'];

		$isUploadSuccessful = $photo->save();

		if (!$isUploadSuccessful) {
			$resultArray = array("status"=>"fail","error"=> "Error updating database.");
			$resultArray = json_encode($resultArray);
			die;
		}
		$resultArray = array("status"=>"success");
		$resultArray = json_encode($resultArray);
		echo $resultArray;
	}
	else {
		$resultArray = array("status"=>"fail","error"=> $handle->error);
		$resultArray = json_encode($resultArray);
		echo $resultArray;
	}
}
else {
	echo '{ "status": "fail", "error":"no file" }';
}
?>