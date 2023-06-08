<?php
/**
* This Class uploads only images supported by the current php installed.
* It makes a unique filename for the uploaded file and check it is not already taken, sample filename: filename_[$i].jpg
* In case of failure upload process, 'uploaded' property set to false and error message stores in 'error' property.
*
* @author Naji Abi Ghosn
* @since 5 June 2008
*/
Class Image{

	/**
	* Uploaded file name
	*
	* @access private
	* @var string
	*/
	public $file_src_name;

	/**
	* Uploaded file name body (i.e. without extension)
	*
	* @access private
	* @var string
	*/
	public $file_src_name_body;

	/**
	* Uploaded file name extension
	*
	* @access private
	* @var string
	*/
	public $file_src_name_ext;

	/**
	* Uploaded file size, in bytes
	*
	* @access private
	* @var double
	*/
	public $file_src_size;

	/**
	* Uploaded file name, including server path
	*
	* @access private
	* @var string
	*/
	public $file_src_pathname;

	/**
	* Uloaded file name temporary copy
	*
	* @access private
	* @var string
	*/
	public $file_src_temp;

	/**
	* Destination file name
	*
	* @access private
	* @var string
	*/
	public $file_dst_path;

	/**
	* Destination file name
	*
	* @access private
	* @var string
	*/
	public $file_dst_name;

	/**
	* Destination file name, including path
	*
	* @access private
	* @var string
	*/
	public $file_dst_pathname;
	public $file_dst_pathname_thumb;

	/**
	* Source image width
	*
	* @access private
	* @var integer
	*/
	public $image_src_x;

	/**
	* Source image height
	*
	* @access private
	* @var integer
	*/
	public $image_src_y;

	/**
	* Type of image (png, gif, jpg or bmp)
	*
	* @access private
	* @var string
	*/
	public $image_src_type;

	/**
	* Holds eventual error message
	*
	* @access public
	* @var string
	*/
	public $error;

	/**
	* Flag set after instanciating the class
	*
	* Indicates if the file has been uploaded properly
	*
	* @access public
	* @var bool
	*/
	public $uploaded;

	public $thumbWidth = '60';
	public $thumbHeight = '55';


	/**
	* check if uploaded image type supported by the current PHP installed
	*
	* @access private
	* @param string $file_src_type type of the uploaded file
	*/
	function validateImageType($file_src_type){

		$image_supported = array();

		if (imagetypes() & IMG_GIF) {
			$image_supported['image/gif'] = 'gif';
		}
		if (imagetypes() & IMG_JPG) {
			$image_supported['image/jpg'] = 'jpg';
			$image_supported['image/jpeg'] = 'jpg';
			$image_supported['image/pjpeg'] = 'jpg';
		}
		if (imagetypes() & IMG_PNG) {
			$image_supported['image/png'] = 'png';
			$image_supported['image/x-png'] = 'png';
		}
		if (imagetypes() & IMG_WBMP) {
			$image_supported['image/bmp'] = 'bmp';
			$image_supported['image/x-ms-bmp'] = 'bmp';
			$image_supported['image/x-windows-bmp'] = 'bmp';
		}

		// if the file is an image, we gather some useful data
		if (array_key_exists($file_src_type, $image_supported)) {
			$this->image_src_type = $image_supported[$file_src_type];
			$info = @getimagesize($this->file_src_pathname);
			if (is_array($info)) {
				$this->image_src_x = $info[0];
				$this->image_src_y = $info[1];
			}
		}else {
			$this->uploaded = false;
			$this->error = 'Can\'t read image source. Not an image?.';
		}

	}

	/**
	* Checks uploaded file size to default value upload_max_filesize from php.ini
	* and to maxfiesize argument if it was setting.
	*
	* @access private
	* @param string $maxfilesize_string the maximum size as string (ex. 4M) for an uploaded file, is set by default to the value upload_max_filesize from php.ini
	* @note $maxfilesize_string cannot be greate than upload_max_filesize set in php.ini
	*/
	function ValidateMaxFileSize($maxfilesize_string = null ){

		$val_string = ini_get('upload_max_filesize');
		$val_bytes = $this->return_bytes($val_string);

		if ( !is_null($maxfilesize_string) || $maxfilesize_string != '' ){
			$maxfilesize_bytes = $this->return_bytes($maxfilesize_string);
			if ($maxfilesize_bytes < $val_bytes){
				$val_bytes = $maxfilesize_bytes;
				$val_string = $maxfilesize_string;
			}
		}

		if ($this->file_src_size > $val_bytes ) {
			$this->uploaded = false;
			$this->error = "file exceed max file size limit ($val_string)";
		}

	}

	/**
	* Converts a string of file size to bytes
	* example: 4M it return 4194304 bytes
	*
	* @param string $val
	* @return double $val in bytes
	*/
	function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

	/**
	*
	* The constructor takes $_FILES['form_field'] array as argument
	* where form_field is the form field name and $server_path the path location of the uploaded file
	*
	* If the file has been uploaded, the constructor will populate all the variables holding the upload information.
	*
	* @access public
	* @param array $file $_FILES['form_field']
	* @param string $server_path $file['type']
	* @param int $width specify max width
	* @param int $height specifc max height
	* @param double $maxfilesize the maximum size in bytes for an uploaded file, is set by default to the value upload_max_filesize from php.ini
	*/
	function Image($file, $server_path, $width=200, $height=300, $maxfilesize = null, $doResize = true, $nameOfStoredFile='') {
		$this->uploaded      = true;
		$this->error         = NULL;
		$this->image_dst_x   = 0;
		$this->image_dst_y   = 0;
		$this->setFileDstPath($server_path);

		if (!$file) {
			$this->uploaded = false;
			$this->error = 'File error. Please try again.';
		}

		//print the error occured during the upload process if detected
		if ($this->uploaded) {
			$this->file_src_error = $file['error'];
			switch($this->file_src_error) {
				case 0:
					// all is OK
					break;
				case 1:
					$this->uploaded = false;
					$this->error = 'File upload error (the uploaded file exceeds the upload_max_filesize directive in php.ini).';
					break;
				case 2:
					$this->uploaded = false;
					$this->error = 'File upload error (the uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form).';
					break;
				case 3:
					$this->uploaded = false;
					$this->error = 'File upload error (the uploaded file was only partially uploaded).';
					break;
				case 4:
					$this->uploaded = false;
					$this->error = 'File upload error (no file was uploaded).';
					break;
				default:
					$this->uploaded = false;
					$this->error = 'File upload error (unknown error code).';
			}
		}

		$this->file_src_size = $file['size'];
		if ($this->uploaded)
			$this->ValidateMaxFileSize($maxfilesize);

		if ($this->uploaded) {
			$this->file_src_pathname   = $file['tmp_name'];
			$this->file_src_name       = $file['name'];

			if ($this->file_src_name == '') {
				$this->uploaded = false;
				$this->error = 'File upload error. Please try again.';
			}
		}

		if ($this->uploaded) {
			$extension = NULL;
			ereg('\.([^\.]*$)', $this->file_src_name, $extension);
			if (is_array($extension)) {
				$this->file_src_name_ext  = strtolower($extension[1]);
				$this->file_src_name_body = substr($this->file_src_name, 0, ((strlen($this->file_src_name) - strlen($this->file_src_name_ext)))-1);
			} else {
				$this->file_src_name_ext  = '';
				$this->file_src_name_body = $this->file_src_name;
			}

			$file_src_type = $file['type'];
			$this->validateImageType($file_src_type);
		}

		//uploads image file and renames regarding the set processing class variables
		if ($this->uploaded) {

			// populate dst variables from src
			if (empty($nameOfStoredFile)) {
				$this->file_dst_name_body  = time()."_".rand(000,999); //set destination file name the time concatenated with a random number of 3 degit
			}
			else {
				$this->file_dst_name_body = $nameOfStoredFile;
			}
			$this->file_dst_name_ext        = $this->file_src_name_ext;
			$this->file_dst_name            = $this->file_dst_name_body.".".$this->file_dst_name_ext;
			$this->file_dst_pathname        = $this->file_dst_path . $this->file_dst_name;
			$this->file_dst_pathname_thumb  = $this->file_dst_path .'thumbs/' . $this->file_dst_name;

			if (!move_uploaded_file($this->file_src_pathname, $this->file_dst_pathname)){

				$this->uploaded = false;
				$this->error = 'receiving directory insufficient permission';
			}
			else {
				if (!$this->resizeImage($width, $height, $this->thumbWidth, $this->thumbHeight, $doResize)) {
					$this->uploaded = false;
					$this->error = 'could not resize image';
				}
			}

		}

	}

	private function resizeImage($width, $height, $thumbWidth, $thumbHeight, $doResize)
	{
		// Get new dimensions
		$filename = $this->file_dst_pathname;
		$filename_thumb = $this->file_dst_pathname_thumb;


		list($width_orig, $height_orig) = getimagesize($filename);
		
		if (!$doResize && ($width_orig > $width || $height_orig > $height)) {
			$this->error = 'file width/height exceeds maximum allowed.';
			return false;
		}


		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig) {
			$width = $height*$ratio_orig;
			$thumbWidth = $thumbHeight*$ratio_orig;
		} else {
			$height = $width/$ratio_orig;
			$thumbHeight = $thumbWidth/$ratio_orig;
		}


		$imageResizer = new SimpleImage();
		$imageResizer->load($filename);
		$imageResizer->resize($width,$height);
		//$imageResizer->resizeToWidth($width);
		$imageResizer->save($filename);

		$imageResizer->resize($thumbWidth,$thumbHeight);
		$imageResizer->save($filename_thumb);

		return true;
		// Resample
		/*$image_p = imagecreatetruecolor($width, $height);
		$image_p_thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

		$image = imagecreatefromjpeg($filename);
		$image_thumb = imagecreatefromjpeg($filename);

		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
		imagecopyresampled($image_p_thumb, $image_thumb, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width_orig, $height_orig);

		if (!imagejpeg($image_p, $filename, 75) || !imagejpeg($image_p_thumb, $filename_thumb, 75)) { //overwrite original image
			return false;
		}
		// Free up memory
		imagedestroy($image);
		return true;
		*/

	}


	/**
	* Prints error occured during the upload process
	* @access public
	*/
	function printError(){
		if (!$this->uploaded)
			echo $this->error;
	}

	/**
	* Set file_dst_path to $server_path end with 1 forward slash '/'
	*
	* @access private
	* @param string $server_path path location to the uploaded file
	*/
	function setFileDstPath($server_path){

		if (!empty($server_path) || !is_null($server_path)) {

			$this->file_dst_path = $server_path;
			if (substr($server_path, -1, 1) != '/')
				$this->file_dst_path = $server_path . '/';

		}else{
			$this->error = 'Path location of the uploaded file can not be empty or null';
			$this->uploaded = false;
		}

	}

}

/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/

class SimpleImage {

	var $image;
	var $image_type;

	function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		if( $this->image_type == IMAGETYPE_JPEG ) {
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->image_type == IMAGETYPE_GIF ) {
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->image_type == IMAGETYPE_PNG ) {
			$this->image = imagecreatefrompng($filename);
		}
	}
	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		}
		if( $permissions != null) {
			chmod($filename,$permissions);
		}
	}
	function output($image_type=IMAGETYPE_JPEG) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image);
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image);
		}
	}
	function getWidth() {
		return imagesx($this->image);
	}
	function getHeight() {
		return imagesy($this->image);
	}
	function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}
	function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height);
	}
	function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100;
		$this->resize($width,$height);
	}
	function resize($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
		$this->image = $new_image;
	}
}
?>