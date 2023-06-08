<?php
require_once('api-adodb/config.php');
require_once(LIB_PATH.DS.'htmlpurifier'.DS.'HTMLPurifier.auto.php');
function pageIs($pageName=""){

}


/**
* Hash the name of the table and prepend it with h9c just to identify that this table has been hashed
*
* @param mixed $tableName
*/
function hashTable($tableName){
	if(!HASH_TABLE_NAME){
		return $tableName;
	}
	if(strpos($tableName,'h9c') === 0){
		return $tableName;
	}else{
		$c = strrev($tableName);
		$c = base64_encode($c);

		return 'h9c'.$c;
	}
}

function printSitemapByTableName($tableName,$options){



	$menu = TableTraversal::createMenu($tableName,$options);
	drawSiteMapMenu($menu);

}

/**
* Unhash the table name back to its original name in the database
*
* @param mixed $hash
*/
function unhashTable($hash){

	if(!HASH_TABLE_NAME){
		return $hash;
	}

	if(strpos($hash,'h9c') === 0){
		$hash = substr($hash,3,strlen($hash));
		$c = base64_decode($hash);
		$d = strrev($c);

		return $d;

	}else{
		return $hash;
	}
}

function __autoload($class_name) {
	//Editing the autoload to support the HTML Purifier
	if (HTMLPurifier_Bootstrap::autoload($class_name)) return true;
	$class_name = strtolower($class_name).".class";
	$path = LIB_PATH.DS."{$class_name}.php";
	if(file_exists($path)) {
		require_once($path);
	} else {
		die("The file {$class_name}.php could not be found.");
	}
}

/**
* Purify the HTML and return the sanitized value
*
* @param mixed $value
* @return Purified
*/
function htmlPurify($value){
	$config = HTMLPurifier_Config::createDefault();
	$purifier = new HTMLPurifier($config);
	$clean_html = $purifier->purify($value);

	return $clean_html;
}

/**
* Convenience wrapper for htmlspecialchars()
*
* @param mixed $value
* @param mixed $quoteStyle
* @param mixed $charset
* @param mixed $doubleEncode
*/
function h($value,$quoteStyle=ENT_QUOTES,$charset = null,$doubleEncode = null){
	return htmlspecialchars($value,$quoteStyle,$charset,$doubleEncode);
}

/**
* Return URL-Friendly string slug
* @param string $string
* @return string
*/


function truncateUtf8($string, $max_length) {
	if (mb_strlen($string, 'UTF-8') > $max_length){
		$string = mb_substr($string, 0, $max_length, 'UTF-8');
		$pos = mb_strrpos($string, ' ', false, 'UTF-8');
		if($pos === false) {
			return mb_substr($string, 0, $max_length, 'UTF-8').'…';
		}
		return mb_substr($string, 0, $pos, 'UTF-8').'…';
	}else{
		return $string;
	}
}



/**
* Create a web friendly URL slug from a string.
*
* Although supported, transliteration is discouraged because
*     1) most web browsers support UTF-8 characters in URLs
*     2) transliteration causes a loss of information
*
* @author Sean Murphy <sean@iamseanmurphy.com>
* @copyright Copyright 2012 Sean Murphy. All rights reserved.
* @license http://creativecommons.org/publicdomain/zero/1.0/
*
* @param string $str
* @param array $options
* @return string
*/
function seoUrl($str, $options = array()) {
	// Make sure string is in UTF-8 and strip invalid UTF-8 characters
	$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

	$defaults = array(
		'delimiter' => '-',
		'limit' => null,
		'lowercase' => true,
		'replacements' => array(),
		'transliterate' => false,
	);

	// Merge options
	$options = array_merge($defaults, $options);

	$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
		'ß' => 'ss',
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
		'ÿ' => 'y',

		// Latin symbols
		'©' => '(c)',

		// Greek
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y',
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

		// Turkish
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',

		// Russian
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',

		// Ukrainian
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

		// Czech
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
		'Ž' => 'Z',
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z',

		// Polish
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
		'Ż' => 'Z',
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',

		// Latvian
		'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
		'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
		'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
		'š' => 's', 'ū' => 'u', 'ž' => 'z'
	);

	// Make custom replacements
	$str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

	// Transliterate characters to ASCII
	if ($options['transliterate']) {
		$str = str_replace(array_keys($char_map), $char_map, $str);
	}

	// Replace non-alphanumeric characters with our delimiter
	$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

	// Remove duplicate delimiters
	$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

	// Truncate slug to max. characters
	$str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

	// Remove delimiter from ends
	$str = trim($str, $options['delimiter']);

	return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
}


function Slug($string)
{
	return substr((trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-')),0,255);
}
//Function that manages redirections
function redirect_to( $location = NULL ) {

	if ($location != NULL) {
		$location = pageLink($location);
		header("Location: {$location}");
		exit;
	}
}


/**
* Returns the thumbnail image link of given youtube video id
*
* @param string $youtube_code
*/
function youtubeThumb($youtube_code){
	$img = "http://img.youtube.com/vi/{$youtube_code}/default.jpg";
	return $img;
}



/**
* Returns the thumbnail image link of given image, cropped or not
*
* @param string $image
* @param int $width
* @param int $height
* @param bool $crop
*/
function thumbnailLink($image,$width=100,$height=100,$crop=true){
	$width = (int)$width;
	$height = (int)$height;
	$file = IMAGES_PATH.DS.$image;

	//Strip whitespace from the end of the path
	$image = rtrim($image);

	$md5 = md5($image);

	$cropStr = '';
	if ($crop) {
		$cropStr = '&amp;cropratio='.$width.':'.$height;
	}


	//    return PUBLIC_HTML_SITE.DS.'images/thumbs/image.php/image_'.$md5.'.jpg?width='.$width.'&amp;height='.$height.$cropStr.'&amp;image='.WEB_ROOT.DS.$image;
	//!is_dir($file) to verify that is not empty
	if(!is_dir($file) && file_exists($file)){
		return PUBLIC_HTML_SITE.DS.'images/thumbs/image.php/image_'.$md5.'.jpg?width='.$width.'&height='.$height.$cropStr.'&image='.WEB_ROOT.DS.$image;
	} else {

		$image = urlencode('tablesl/images/not_found.jpg');
		return PUBLIC_HTML_SITE.DS.'images/thumbs/image.php/image_'.$md5.'.jpg?width='.$width.'&amp;height='.$height.'&amp;'.$cropStr .'&amp;image='.WEB_ROOT.DS.$image;
	}
}

/**
* Uses TimThumb to resize images
*
* @param string $image
* @param int $width
* @param int $height
* @param array $options = array ('zc' => 1, '
* zc = 0 = resize to fid dimensions without croping
* zc = 1 crop and fit best dimensions
* zc = 2 Resize proportionally to fit entire image into specified dimensions, and add borders if required
* zc = 3 Resize proportionally adjusting size of scaled image so there are no borders gaps
*/
function TimThumbnailLink($image,$width=100,$height=100, $options = array () ){
	$width = (int)$width;
	$height = (int)$height;
	$file = PUBLIC_PATH.DS.$image;
	$image = ($image);
	$q = 80;

	$cropStr = '';
	if (!empty($options) ) {
		foreach($options as $key => $value){
			$cropStr .= '&amp;'.$key.'='.$value;
		}

	}

	//!is_dir($file) to verify that is not empty
	if(!is_dir($file) && file_exists($file)){
		return PUBLIC_HTML_SITE.DS.'images/timthumb/timthumb.php?w='.$width.'&amp;h='.$height.$cropStr.'&amp;src='.HTML_ROOT.DS.$image;
	} else {

		$image = urlencode('tablesl/images/not_found.jpg');
		return PUBLIC_HTML_SITE.DS.'images/timthumb/timthumb.php?w='.$width.'&amp;h='.$height.'&amp;'.$cropStr .'&amp;src='.HTML_ROOT.DS.$image;
	}
}

function formatDate($date){
	return date("d F, Y", strtotime($date));
}
function field_name($field){
	return ucwords(str_replace('_',' ',$field));
}
function isSuccess($message){
	if(strpos($message,'@success@') !== false){
		return true;
	}
	return false;
}

/**
* Wrapper function for parse_ini_string . If method exists, uses it, otherwise uses an alternative definition
*
* @param mixed $parameters
*/
function parse_ini_string_1($parameters){

	if(function_exists('parse_ini_string')){
		return parse_ini_string($parameters);
	}

	$parameters =  str_replace("\n", ",", $parameters);
	$a = explode(",",$parameters);
	$result = array ();
	foreach($a as $value){
		$s = explode("=",$value);
		$result[$s[0]] = $s[1];
	}

	return $result;
}

//Function that returns the link
function pageLink($pageName=""){

	//return $pageName;
	if(strpos($pageName,'showDatabase.php') !== false){
		return $pageName;
	}
	$link = ADMIN_PATH_HTML.DS;
	parse_str( parse_url( $pageName, PHP_URL_QUERY ), $link_params );
	if(empty($link_params)){
		//If no params exist
		$link .= $pageName;
	}else{


		if(!REWRITE_ENABLED){

			$a = explode(".php",$pageName);

			$link .= $a[0].'.php?';

			if(!empty($link_params['table'])){
				$link .= '&table='.hashTable($link_params['table']);
				unset($link_params['table']);
				if(!empty($link_params['id'])){
					$link .=  '&id='.$link_params['id'];
					unset($link_params['id']);
				}
			}

			foreach($link_params as $index => $value){
				$link .= '&'.$index.'='.$value;
			}


		}else{
			//If params (table,id,action) exist
			if(strpos($pageName,'generate.php') !== false){
				$link .= 'generate'.DS;
			}elseif(strpos($pageName,'list.php') !== false){
				$link .= 'list'.DS;
			}
			if(!empty($link_params['table'])){

				$temp = array ();

				$link .= $link_params['table'].'.html?';
				unset($link_params['table']);

				if(!empty($link_params)){
					foreach($link_params as $index => $value){
						$temp[] = urlencode($index).'='.urlencode($value);
					}


					$link .=  join("&",$temp);
				}

			}
		}
	}

	return $link;
}


/**
* Returns the display result
*
* @param mixed $tableName Name of the table
* @param mixed $displayValue The value you want to display, text, link, image path
*/
function displayInSitemap($tableName, $displayValue){
	$displayField = TableTraversal::getFieldToDisplay($tableName);
	$table = new Table($tableName);
	$fieldType = $table->getFieldType($displayField);


	switch($fieldType){

		case 'photo_upload':
			return  '<div class="thumbnail-item">
			<img src="'.thumbnailLink($displayValue,50,50).'" class="thumbnail"/>
			<div class="tooltip">
			<img src="'.thumbnailLink($displayValue,330,185).'" alt="" width="330" height="185" />
			<span class="overlay"></span>
			</div>
			</div>';
			//                return '<img src="'.thumbnailLink($displayValue,50,50).'" width="50" height="50" />';
			break;

		case 'foreign':

			$foreignTable = $table->getForeignTable($displayField);
			$displayField = TableTraversal::getFieldToDisplay($foreignTable);
			$table = new Table($foreignTable);
			$row = $table->findItemById($displayValue);

			return $row[$displayField];

		default:
			return $displayValue;
			break;
	}

}


function clearDate($date){
	return strftime("%c", strtotime($date))   ;
}

function ShowFileName($filepath)
{
	preg_match('/[^?]*/', $filepath, $matches);
	$string = $matches[0];
	#split the string by the literal dot in the filename
	$pattern = preg_split('/\./', $string, -1, PREG_SPLIT_OFFSET_CAPTURE);
	#get the last dot position
	$lastdot = $pattern[count($pattern)-1][1];
	#now extract the filename using the basename function
	$filename = basename(substr($string, 0, $lastdot-1));
	#return the filename part
	return $filename;
}
function ShowFileExtension($filepath)
{
	preg_match('/[^?]*/', $filepath, $matches);
	$string = $matches[0];

	$pattern = preg_split('/\./', $string, -1, PREG_SPLIT_OFFSET_CAPTURE);

	# check if there is any extension
	if(count($pattern) == 1)
	{
		//echo 'No File Extension Present';
		exit;
	}

	if(count($pattern) > 1)
	{
		$filenamepart = $pattern[count($pattern)-1][0];
		preg_match('/[^?]*/', $filenamepart, $matches);
		return $matches[0];
	}
}
//Function that checks if the provided page is the one selected
function currentPageIs($pageName){
	$currentPage = basename($_SERVER['REQUEST_URI']);

	if(strpos($currentPage,$pageName) !== false){
		return true;
	}  else {
		return false;
	}
}

function randomPassword() {
	$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	$pass = array(); //remember to declare $pass as an array
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < 12; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}
	return implode($pass); //turn the array into a string
}

function validMail($field){
	//filter_var() sanitizes the e-mail
	//address using FILTER_SANITIZE_EMAIL
	$field=filter_var($field, FILTER_SANITIZE_EMAIL);
	//filter_var() validates the e-mail
	//address using FILTER_VALIDATE_EMAIL
	if(filter_var($field, FILTER_VALIDATE_EMAIL))
	{
		return  TRUE;
	}
	else
	{
		return FALSE;
	}

}
//Determines if the current menu item is selected or not
function isMenuItemSelected($tableName){
	if(isset($_GET['table']) &&  $_GET['table'] == $tableName){
		return true;
	}
	return false;
}
function pr($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

function reorderTable($tableArray){
	//Load the predefined language file
	include(LIB_PATH.DS."language.php");

	$newArray = array();
	foreach($predefined as $key => $value){
		if(isset($tableArray[$key])){
			$newArray [] = $key;
		}
	}

	foreach($tableArray as $tableName => $i){
		if(!in_array($tableName,$newArray)){
			$newArray [] = $tableName;
		}
	}
	return array_flip($newArray);
	/*pr($newArray);die;
	$newArray = array();
	foreach ($tableArray AS $table=>$index ){
	if(isset($predefined[$table])){
	$newArray[] = $table;
	}
	}
	echo "<pre>";
	print_r($newArray);
	echo "</pre>";*/
}



//Function that returns the name of the table from the table name in the database
function printTableName($name){
	//Load the predefined language file
	include(LIB_PATH.DS."language.php");
	if(isset($predefined[$name])){
		return $predefined[$name];
	}
	return ucwords(str_replace('_',' ',unhashTable($name)));
}
//Function that overwrites the database name with the one to be generated
function overwriteDBLine($dbName){
	$source=LIB_PATH.DS.'api-adodb/config.php';
	$target='out.txt';
	$searchFor = "defined('DB_NAME')";
	// copy operation
	$sh=fopen($source, 'r');
	$th=fopen($target, 'w');
	while (!feof($sh)) {
		$line=fgets($sh);
		//if (strpos($line, $dbName)!==false) {
		//                return 0;
		//            }
		if (strpos($line, $searchFor)!==false) {
			$line="defined('DB_NAME')   ? null : define('DB_NAME', '".$dbName."');" . PHP_EOL;
		}
		fwrite($th, $line);
	}
	fclose($sh);
	fclose($th);

	// delete old source file
	unlink($source);
	// rename target file to source file
	rename($target, $source);

}

//Function that overwrites the database name with the one to be generated
function overwriteHostedWithLSD($value=""){
	$source=LIB_PATH.DS.'api-adodb/config.php';
	$target='out.txt';
	$searchFor = "defined('HOSTED_WITH_US')";
	if($value =='on'){

		$value = 1;
	} else{
		$value = 0;
	}
	// copy operation
	$sh=fopen($source, 'r');
	$th=fopen($target, 'w');
	while (!feof($sh)) {
		$line=fgets($sh);
		//            if (strpos($line, $dbName)!==false) {
		//                return 0;
		//            }
		if (strpos($line, $searchFor)!==false) {
			$line="defined('HOSTED_WITH_US')   ? null : define('HOSTED_WITH_US', ".$value.");" . PHP_EOL;
		}
		fwrite($th, $line);
	}
	fclose($sh);
	fclose($th);

	// delete old source file
	unlink($source);
	// rename target file to source file
	rename($target, $source);

}

function createTableSQL($database){
	return '
	CREATE TABLE IF NOT EXISTS backend_structure (
	field_type varchar(255) NOT NULL,
	html text NOT NULL,
	common_name text NOT NULL ,
	UNIQUE KEY field_type (field_type)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;

	CREATE TABLE IF NOT EXISTS `social` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`social_network` varchar(100) NOT NULL,
	`url` varchar(255) NOT NULL,
	`icon` varchar(255) NOT NULL ,
	`profile` varchar(255) NOT NULL,
	`country` varchar(255) NOT NULL,
	`active` int(1) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	KEY `social_network` (`social_network`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

	CREATE TABLE IF NOT EXISTS `system` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`table_name` varchar(100) NOT NULL,
	`field_name` varchar(255) NOT NULL,
	`field_type` varchar(255) NOT NULL,
	`foreign_table` varchar(100) NOT NULL,
	`foreign_field` varchar(255) NOT NULL,
	`mandatory` tinyint(1) NOT NULL,
	`active` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
	PRIMARY KEY (`id`),
	KEY `id` (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

	CREATE TABLE IF NOT EXISTS `users` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`username` varchar(30) NOT NULL,
	`password` varchar(60) NOT NULL,
	`email` varchar(200) NOT NULL,
	`active` tinyint(1) NOT NULL,
	`last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`user_level` int(1) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
	';
}


function remove_comments(&$output)
{
	$lines = explode("\n", $output);
	$output = "";

	// try to keep mem. use down
	$linecount = count($lines);

	$in_comment = false;
	for($i = 0; $i < $linecount; $i++)
	{
		if( preg_match("/^\/\*/", preg_quote($lines[$i])) )
		{
			$in_comment = true;
		}

		if( !$in_comment )
		{
			$output .= $lines[$i] . "\n";
		}

		if( preg_match("/\*\/$/", preg_quote($lines[$i])) )
		{
			$in_comment = false;
		}
	}

	unset($lines);
	return $output;
}

//
// remove_remarks will strip the sql comment lines out of an uploaded sql file
//
function remove_remarks($sql)
{
	$lines = explode("\n", $sql);

	// try to keep mem. use down
	$sql = "";

	$linecount = count($lines);
	$output = "";

	for ($i = 0; $i < $linecount; $i++)
	{
		if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
		{
			if (isset($lines[$i][0]) && $lines[$i][0] != "#")
			{
				$output .= $lines[$i] . "\n";
			}
			else
			{
				$output .= "\n";
			}
			// Trading a bit of speed for lower mem. use here.
			$lines[$i] = "";
		}
	}

	return $output;

}

//
// split_sql_file will split an uploaded sql file into single sql statements.
// Note: expects trim() to have already been run on $sql.
//
function split_sql_file($sql, $delimiter)
{
	// Split up our string into "possible" SQL statements.
	$tokens = explode($delimiter, $sql);

	// try to save mem.
	$sql = "";
	$output = array();

	// we don't actually care about the matches preg gives us.
	$matches = array();

	// this is faster than calling count($oktens) every time thru the loop.
	$token_count = count($tokens);
	for ($i = 0; $i < $token_count; $i++)
	{
		// Don't wanna add an empty string as the last thing in the array.
		if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
		{
			// This is the total number of single quotes in the token.
			$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
			// Counts single quotes that are preceded by an odd number of backslashes,
			// which means they're escaped quotes.
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

			$unescaped_quotes = $total_quotes - $escaped_quotes;

			// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
			if (($unescaped_quotes % 2) == 0)
			{
				// It's a complete sql statement.
				$output[] = $tokens[$i];
				// save memory.
				$tokens[$i] = "";
			}
			else
			{
				// incomplete sql statement. keep adding tokens until we have a complete one.
				// $temp will hold what we have so far.
				$temp = $tokens[$i] . $delimiter;
				// save memory..
				$tokens[$i] = "";

				// Do we have a complete statement yet?
				$complete_stmt = false;

				for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
				{
					// This is the total number of single quotes in the token.
					$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
					// Counts single quotes that are preceded by an odd number of backslashes,
					// which means they're escaped quotes.
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

					$unescaped_quotes = $total_quotes - $escaped_quotes;

					if (($unescaped_quotes % 2) == 1)
					{
						// odd number of unescaped quotes. In combination with the previous incomplete
						// statement(s), we now have a complete statement. (2 odds always make an even)
						$output[] = $temp . $tokens[$j];

						// save memory.
						$tokens[$j] = "";
						$temp = "";

						// exit the loop.
						$complete_stmt = true;
						// make sure the outer loop continues at the right point.
						$i = $j;
					}
					else
					{
						// even number of unescaped quotes. We still don't have a complete statement.
						// (1 odd and 1 even always make an odd)
						$temp .= $tokens[$j] . $delimiter;
						// save memory.
						$tokens[$j] = "";
					}

				} // for..
			} // else
		}
	}

	return $output;
}

function isPostEmpty(){
	$postValues = $_POST;
	unset($postValues['table']);
	foreach($postValues as $value){
		if(!empty($value)){
			return false;
		}
	}

	if(!empty($_FILES)){
		return false;
	}

	return true;
}







/**** functions from h.module *********/


# May 20, 2011
# LIVE Admin functions


# June 15, 2009
# outputs a gd resized image... its so old now May 10 2011
function gd_image($image,$att){
	//global HTML_SITE;
	$gd_att= HTML_IMAGE."/image.php/".$image."?".$att."&image=".HTML_IMAGE.$image;
	return '<img src="'.$gd_att.'">';
}



# Feb 10, 2008
# Shows maximum number of characers in a string, and adds "..." to trancated string
function draw_sep($width='100%',$height='1'){
	return "<img src=\"images/pix.gif\" width=\"$width\" height=\"$height\" border=0>";
}
// $key, $value
function myvar(){
	$key=func_get_arg(0);
	switch(func_num_args()):
	case 2:
		//saves a variable in the database
		$value=func_get_arg(1);
		if ($value==NULL) {
			$strSQL="delete from vars where var_name='".sqlencode($key)."'";
		} else {
			$strSQL="select * from vars where var_name='".sqlencode($key)."'";
			$objRS=mysql_query($strSQL);
			if ($row=mysql_fetch_object($objRS)){
				$strSQL="update vars set var_value='".sqlencode($value)."' where var_name='".sqlencode($key)."'";
			} else {
				$strSQL="insert into vars set var_name='".sqlencode($key)."', var_value='".sqlencode($value)."'";
			}
		}
		return mysql_query($strSQL);
		break;
	case 1:
		//retrieve a variable from database
		$strSQL="select * from vars where var_name='".sqlencode($key)."'";
		$objRS=mysql_query($strSQL);
		if ($row=mysql_fetch_object($objRS)) return $row->var_value;
		break;
	default:
		trigger_error("<b>myvar():</b> Wrong number of arguments.", E_USER_ERROR);
		return false;
		endswitch;

}

# Mars 10, 2008
# Shows maximum number of characers in a string, and adds "..." to trancated string
function humandate( $d ){
	if ( $d=="" ) return;
	$hd=split('-',$d);
	return date("D, d M Y",mktime(0,0,0,$hd[1],$hd[2],$hd[0]));
}


function rmhtml($string){
	$temp=preg_replace("/\<br\>/", "\r\n", $string);
	return preg_replace("/\<[^>]+\>/", "", $temp);
}


# Feb 10, 2004
# Shows maximum number of characers in a string, and adds "..." to trancated string
function showmax($string,$len){
	$ret=substr($string,0,$len);
	if (strlen($string)>$len) $ret .= "...";
	return $ret;
}


#show max words
function showmaxwords($string,$len){
	$string_ar=explode(" ",$string);
	for($i=0;$i<$len;$i++)
		$ret.=$string_ar[$i]." ";
	if (count($string_ar)>$len) $ret .= "...";
	return $ret;
}


# July 25, 2003 n1
function isid($string){
	if (!$string) return false;
	for ($i=0;$i<strlen($string);$i++){
		$temp=ord(substr($string,$i,1));
		if ($temp<48 || $temp>57) return false;
	}
	return true;
}

# July 25, 2003 n1 -
function getfield($id,$field,$table){
	if (!isid($id)) $id=0;
	$strSQL="select $field as thename from $table where id=$id";
	$objRS=mysql_query($strSQL);
	if ($row=mysql_fetch_object($objRS)) return $row->thename; else return "N/A";
}

# January 16, 2004
# gets fields from (table,condition,field1,...)
function getfields(){

	$table=func_get_arg(0);
	$condition=func_get_arg(1);
	for ($i=2;$i<func_num_args();$i++) $fields.=($fields==""?'':',').func_get_arg($i);

	$strSQL="select $fields from $table where $condition";
	$objRS=mysql_query($strSQL);
	if (!($row=mysql_fetch_object($objRS))) for ($i=2;$i<func_num_args();$i++) $row->{func_get_arg($i)}='N/A';

	return $row;
}


# July 25, 2003 n1
function sqlencode($strng){
	return mysql_escape_string($strng);
	// return addslashes($strng);
}

# July 25, 2003 n1
# - Needs: EMAIL_FROM
function sendmail($to,$subject,$message){

	$site = new Site();

	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "From:".$site->contact_email."\n";
	mail($to, $subject, $message, $headers);
}

# July 25, 2003 n1
function isurl($strng){
	return preg_match('/^http:\/\/[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/i',$strng);
}

# July 25, 2003 n1
function isemail($strng){
	$strng = trim($strng);
	if(empty($strng)){
		return false;
	}
	return preg_match('/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/i',$strng);
}

# Aug 9, 2003 n1
function textencode($strng){
	$temp=htmlspecialchars($strng);
	return $temp;
}


# Aug 13, 2003 n2
function trime($strng){
	return stripslashes(trim($strng));
}

# July 25, 2003 n1
function make_seed(){
	list($usec, $sec) = explode(' ', microtime());
	return (float) $sec + ((float) $usec * 100000);
}



#page pagination left and right

function dispPagesF ($total,$PageSize,$p,$Param){
	if ($total>0){
		if (!is_numeric($p) || $p<1) $p=1;
		$NumOfPages=ceil($total/$PageSize);
		if ($NumOfPages>1){



			if ($p<$NumOfPages) echo "<a href='?p=",$p+1,"&$Param'><img style='border:0px;' src='images/next.png'/></a>";
		}
	}
}

function dispPagesB ($total,$PageSize,$p,$Param){
	if ($total>0){
		if (!is_numeric($p) || $p<1) $p=1;
		$NumOfPages=ceil($total/$PageSize);
		if ($NumOfPages>1){

			if ($p>1) echo "<a href='?p=",$p-1,"&$Param'><img style='border:0px;' src='images/back.png'/></a>";


		}
	}
}

#page pagination back front and numbers

function dispPages ($total,$PageSize,$p,$Param){
	if ($total>0){
		if (!is_numeric($p) || $p<1) $p=1;
		$NumOfPages=ceil($total/$PageSize);
		if ($NumOfPages>1){
			if ($p>1) echo "<a href='?p=",$p-1,"&$Param'>Back</a>";
			$beforetheend=$NumOfPages-2;
			$beforethep=$p-2;
			$afterthep=$p+2;
			$shownone=false;
			$showntwo=false;
			for ($i=1;$i<=$NumOfPages;$i++){
				if($i<=3){
					if ($i!=intval($p))
						echo " <a href='?p=$i&$Param'>$i</a> ";
					else
						echo " <b>[$i]</b> ";
				}
				elseif($i>=$beforetheend){
					if ($i!=intval($p))
						echo " <a href='?p=$i&$Param'>$i</a> ";
					else
						echo " <b>[$i]</b> ";
				}
				elseif($i>=$beforethep and $i<=$afterthep){
					if ($i!=intval($p))
						echo " <a href='?p=$i&$Param'>$i</a> ";
					else
						echo " <b>[$i]</b> ";
				}
				elseif($i<$p and !$shownone){
					$shownone=true;
					echo "...";
				}
				elseif($i>$p and !$showntwo){
					$showntwo=true;
					echo "...";
				}
			}
			if ($p<$NumOfPages) echo "<a href='?p=",$p+1,"&$Param'>Next</a>";
		}
	}
}



function dispPagesAll ($total,$PageSize,$p,$Param){
	if ($total>0){
		if (!is_numeric($p) || $p<1) $p=1;
		$NumOfPages=ceil($total/$PageSize);
		for ($i=1;$i<=$NumOfPages;$i++){
			if($i<=3){
				if ($i!=intval($p))
					echo " <a href='?p=$i&$Param'>$i</a> ";
				else
					echo " <b> ($i) </b> ";
			}
			elseif($i>=$beforetheend){
				if ($i!=intval($p))
					echo " <a href='?p=$i&$Param'>$i</a> ";
				else
					echo " <b> ($i) </b> ";
			}

		}
	}
}




function makePages ($SQL,$PageSize,$p){
	if (!is_numeric($p) || $p==0) $p=1; else $p=intval(abs($p));
	$PageStart=($p-1)*($PageSize);

	$SQL=$SQL." limit $PageStart,$PageSize";
	return $SQL;
}





# h.upload.php

# July 25, 2003 n1
# Splits a file into basic name and extension
function fname_split($file){
	if (strstr($file,'.')){
		preg_match('/(^.+)\.(.*$)/',$file,$matches);
		list(,$basic_name,$ext_name)=$matches;
	} else {
		$basic_name=$file;
	}
	$basic_name=preg_replace('/(\[\d+\])+$/','',$basic_name);
	return array($basic_name,$ext_name);
}

# July 25, 2003 n1
# Creates the field for edit-upload file
// add only upload
function file_field($myvar,$path,$maxlength){
	global $$myvar;
	if ($$myvar!='') echo "";
	echo "<input type=\"text\" name=\"$myvar\" value=\"",textencode($$myvar),"\" maxlength=\"$maxlength\">\n";
	echo "<input type=\"hidden\" name=\"$myvar","old\" value=\"",textencode($$myvar),"\">\n";
	echo "<input type=\"file\" name=\"$myvar","file\">\n";
}

function file_field_cust($myvar,$path,$maxlength){
	global $$myvar;
	if ($$myvar!='') echo "";
	echo "<input class=\"cust\" type=\"text\" name=\"$myvar\" value=\"",textencode($$myvar),"\" maxlength=\"$maxlength\">\n";
	echo "<input class=\"cust\" type=\"hidden\" name=\"$myvar","old\" value=\"",textencode($$myvar),"\">\n";
	echo "<input class=\"cust\" type=\"file\" name=\"$myvar","file\">\n";
}

# Sep 3, 2003 n1
# uploads a file
# - Needs: fname_split
// add delete file if field is emptied
function file_upload ($myvar,$path,&$status,$max_size=''){
	global $_FILES,$_POST;
	$old_name=$_POST[$myvar.'old'];
	$file_name=$_POST[$myvar];
	$real_name=$_FILES[$myvar.'file']['name'];
	$temp_name=$_FILES[$myvar.'file']['tmp_name'];
	$file_size=$_FILES[$myvar.'file']['size'];
	if (!$real_name){
		if ($file_name!=$old_name)
			if (file_exists($path.$old_name) and !file_exists($path.$file_name) and $file_name!='')
				rename($path.$old_name,$path.$file_name);
			return false;
	} elseif (!is_uploaded_file($temp_name)){
		$status.="File \"$real_name\" is not uploaded!<br>";
		return false;
	} elseif ($max_size !='' and $file_size>$max_size){
		$status.="File \"$real_name ($file_size bytes)\" is larger than the maximum allowed of $max_size bytes.<br>";
		return false;
	} else {
		if (file_exists($path.$old_name)) @unlink($path.$old_name);
		$destination_file=str_replace(' ','_',$real_name);
		list($basic_name,$ext_name)=fname_split($destination_file);
		while (file_exists($path.$destination_file)) $destination_file=$basic_name.'['.++$i.']'.($ext_name!=''?".$ext_name":'');
		$result['name']=$destination_file;
		$result['size']=$file_size;
		$result['ext_name']=$ext_name;
		if (!move_uploaded_file($temp_name,$path.$destination_file)){
			$status.="Error in moving the temp file \"$temp_name\" of \"$real_name ($file_size bytes)\"";
			return false;
		}
		return $result;
	}
}



// send email with attachment
/*
* example:
*
$my_file = "somefile.zip";
$my_path = $_SERVER['DOCUMENT_ROOT']."/your_path_here/";
$my_name = "Olaf Lederer";
$my_mail = "my@mail.com";
$my_replyto = "my_reply_to@mail.net";
$my_subject = "This is a mail with attachment.";
$my_message = "Hallo,\r\ndo you like this script? I hope it will help.\r\n\r\ngr. Olaf";
mail_attachment($my_file, $my_path, "recipient@mail.org", $my_mail, $my_name, $my_replyto, $my_subject, $my_message);

*/
function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
	$file = $path.$filename;
	$file_size = filesize($file);
	$handle = fopen($file, "r");
	$content = fread($handle, $file_size);
	fclose($handle);
	$content = chunk_split(base64_encode($content));
	$uid = md5(uniqid(time()));
	$name = basename($file);
	$header = "From: ".$from_name." <".$from_mail.">\r\n";
	$header .= "Reply-To: ".$replyto."\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
	$header .= "This is a multi-part message in MIME format.\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
	$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$header .= $message."\r\n\r\n";
	$header .= "--".$uid."\r\n";
	$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
	$header .= "Content-Transfer-Encoding: base64\r\n";
	$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
	$header .= $content."\r\n\r\n";
	$header .= "--".$uid."--";
	if (mail($mailto, $subject, "", $header)) {
		return true;
	} else {
		return false;
	}
}


/**
* Function that checks whether or not a string contains arabic characters
* @param    string      $str      String that needs to be checked
* @return   bool                  True if it contains arabic, false if it doesn't
**/
function is_arabic($str) {
	$match = preg_match('~\p{Arabic}~u', $str);
	return $match;
}


function emailHtml($to = 'joegabdelsater.com',$subject = 'New Report',$html){

	$headers = "From: " . strip_tags($to) . "\r\n";
	$headers .= "Reply-To: ". strip_tags($to) . "\r\n";
	//    $headers .= "CC: susan@example.com\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	$msg .= "<html><body>";
	$msg .= $html;
	$msg .= "</body></html>";

	mail($to, $subject, $msg, $headers);
}

// check if a link has http:// or not, and generate it
function generateProperLink($urlStr){
	$parsed = parse_url($urlStr);
	if (empty($parsed['scheme'])) {
		$urlStr = 'http://' . ltrim($urlStr, '/');
	}
	return $urlStr ;
}

function getImgTagsFromText($searchString) {
	$images = array();
	// only first image
	//preg_match('/ < img.+ src = [\'"](?P< src >.+)[\'"].*>/i', $searchString, $image);

	//print out the image
	//echo $image['src'];

	// and now we print out all the images
	preg_match_all('/< img.+ src = [\'"](?P< src >.+)[\'"].*>/i', $searchString, $images);

	// lets see the images array
	return $images;

}


function crawlerDetect($USER_AGENT){
	$crawlers = array(
		'Google' => 'Google',
		'MSN' => 'msnbot',
		'Rambler' => 'Rambler',
		'Yahoo' => 'Yahoo',
		'AbachoBOT' => 'AbachoBOT',
		'accoona' => 'Accoona',
		'AcoiRobot' => 'AcoiRobot',
		'ASPSeek' => 'ASPSeek',
		'CrocCrawler' => 'CrocCrawler',
		'Dumbot' => 'Dumbot',
		'FAST-WebCrawler' => 'FAST-WebCrawler',
		'GeonaBot' => 'GeonaBot',
		'Gigabot' => 'Gigabot',
		'Lycos spider' => 'Lycos',
		'MSRBOT' => 'MSRBOT',
		'Altavista robot' => 'Scooter',
		'AltaVista robot' => 'Altavista',
		'ID-Search Bot' => 'IDBot',
		'eStyle Bot' => 'eStyle',
		'Scrubby robot' => 'Scrubby',
		'Facebook' => 'facebookexternalhit',
	);
	// to get crawlers string used in function uncomment it
	// it is better to save it in string than use implode every time
	// global $crawlers
	$crawlers_agents = implode('|',$crawlers);
	if (strpos($crawlers_agents, $USER_AGENT) === false)
		return false;
	else {
		return TRUE;
	}
}


function generateSocialMetaTags($title = "", $description ="", $img = "", $url = ""){
	$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";



	$title = (strip_tags($title));
	$description = (strip_tags($description));

	$html = "
	<meta property='og:type' content='article' />
	<meta property='og:title' content=\"$title\" />
	<meta property='og:url' content='$url' />
	<meta property='og:description' content=\"" . strip_tags($description) ."\" />
	<meta property='og:site_name' content='TheMakeover' />
	<meta property='og:image' content='$img' />
	<meta name='twitter:site' content='@kotexlb' />
	<meta name='twitter:image:src' content='$img' />
	<meta name='twitter:creator' content='@kotexlb' />
	";

	return $html;

}


function getArrayCommonPrefix($array){
	$pl = 0; // common prefix length
	$n = count($array);
	$l = strlen($array[0]);
	while ($pl < $l) {
		$c = $array[0][$pl];
		for ($i=1; $i<$n; $i++) {
			if ($array[$i][$pl] !== $c) break 2;
		}
		$pl++;
	}
	$prefix = substr($array[0], 0, $pl);
	return $prefix;
}


function sendSms($from="LPK Academy",$to="9613431999",$text="test"){

	$service_url = 'http://api.infobip.com/sms/1/text/single';
	// auth
	// Combine the username and password into a string username:password.
	// Encode the resulting string using Base64 encoder.
	$auth = '####'; 

	/*
	multiple recipients:
	"to":[  
	"41793026727",
	"41793026834"
	]
	*/
	if (!empty($to)) {


		$data = array(
			'from'=>$from,
			'to'=>$to,
			'text'=>$text
		);
		$dataJson = json_encode($data);

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $dataJson ,
			CURLOPT_HTTPHEADER => array(
				"accept: application/json",
				"authorization: Basic $auth",
				"content-type: application/json"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			echo $response;
		}


	}
	echo "<pre>data:";
	print_r($data);
	echo "sending $text to $to | <br>result<br>" .$exec_result." | <br>exec info:<br>".print_r($exec_info)." | error<br>" .$error_msg ."<BR><BR>";
}

function convertLebaneseMobileNumberToInternationalFormat($number) {

	$number = (string)$number;
	$number = str_replace(' ','',$number);

	$length = strlen($number);

	if ($length == 8) {
		$firstTwo = substr($number, 0, 2); // get 1st 2 characters
		$remainingSix = substr($number, 2);
		if ($firstTwo == '03') {
			$number = '9613'.$remainingSix ;
		}else {
			$number = '961'.$number;
		}

		return $number;
	}if ($length == 7) {
		$firstTwo = substr($number, 0, 1); // get 1st character
		if ($firstTwo == '3') {
			$number = '961'.$number ;
		}
		return $number;
	}else if ($length == 10 || $length == 11) {
		return $number;
	}
	return false;

}



function emailSendGrid($to = array('roy@xtnd.io'),$subject = 'Email',$text,$alternativeLogo= '', $from = array("no-reply@domain.com"=>'SITENAME'), $replyTo = array('no-reply@DOMAIN.com') ){


	$transport  = Swift_SmtpTransport::newInstance('smtp.sendgrid.net', 587);
	$transport->setUsername("user");
	$transport->setPassword('pass');

	$mailer     = Swift_Mailer::newInstance($transport);

	$message    = new Swift_Message();

	$message->setTo( $to );

	$message->setFrom( $from );

	$message->setReplyTo( $replyTo );

	$message->setSubject( $subject );

	//$message->setBody("%how% are you doing?");

	if (empty($alternativeLogo)) {
		$headerImg = HTML_SITE.'/images/logo.png';
	}else {
		$headerImg = $alternativeLogo;
	}

	$html = '<!DOCTYPE html>
	<html>
	<head>
	</head>
	<body>
	'.$text.'
	</body>
	</html>';

	$message->setBody(strip_tags($html), 'text/plain');
	$message->addPart($html, 'text/html');
	//    $message->setBody($text);
	//    $message->addPart($html, 'text/html');


	$header           = new Smtpapi\Header();
	//$header->addSubstitution("%how%", array("Owl","Test"));

	$message_headers  = $message->getHeaders();
	$message_headers->addTextHeader("x-smtpapi", $header->jsonString());

	try {
		$response = $mailer->send($message);
		return $response;
	} catch(\Swift_TransportException $e) {
		return 'Bad username / password';
	}

}


function time_elapsed_string($datetime, $full = false) {
	$now = new DateTime;
	$ago = new DateTime($datetime);
	$diff = $now->diff($ago);

	$diff->w = floor($diff->d / 7);
	$diff->d -= $diff->w * 7;


	$string = array(
		'y' => 'year',
		'm' => 'month',
		'w' => 'week',
		'd' => 'day',
		'h' => 'hour',
		'i' => 'minute',
		's' => 'second',
	);
	foreach ($string as $k => &$v) {
		if ($diff->$k) {
			$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
		} else {
			unset($string[$k]);
		}
	}

	if (!$full) $string = array_slice($string, 0, 1);
	return $string ? implode(', ', $string) . ' ago' : 'just now';
}