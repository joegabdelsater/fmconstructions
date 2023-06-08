<?php

class FormElement extends Table {

	public $id;
	public $preselectedId;
	public $fieldName;
	public $dbReadyFieldname;
	public $value;

	public $active;
	public $mandatory;
	public $foreignTable;
	public $foreignField;
	public $fieldType;
	public $versionId; //The id of the version you want to rever back to

	function __construct($tableName, $fieldName, $rowId = NULL, $versionId = NULL , $formOptions){
		parent::__construct($tableName);
		if(isset($rowId)){
			$this->id = (int)$rowId;
		}

		if(isset($versionId)){
			$this->versionId = (int)$versionId;
		}

		$this->fieldName = (string) $fieldName;

		if (isset($formOptions['forceValue'])) {
			$this->preselectedId = $formOptions['forceValue'];
		}



		$this->dbReadyFieldname =  $fieldName = $this->adodb->qstr($this->fieldName,self::$magicQuotes);

		$tableName = $this->sanitizeTableNameVariable();
		$sql = "SELECT * FROM `system` WHERE table_name = $tableName AND field_name = $fieldName LIMIT 1;";
		$resultSet = $this->adodb->Execute($sql);
		$row = $resultSet->FetchRow();

		$this->active = $row['active'];
		$this->mandatory = $row['mandatory'];
		$this->foreignTable = $row['foreign_table'];
		$this->foreignField = $row['foreign_field'];
		$this->fieldType = $row['field_type'];

		$this->setValue();

	}


	function setValue(){
		$id = $this->id;
		$fieldType = $this->fieldType;
		$withoutQuotesFieldName = str_replace('\'','',$this->fieldName);


		if(!empty($this->versionId)){
			//If editing an older version 
			$contentHistory = $this->getContentHistoryById($this->versionId);
			$value = "";
			$resultArray = json_decode($contentHistory['data']);

			if(isset($resultArray->$withoutQuotesFieldName)){
				$value = $resultArray->$withoutQuotesFieldName;
			}

			if($fieldType == 'checkbox'){
				$value = !empty($value) ? 'checked' : ''; //Checked is for checkboxes
			}

		}else if(!empty($id)){
			// If editing an already existing entry
			$sql = "SELECT `".$withoutQuotesFieldName."` FROM ".$this->tableName." WHERE id=$id LIMIT 1;";
			$res = $this->adodb->Execute($sql);
			if($res->RecordCount() != 1){
				//Error if the id does not exist.
				die("ID selected does not exist.");
			}
			$resultArray = $res->FetchRow();
			if($fieldType == 'checkbox'){
				$value = !empty($resultArray[$withoutQuotesFieldName]) ? 'checked' : ''; //Checked is for checkboxes
			}else{
				$value = $resultArray[$withoutQuotesFieldName]; //This is the current value of the field_name for the defined id
			}
			/**Check if this is an upload file.
			/*If This is an file upload field, then remove the required attribute from bValidator so that the user can update this row without having to reupload an image
			*/
			if((strpos($fieldType,'_upload') !== false) && (!empty($value))){
				$isRequired = 0;
				$mandatory = ' ';
			}
		}else{
			if(isset($_GET[$this->fieldName])){
				$value = $_GET[$this->fieldName];
			}else{
				$value = "";
			}
		}

		$this->value = $value;
	}

	function isEditing(){
		return isset($this->id) && !empty($this->id) ;
	}

	function isRequired($fieldName = NULL ){
		if($this->isEditing() && (strpos($this->fieldType,'_upload') !== false) && (!empty($this->value))){
			return false;
		}

		return !empty($this->mandatory);
	}

	function getFieldInfo(){
		$mandatory = '';

		$parameters = $this->getFieldParameters($this->fieldName);

		if(isset($parameters['multi'])){
			$multiple = '[]';
			$style = 'style="min-height:350px;"';
			$style = 'style="min-width:350px;"';
		}else{
			$multiple = '';
			$style = '';
		}

		//Append data[tableName]fieldName] to the name of each input
		if(strpos($_SERVER['PHP_SELF'],"generate.php") !== false){
			$fieldName = 'data['.$this->getRawTableName().']'.'['.$this->fieldName.']';
		}else{
			$fieldName = $this->fieldName;
		}

		$name = ' name="'.$fieldName.$multiple.'" '.$style.' ';


		$placeholder = ' placeholder="'.ucwords(str_replace('_',' ',$this->fieldName)).'" ';
		if($this->isRequired() && $this->fieldType != 'textarea'){
			$mandatory = ' required="required" ';
		}
		$fieldInfo = $name . $mandatory . $placeholder;

		return $fieldInfo;
	}

	function displayFormElement(){

		$fieldName = $this->fieldName;

		$originalFieldName = $fieldName;


		$tableName = $this->sanitizeTableNameVariable();

		## Read results from the fetched row ##


		$isRequired = $this->mandatory;

		$foreignKey = !empty( $this->foreignTable ) ? true : false;  //Checks if a foreign key exists

		if($foreignKey){
			$foreign_table = $this->foreignTable;
			$foreign_field = $this->foreignField;
		}

		$fieldType = $this->fieldType;
		$value = h($this->value);

		//Save the all field INFO inside a variable
		$bValidatorAttr = $this->getbValidatorParams($fieldType,$isRequired);
		$fieldInfo = $this->getFieldInfo();
		$fieldInfo .= ' data-bvalidator="'.$bValidatorAttr.'" ';;
		## END FIELD EDIT VALUE RETRIEVAL ##

		## PREDEFINED HTML IN THE TABLE ##
		//Get the predefined HTML from the table
		$html = $this->getFieldHTML($fieldType);
		$originalId = $this->id;

		if(!empty($html)){
			$html = str_replace('@FIELD_INFO@',$fieldInfo,$html); //name="" placeholder="" required=""
			$html = str_replace('@FIELD_VALUE@',$value,$html);//Replace the value
			//Check if the HTML is a select field
			if(strpos($html,'<select') !== false){

				if(strpos($html,'value="'.$value.'"') !== false){
					$html = str_replace('value="'.$value.'"',' value="'.$value.'" selected',$html); //Select the value
				}
			}

			##Image, show the image link ##
			if($fieldType == 'photo_upload'){

				//If this table is a photo gallery and you are NOT editing an existing entry
				if($this->isPhotoGallery() && empty($value) && empty($originalId)){
					$html = '<div class="multipleFileUploadContainer"><input type="file" id="multipleFileUpload" name="'.$originalFieldName.'[]" multiple="true" /> <em><strong>PS:</strong>You can select multiple images</em></div><div id="queue"></div>';




					$html = '</label><div class="multipleFileUploadContainer">
					<script src="'.ADMIN_PATH_HTML.DS.'plugins/tojson/tojson.js" type="text/javascript"></script>
					<script src="'.ADMIN_PATH_HTML.DS.'plugins/fineuploader/jquery.fineuploader-3.6.4.min.js" type="text/javascript"></script>
					<link rel="stylesheet" type="text/css" href="'.ADMIN_PATH_HTML.DS.'plugins/fineuploader/fineuploader-3.6.4.css">';



					$html .= '
					<div class="manual-fine-uploader"></div>

					<script>
					var numberUploaded = 0;
					$(document).ready(function() {
					var manualuploader = new qq.FineUploader({

					element: $(".manual-fine-uploader")[0],

					request: {
					inputName : "'. 'data['.$this->getRawTableName().']'.'['.$this->fieldName.']' .'",
					endpoint: "'.ADMIN_PATH_HTML.DS.'actions/upload.php",
					params: {
					"table" : "'.$this->getRawTableName().'"
					}
					},
					autoUpload: false,
					callbacks : {
					onComplete: function(id, fileName, responseJSON){
					if (responseJSON.success) {
					numberUploaded++;
					console.log(numberUploaded);
					//alert("Upload complete");
					if(numberUploaded == totalFilesToUpload){
					startUpload();
					$("#saveResult").show().removeClass("error").addClass("success").html("Saved!").delay(3000).append(" Redirecting you...").fadeOut("slow", function () {

					window.location = "list.php?table="+"'.$this->getRawTableName().'";

					});

					}
					}
					},
					onError: function(id,name,errorReason){
					numberUploaded = 0;
					$(".qq-upload-status-text").html("<br /><small>"+errorReason+"</small>");
					},

					},

					failedUploadTextDisplay : {
					enableTooltip : true
					},
					text: {
					uploadButton: \'<i class="icon-plus icon-white"></i> Select Files\'
					}
					});

					var totalFilesToUpload = 0;

					$(".triggerFineUploader").click(function() {

					totalFilesToUpload = 0;
					tinyMCE.triggerSave();
					var data = JSON.stringify( $(this).closest("form").serializeObject());
					data = jQuery.parseJSON(data);

					manualuploader.setParams( data );
					manualuploader.uploadStoredFiles();
					$(".qq-upload-list li").not(".qq-upload-fail").each(function(){
					totalFilesToUpload++;
					});

					console.log(totalFilesToUpload);

					});     

					$(this).closest("form").submit(function() {

					totalFilesToUpload = 0;
					tinyMCE.triggerSave();
					var data = JSON.stringify( $(this).closest("form").serializeObject());
					data = jQuery.parseJSON(data);

					manualuploader.setParams( data );
					manualuploader.uploadStoredFiles();
					$(".qq-upload-list li").not(".qq-upload-fail").each(function(){
					totalFilesToUpload++;
					});

					console.log(totalFilesToUpload);

					});


					});

					//$(".tabcontent").bind("DOMNodeInserted DOMNodeRemoved", function(event) {alert("hi")});

					</script>
					</div>';
				}
				if(!empty($value)){
					$html .= '<div class="previewFileContainer">';
					$html .= '<input type="hidden" '.$fieldInfo.' value="'.$value.'" /><br /><br /><div class="label">Current Image: </div><a href="'.PUBLIC_HTML_SITE.DS.$value.'" target="_blank" title="View large image" alt="Image" />
					<img src="'.thumbnailLink($value,246,100).'" alt="Image" width="246" height="100" style="border:thin solid #ccc;padding:1px;" class="previewImage" /></a><br /><div class="label">&nbsp;</div><input type="text" value="'.$value.'" disabled="disabled" style="margin-top:4px;" class="previewImageText" />';
					$html .= '
					<a data-field-name="'.$originalFieldName.'" data-token="'.CSRF::generateToken().'" data-table-name="'.$this->getRawTableName().'" data-row-id="'.$originalId.'" href="#" data-url="'.pageLink('actions/deleteFile.php').'" class="deleteFile">Delete file &raquo;</a>';
					$html .= '</div>';
				}else{
					//$html .= '</label><label><br /><div class="label">&nbsp;</div><input type="text" data-bvalidator="image" placeholder="Optional image url" '.$name.'  style="margin-top:4px;" />';

					$html .= '<script type="text/javascript">
					$(document).on("change", "input[type=text][name='.$originalFieldName.']", function(){
					var value = $(this).val();
					if(value != ""){
					$("input[type=file][name='.$originalFieldName.']").attr("data-bvalidator","image");
					}
					});

					</script>';
				}


			}



			if($fieldType == 'thumbnail' && !empty($originalId)){
				$record = $this->findItemById($originalId);
				$params = $this->getFieldParameters($originalFieldName);
				$image = $record[$params['field_name']];
				$thumb = $record[$originalFieldName];

				$html = "

				";
				if(!is_dir(PUBLIC_PATH.DS.$thumb) && file_exists(PUBLIC_PATH.DS.$thumb)){

					$html .= "<div class='previewFileContainer'><span id='thumbContainer'><img src='".HTML_ROOT.DS.$thumb."' /></span>";
					$html .= '
					<a data-field-name="'.$originalFieldName.'" data-token="'.CSRF::generateToken().'" data-table-name="'.$this->getRawTableName().'" data-row-id="'.$originalId.'" href="#" data-url="'.pageLink('actions/deleteFile.php').'" class="deleteFile">Delete file &raquo;</a></div><br /><br />';

				}else {
					$html .= "<span id='thumbContainer'>No thumb - default will be generated!</span><br /><br />";
				}

				$html .= '

				<a style="cursor:pointer" onclick="$(\'#thumb_gen\').show(500);">Click here to generate a new thumbnail.</a>
				<script type="text/javascript">
				// api needs to be defined globally so it can be accessed from the setTimeout function
				$.globalEval("var jcrop_api;");

				function stopJcrop() {
				jcrop_api.destroy();
				return (false);
				}

				function setCrop()
				{
				// Need to pause a second or two to allow the image to load, otherwise the Jcrop plugin
				// will not update the image size correctly and if you change image size the picture
				// will be stretched.
				// Change the 1000 to however many seconds you need to load the new image.
				setTimeout("$(\'#cropbox\').Jcrop({ onSelect: updateCoords ';
				if(isset($params['thumbnail_preset_ratio'])){
					$html .= ', aspectRatio : '.$params['thumbnail_preset_ratio'].' ';
				}
				$html .= '}, function(){ jcrop_api = this;});",1000);
				}

				$(function(){
				setCrop();
				});

				$(document).ready(function(){

				$(".reloadImageToCrop").click(function(){
				var public_html = $(this).attr("data-public-html");
				var image= $(this).attr("data-image");

				var width=prompt("Please enter the new width","600");
				var height=prompt("Please enter the new height","400");
				if ( ( width!=null && width!="" ) && ( height!=null && height!="")   ) {
				//jcrop_api.setImage(public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image);
				//            $("#cropbox").attr("src",public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image);
				//            $("#src").val(public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image);

				var imagePath = public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image;
				var imagePathRel = "images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image;
				stopJcrop();
				$("#cropbox").attr("src",imagePath);
				$("#src").val(imagePathRel);
				$("#cropbox").removeAttr("style");
				if (height <= 500) {
				$("#cropImageContainer").css("height",height);
				}
				setCrop();
				}
				else {
				alert("You cannot enter 0 as a dimension.");
				}
				});
				});
				</script>
				<div id="thumb_gen" style="display:none">
				<div class="croparea">
				<h4>
				Use the crosshair to select the area you would like to crop and click on the Crop button to save it.<br />
				<!--<a class="reloadImageToCrop" data-public-html="'.PUBLIC_HTML_SITE.'" data-image="'.$image.'"><b>Click here</b></a> to reload the image at a different size.-->
				</h4>
				<div style="width:100%;height:500px;overflow:auto" id="cropImageContainer"><img src="'.PUBLIC_HTML_SITE.DS.$image.'" id="cropbox" /></div>
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y" name="y" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" id="h" name="h" />

				<input type="hidden" id="src" name="src" value="'.$image.'" />

				<input type="hidden" name="table" value="'.$tableName.'" />
				<input type="hidden" name="fieldName" value="'.$originalFieldName.'" />
				<input type="hidden" name="recordId" value="'.$originalId.'" />
				<input type="hidden" name="thumbForFieldName" value="'.$params['field_name'].'" />

				<input type="hidden" name="cropUrl" value="'. ADMIN_PATH_HTML.DS.'actions'.DS.'imageCrop.php' .'" />

				<br />
				<input type="button" value="Crop Image" class="cropSubmit submit round drop_shadow btn btn-large btn-inverse" />
				</div>
				</div>
				';

			}else if($fieldType == 'thumbnail'){
				$html = 'Save the image to generate a thumbnail.';
			}

			if($fieldType == 'time'){
				if(!empty($value)){
					$hour = date('H',strtotime($value));
					$minute = date('i',strtotime($value));
					$second = date('s',strtotime($value));
				}else{
					list($hour,$minute,$second) = explode(":",date("h:i:s"));
				}
				$html = '<select fieldType="time" fieldName="'.$originalFieldName.'" name="'.$originalFieldName.'_cms_hour" style="width:50px;min-width:0;">';
				for($i = 0; $i<= 23; $i++){
					$html .= '<option value="'.$i.'" '.($hour == $i ? 'selected="selected"' : '').'>'.($i<10 ? '0' : '') . $i.'</option>';
				}
				$html .=  '</select>&nbsp;:&nbsp;</label>';

				$html .= '<label><select fieldType="time"  fieldName="'.$originalFieldName.'"  name="'.$originalFieldName.'_cms_minute" style="width:50px;min-width:0;">';
				for($i = 0; $i<= 59; $i++){
					$html .= '<option '.($minute == $i ? 'selected="selected"' : '').'>'.($i<10 ? '0' : '') . $i.'</option>';
				}
				$html .=  '</select>&nbsp;:&nbsp;</label>';

				$html .= '<label><select fieldType="time"  fieldName="'.$originalFieldName.'"  name="'.$originalFieldName.'_cms_seconds" style="width:50px;min-width:0;">';
				for($i = 0; $i<= 59; $i++){
					$html .= '<option '.($second == $i ? 'selected="selected"' : '').' >'.($i<10 ? '0' : '') . $i.'</option>';
				}
				$html .=  '</select><input type="hidden" name="'.$originalFieldName.'" value="'.$hour .':'. $minute .':' . $second.'" />';

				/**$html .= '
				<script>
				$(document).ready(function(){
				$("select").change(function(){

				var hours = $("select[name=\''.$originalFieldName.'_cms_hour\']").val();
				var mins = $("select[name=\''.$originalFieldName.'_cms_minute\']").val();
				var secs = $("select[name=\''.$originalFieldName.'_cms_seconds\']").val();

				$("input[name=\''.$originalFieldName.'\']").val(hours+":"+mins+":"+secs);
				});
				});
				</script>
				';
				**/

			}

			if($fieldType == 'auto_date'){
				$html = "<input type='hidden' value='".date('Y-m-d H:i:s')."'  name=\"{$originalFieldName}\" />";
				$html .= date('Y-m-d');
			}



			if($fieldType == 'pdf_upload'){
				if(!empty($value)){




					$append = $html;
					$html = '<input type="hidden" '.$fieldInfo.' value="'.$value.'" /></div>';
					$html .= '<div class="previewFileContainer">';
					$html .= '



					<a href="'.PUBLIC_HTML_SITE.DS.$value.'" target="_blank" title="View pdf" alt="PDF" />View Uploaded PDF file</a> | 

					';
					$html .= '
					<a data-field-name="'.$originalFieldName.'" data-table-name="'.$this->getRawTableName().'" data-row-id="'.$originalId.'" href="#" data-url="'.pageLink('actions/deleteFile.php').'" class="deleteFile">Delete file &raquo;</a>';
					$html .= '</div>';

					$html .= 'Upload a new file: <br /><label class="label" style="margin:0;">&nbsp;</label>';
					$html .= $append;


				}
			}

			if($fieldType == 'nashra_link'){
				if(!empty($value)){




					$append = $html;
					$html = '<input type="hidden" '.$fieldInfo.' value="'.$value.'" /></div>';


					$html .= 'Upload a zip file: <br /><label class="label" style="margin:0;">&nbsp;</label>';
					$html .= $append;


				}
			}

			if($fieldType == 'mp3_upload'){
				if(!empty($value)){
					$html .= '<br /><br /><div class="label">Current File: </div><a href="'.PUBLIC_HTML_SITE.DS.$value.'" target="_blank" title="Listen" alt="PDF" />'.PUBLIC_HTML_SITE.DS.$value.'</a><br /><div class="label">&nbsp;</div>';
				}
			}


			if($fieldType == 'limited_textarea'){
				$parameters =   $this->getFieldParameters($this->fieldName);
				$maxLength = $parameters['maxlength'];

				$html = str_replace('@MAX_LENGTH_COUNT@',$maxLength,$html);
				$html = str_replace('@MAX_LENGTH_COUNT_ATTRIBUTE@',' data-max-length="'.$maxLength.'"',$html);
			}


			##COlor Picker##
			if($fieldType == 'colorpicker'){

				$html .= '<script src="'.ADMIN_PATH_HTML.DS.'plugins/colorpicker/jscolor.js" type="text/javascript"></script>
				';
			}
			##END COLOR PICKER


			return $html;
		}


		## HTML IF A FOREIGN KEY EXISTS ##
		if($foreignKey){




			/*
			OLD LOGIC

			$html = '<select '.$fieldInfo.'>';
			$sql =  "SELECT  id,$foreign_field FROM `$foreign_table`";
			$resultSet = $this->adodb->Execute($sql);
			$html .= '<option value="" style="color:red">Select</option>';
			while($row = $resultSet->FetchRow()){
			$html .= '<option value="'.$row["id"].'" '.( $row['id'] == $value ? 'selected' : '' ) .'>'.$row[$foreign_field].'</option>';
			}
			$html .= '</select>';
			$html .= '&nbsp;<a href="'.pageLink('generate.php?table='.$foreign_table.'').'">Add a new entry</a>';
			return $html;
			*/
			TableTraversal::resetResult();

			$parameters = $this->getFieldParameters($originalFieldName);
			if(!isset($parameters['tables'])){
				$parameters['tables'] = array ();
			}

			if($fieldType == 'foreign' && isset($parameters['ajax'])){
				// for ajax, force displayed value for editing
				if ($this->preselectedId > 0) {
					$foreignObj = new Table($this->getRawTableName());
					$preselectedItem = $foreignObj -> findItemById($this->preselectedId);

					/*$html = '<input '.$fieldInfo.' class="ajax_foreign_'.$originalFieldName.'" data-table-name="'.$foreign_table.'" data-foreign-key="'.$originalFieldName.'" data-original-table="'.$this->getRawTableName().'"  style="width:500px;"  />'; */
					$html = "<input disabled = 'disabled' value='{$this->preselectedId}'><input type='hidden' $fieldInfo value='{$this->preselectedId}'>";
				}
				else {
					$html = '<input '.$fieldInfo.' class="ajax_foreign_'.$originalFieldName.'" data-table-name="'.$foreign_table.'" data-foreign-key="'.$originalFieldName.'" data-original-table="'.$this->getRawTableName().'"  style="width:500px;"  />';
					$html .= '

					<script type="text/javascript">
					$(".ajax_foreign_'.$originalFieldName.'").ajaxForeignKey();
					</script>
					';
				}

				return $html;
			}

			if ($fieldType == 'foreign' && isset($parameters['single_table'])) {

				$html = '<select '.$fieldInfo;

				if(isset($parameters['multi']) && $parameters['multi'] == '1'){
					$html .= ' multiple="multiple" ';
					$selectedValues = explode(',',$value);
				}
				$html .= '>';

				$html .= '<option value="">Please select...</option>';

				$sql =  "SELECT  * FROM `$foreign_table`";
				$o = new Table();
				$entries = $o->findSql($sql);
				$displayField = reset($o->findSql("SELECT * FROM `table_options` WHERE table_name = '$foreign_table' LIMIT 1;"));

				foreach ($entries  AS $e) {
					$selected = '';


					if(isset($parameters['multi']) && $parameters['multi'] == '1'){
						if (in_array($e['id'],$selectedValues) !== false){
							$selected ='selected="selected"';
						}
					}else {
						if ($e['id'] == $value || $e['id'] == $this->preselectedId) {
							$selected ='selected="selected"';
						}
					}

					$html .= "<option value='{$e['id']}' $selected>{$e[$displayField['display_fields']]}</option>";
				}
				$html .="</select>";
				$html .= '&nbsp;<a class="ajaxGenerate"  data-fieldname="'.$fieldName.'" data-tablename="'.str_replace("'",'',$tableName).'" href="'.pageLink('generate.php?table='.$foreign_table.'').'">Add a new entry</a>';
				return $html;
			}

			if($fieldType == 'habtm_foreign' && isset($parameters['auto_complete_tag']) ){

				$newTable = new Table($foreign_table);
				$list = $newTable->findAll();

				$html = '<select multiple="multiple" '.$fieldInfo.' class="no-select2 auto_complete_tag_'.$originalFieldName.'" >';
				$defaultValues = explode("|",$value);
				$defaultValues = array_map(function($item){
					if(!empty(trim($item))){
						return (int) trim($item);
					}
					},$defaultValues);
				if(!empty($defaultValues) && array_filter($defaultValues,'is_int')){
					foreach ($defaultValues as $defaultValue) {
						$title = $newTable->findItemById($defaultValue)['title'];
						$html .= "<option value='{$defaultValue}' selected=\"selected\">{$title}</option>";
					}
				}
				$html .='</select>';
				$html .= '

				<script type="text/javascript">
				$(document).ready ( function () {
				if ($(".auto_complete_tag_'.$originalFieldName.'").length > 0){

				$(".auto_complete_tag_'.$originalFieldName.'").select2({
				multiple: true,

				data: [';
				$entries = array ();
				$displayField = $newTable->getDisplayField();
				foreach($list as $row){
					$displayFieldValue = $row[$displayField];
					$entries [] = '{id: '.$row['id'].', text: "'.$displayFieldValue.'", ' .
					((in_array($row['id'],$defaultValues)) ? 'selected: true' : '')
					. '  }';
				}

				$html .= join(",",$entries);
				$html .=']
				});
				';

				//                $defaultValues = explode("|",$value);
				//                $defaultValues = array_map(function($item){
				//                    if(!empty(trim($item))){
				//                        return (int) trim($item);
				//                    }
				//                },$defaultValues);
				//                if(!empty($defaultValues) && array_filter($defaultValues,'is_int')){
				//                    $html .= '$(\'.auto_complete_tag_'.$originalFieldName. '\').select2(\'data\',[';
				//                    $entries = array ();
				//                    foreach ($defaultValues as $defaultValue) {
				//                        $title = $newTable->findItemById($defaultValue)['title'];
				//                        $entries [] = "{id:{$defaultValue}, text: '{$title}'}";
				//                    }
				//                    $html .= join(",",$entries);
				//                    $html .= ']);';
				//                }
				$html .='
				}
				});
				</script>
				';

				return $html;
			}

			$html = '<select '.$fieldInfo;
			if(isset($parameters['multi']) && $parameters['multi'] == '1'){
				$html .= ' multiple="multiple" ';
			}
			$html .= '>';
			$html .= '<option value="">Please select...</option>';

			$sql =  "SELECT  * FROM `$foreign_table`";

			$parentStart = $foreign_table;
			//Array containing the parent tables of the starting foreign table
			$parentsTableArray [] = $parentStart;

			while( ($parent = $this->getRootTable($parentStart)) && ($parent != $parentsTableArray[0]) ){
				$parentsTableArray [] = $parent;
				$parentStart = $parent;
			}

			$parentsTableArray = array_reverse($parentsTableArray);



			$menu = TableTraversal::createMenu($parentsTableArray[0], array ('options' => array ('limit' => 0 ) , 'parameters' =>$parameters['tables']));
			TableTraversal::$parentsTableArray = array (); //Reset
			TableTraversal::$visitedRows = array (); //Reset
			TableTraversal::$visited = array (); //Reset
			TableTraversal::$previousIndent = 0; //Reset
			TableTraversal::$foreignTableTo = $foreign_table;
			TableTraversal::$originalFieldName = $originalFieldName;
			TableTraversal::$preselectedId = $this->preselectedId;





			drawSelectMenu($menu, $value , $parentsTableArray);
			$html .= TableTraversal::$result;


			$html .= '</select>';
			$html .= '&nbsp;<a class="ajaxGenerate"  data-fieldname="'.$fieldName.'" data-tablename="'.str_replace("'",'',$tableName).'" href="'.pageLink('generate.php?table='.$foreign_table.'').'">Add a new entry</a>';
			return $html;




		}

		## HTML IF THIS A POSITION FIELD ##
		if($fieldType == 'position'){
			$totalPositions = $this->totalRows(); //Total rows in the given table
			$html = '<select '.$fieldInfo.'>';
			for($i=1;$i<=$totalPositions;$i++){
				$html .= '<option value="'.$i.'" '.( $i == $value ? 'selected' : '' ) .'>'.$i.'</option>';
			}
			$html .= '</select>';
			return $html;
		}
		##ENUM##
		if($fieldType == 'enum'){
			$sql = "SHOW COLUMNS FROM ".$this->tableName." LIKE {$this->dbReadyFieldname}";
			$result = $this->adodb->Execute($sql);
			$row = $result->FetchRow();
			$type = $row['Type'];
			preg_match('/enum\((.*)\)$/', $type, $matches);
			$vals = explode(',', $matches[1]);
			$html = '<select '.$fieldInfo.'>';
			$html .= '<option value="" style="color:red">Select</option>';
			foreach($vals as $enumvalue){
				$enumvalue = str_replace("'","",$enumvalue);
				$html .= '<option value="'.$enumvalue.'" '.( $enumvalue == $value ? 'selected' : '' ) .'>'.$enumvalue.'</option>';
			}
			$html .= '</select>';
			return $html;

		}

		if ($fieldType == 'timestamp') {

			//$html = "<input type='input' value='".date('Y-m-d H:i:s')."'  name=\"{$originalFieldName}\"  disabled='disabled' />";
			$html .= date('Y-m-d H:i:s',$value);
			return $html;

		}

		##IF THE FIELD IS UNKOWN IN THE SYSTEM TABLE, OUTPUT A SIMPLE INPUT TEXT FIELD . THIS CASE IF VERY UNLIKELY ##
		return '<input type="text" '.$fieldInfo.' value="'.$value.'" />';

	}

	function displayFormElementOld(){

		$fieldName = $this->fieldName;

		$originalFieldName = $fieldName;
		$fieldName = $this->adodb->qstr($fieldName,self::$magicQuotes);

		$tableName = $this->sanitizeTableNameVariable();
		$sql = "SELECT * FROM system WHERE table_name = $tableName AND field_name = $fieldName LIMIT 1;";
		$resultSet = $this->adodb->Execute($sql);
		$row = $resultSet->FetchRow();

		## Read results from the fetched row ##
		$name = ' name="'.$originalFieldName.'" ';
		$placeholder = ' placeholder="'.ucwords(str_replace('_',' ',$originalFieldName)).'" ';
		$active = $row['active'] == 1 ? true : false; //Show field : true or false
		$isRequired = $row['mandatory'];
		$mandatory = $row['mandatory'] == 1 ? ' required ' : ''; //Checks if the following field is mandatory
		$foreignKey = !empty($row['foreign_table'] ) ? true : false;  //Checks if a foreign key exists
		if($foreignKey){
			$foreign_table = $row['foreign_table'];
			$foreign_field = $row['foreign_field'];
		}
		$fieldType = $row['field_type'];


		## IF EDITING A FIELD ##
		if(!empty($id)){
			$originalId = $id;
			$id = $this->escape($id);
			$withoutQuotesFieldName = str_replace('\'','',$fieldName);
			$sql = "SELECT `".$withoutQuotesFieldName."` FROM ".$this->tableName." WHERE id=$id LIMIT 1;";
			$res = $this->adodb->Execute($sql);
			if($res->RecordCount() != 1){
				//Error if the id does not exist.
				die("ID selected does not exist.");
			}
			$resultArray = $res->FetchRow();
			if($fieldType == 'checkbox'){
				$value = !empty($resultArray[$withoutQuotesFieldName]) ? 'checked' : ''; //Checked is for checkboxes
			}else{
				$value = $resultArray[$withoutQuotesFieldName]; //This is the current value of the field_name for the defined id
			}
			/**Check if this is an upload file.
			/*If This is an file upload field, then remove the required attribute from bValidator so that the user can update this row without having to reupload an image
			*/
			if((strpos($fieldType,'_upload') !== false) && (!empty($value))){
				$isRequired = 0;
				$mandatory = ' ';
			}
		}else{
			if(isset($_GET[$originalFieldName])){
				$value = $_GET[$originalFieldName];
			}else{
				$value = "";
			}
		}




		$value = h($value);

		//Save the all field INFO inside a variable
		$fieldInfo = $name . $mandatory . $placeholder;
		## END FIELD EDIT VALUE RETRIEVAL ##

		## CHECK IF FIELD IS ACTIVE ##
		//if(!$active){
		//                return "--";   //Do not output if the field is inactive
		//            }




		## PREDEFINED HTML IN THE TABLE ##
		//Get the predefined HTML from the table
		$html = $this->getFieldHTML($fieldType);
		$bValidatorAttr = $this->getbValidatorParams($fieldType,$isRequired);
		$fieldInfo .= ' data-bvalidator="'.$bValidatorAttr.'" ';;
		if(!empty($html)){
			$html = str_replace('@FIELD_INFO@',$fieldInfo,$html); //name="" placeholder="" required=""
			$html = str_replace('@FIELD_VALUE@',$value,$html);//Replace the value
			//Check if the HTML is a select field
			if(strpos($html,'<select') !== false){

				if(strpos($html,'value="'.$value.'"') !== false){
					$html = str_replace('value="'.$value.'"',' value="'.$value.'" selected',$html); //Select the value
				}
			}

			##Image, show the image link ##
			if($fieldType == 'photo_upload'){

				//If this table is a photo gallery and you are NOT editing an existing entry
				if($this->isPhotoGallery() && empty($value) && empty($originalId)){
					$html = '<div class="multipleFileUploadContainer"><input type="file" id="multipleFileUpload" name="'.$originalFieldName.'[]" multiple="true" /> <em><strong>PS:</strong>You can select multiple images</em></div><div id="queue"></div>';




					$html = '</label><div class="multipleFileUploadContainer">
					<script src="'.ADMIN_PATH_HTML.DS.'plugins/tojson/tojson.js" type="text/javascript"></script>
					<script src="'.ADMIN_PATH_HTML.DS.'plugins/fineuploader/jquery.fineuploader-3.6.4.min.js" type="text/javascript"></script>
					<link rel="stylesheet" type="text/css" href="'.ADMIN_PATH_HTML.DS.'plugins/fineuploader/fineuploader-3.6.4.css">';



					$html .= '
					<div class="manual-fine-uploader"></div>

					<script>
					$(document).ready(function() {
					var manualuploader = new qq.FineUploader({
					element: $(".manual-fine-uploader")[0],

					request: {
					inputName : "'.$originalFieldName.'",
					endpoint: "'.ADMIN_PATH_HTML.DS.'actions/upload.php",
					params: {
					"table" : "'.$this->getRawTableName().'"
					}
					},
					autoUpload: false,
					callbacks : {
					onComplete: function(){
					//  alert("Upload complete");
					// window.top.window.stopUpload(1,"'.$this->getRawTableName().'");
					},
					onError: function(id,name,errorReason){
					$(".qq-upload-status-text").html("<br /><small>"+errorReason+"</small>");
					}
					},

					failedUploadTextDisplay : {
					enableTooltip : true
					},
					text: {
					uploadButton: \'<i class="icon-plus icon-white"></i> Select Files\'
					}
					});


					$(".triggerFineUploader").click(function() {
					tinyMCE.triggerSave();
					var data = JSON.stringify( $(this).closest("form").serializeObject());
					data = jQuery.parseJSON(data);

					manualuploader.setParams( data );
					manualuploader.uploadStoredFiles();
					});
					});
					</script>
					</div>';
				}
				if(!empty($value)){
					$html .= '<div class="previewFileContainer">';
					$html .= '<input type="hidden" '.$fieldInfo.' value="'.$value.'" /><br /><br /><div class="label">Current Image: </div><a href="'.PUBLIC_HTML_SITE.DS.$value.'" target="_blank" title="View large image" alt="Image" />
					<img src="'.thumbnailLink($value,246,100).'" alt="Image" width="246" height="100" style="border:thin solid #ccc;padding:1px;" class="previewImage" /></a><br /><div class="label">&nbsp;</div><input type="text" value="'.$value.'" disabled="disabled" style="margin-top:4px;" class="previewImageText" />';
					$html .= '
					<a data-field-name="'.$originalFieldName.'" data-token="'.CSRF::generateToken().'" data-table-name="'.$this->getRawTableName().'" data-row-id="'.$originalId.'" href="#" data-url="'.pageLink('actions/deleteFile.php').'" class="deleteFile">Delete file &raquo;</a>';
					$html .= '</div>';
				}else{
					//$html .= '</label><label><br /><div class="label">&nbsp;</div><input type="text" data-bvalidator="image" placeholder="Optional image url" '.$name.'  style="margin-top:4px;" />';

					$html .= '<script type="text/javascript">
					$("input[type=text][name='.$originalFieldName.']").live("change",function(){
					var value = $(this).val();
					if(value != ""){
					$("input[type=file][name='.$originalFieldName.']").attr("data-bvalidator","image");
					}
					});
					</script>';
				}


			}


			if($fieldType == 'thumbnail' && !empty($originalId)){

				$record = $this->findItemById($originalId);
				$params = $this->getFieldParameters($originalFieldName);
				$image = $record[$params['field_name']];
				$thumb = $record[$originalFieldName];

				$html = "

				";
				if(file_exists(PUBLIC_PATH.DS.$thumb)){
					$html .= "<span id='thumbContainer'><img src='".HTML_ROOT.DS.$thumb."' /></span><br /><br />";
				}else {
					$html .= "<span id='thumbContainer'>No thumb - default will be generated!</span><br /><br />";
				}

				$html .= '

				<a style="cursor:pointer" onclick="$(\'#thumb_gen\').show(500);">Click here to generate a new thumbnail.</a>
				<script type="text/javascript">
				// api needs to be defined globally so it can be accessed from the setTimeout function
				$.globalEval("var jcrop_api;");

				function stopJcrop() {
				jcrop_api.destroy();
				return (false);
				}

				function setCrop()
				{
				// Need to pause a second or two to allow the image to load, otherwise the Jcrop plugin
				// will not update the image size correctly and if you change image size the picture
				// will be stretched.
				// Change the 1000 to however many seconds you need to load the new image.
				setTimeout("$(\'#cropbox\').Jcrop({ onSelect: updateCoords ';
				if(isset($params['thumbnail_preset_ratio'])){
					$html .= ', aspectRatio : '.$params['thumbnail_preset_ratio'].' ';
				}
				$html .= '}, function(){ jcrop_api = this;});",1000);
				}

				$(function(){
				setCrop();
				});

				$(document).ready(function(){

				$(".reloadImageToCrop").click(function(){
				var public_html = $(this).attr("data-public-html");
				var image= $(this).attr("data-image");

				var width=prompt("Please enter the new width","600");
				var height=prompt("Please enter the new height","400");
				if ( ( width!=null && width!="" ) && ( height!=null && height!="")   ) {
				//jcrop_api.setImage(public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image);
				//            $("#cropbox").attr("src",public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image);
				//            $("#src").val(public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image);

				var imagePath = public_html+"/images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image;
				var imagePathRel = "images/thumbs/image.php?width="+width+"&height="+height+"&image=/"+image;
				stopJcrop();
				$("#cropbox").attr("src",imagePath);
				$("#src").val(imagePathRel);
				$("#cropbox").removeAttr("style");
				if (height <= 500) {
				$("#cropImageContainer").css("height",height);
				}
				setCrop();
				}
				else {
				alert("You cannot enter 0 as a dimension.");
				}
				});
				});
				</script>
				<div id="thumb_gen" style="display:none">
				<div class="croparea">
				<h4>
				Use the crosshair to select the area you would like to crop and click on the Crop button to save it.<br />
				<!--<a class="reloadImageToCrop" data-public-html="'.PUBLIC_HTML_SITE.'" data-image="'.$image.'"><b>Click here</b></a> to reload the image at a different size.-->
				</h4>
				<div style="width:100%;height:500px;overflow:auto" id="cropImageContainer"><img src="'.PUBLIC_HTML_SITE.DS.$image.'" id="cropbox" /></div>
				<input type="hidden" id="x" name="x" />
				<input type="hidden" id="y" name="y" />
				<input type="hidden" id="w" name="w" />
				<input type="hidden" id="h" name="h" />

				<input type="hidden" id="src" name="src" value="'.$image.'" />

				<input type="hidden" name="table" value="'.$tableName.'" />
				<input type="hidden" name="fieldName" value="'.$originalFieldName.'" />
				<input type="hidden" name="recordId" value="'.$originalId.'" />
				<input type="hidden" name="thumbForFieldName" value="'.$params['field_name'].'" />

				<input type="hidden" name="cropUrl" value="'. ADMIN_PATH_HTML.DS.'actions'.DS.'imageCrop.php' .'" />

				<br />
				<input type="button" value="Crop Image" class="cropSubmit submit round drop_shadow btn btn-large btn-inverse" />
				</div>
				</div>
				';

			}

			if($fieldType == 'time'){
				if(!empty($value)){
					$hour = date('H',strtotime($value));
					$minute = date('i',strtotime($value));
					$second = date('s',strtotime($value));
				}else{
					list($hour,$minute,$second) = explode(":",date("h:i:s"));
				}
				$html = '<select fieldType="time" fieldName="'.$originalFieldName.'" name="'.$originalFieldName.'_cms_hour" style="width:50px;min-width:0;">';
				for($i = 0; $i<= 23; $i++){
					$html .= '<option value="'.$i.'" '.($hour == $i ? 'selected="selected"' : '').'>'.($i<10 ? '0' : '') . $i.'</option>';
				}
				$html .=  '</select>&nbsp;:&nbsp;</label>';

				$html .= '<label><select fieldType="time"  fieldName="'.$originalFieldName.'"  name="'.$originalFieldName.'_cms_minute" style="width:50px;min-width:0;">';
				for($i = 0; $i<= 59; $i++){
					$html .= '<option '.($minute == $i ? 'selected="selected"' : '').'>'.($i<10 ? '0' : '') . $i.'</option>';
				}
				$html .=  '</select>&nbsp;:&nbsp;</label>';

				$html .= '<label><select fieldType="time"  fieldName="'.$originalFieldName.'"  name="'.$originalFieldName.'_cms_seconds" style="width:50px;min-width:0;">';
				for($i = 0; $i<= 59; $i++){
					$html .= '<option '.($second == $i ? 'selected="selected"' : '').' >'.($i<10 ? '0' : '') . $i.'</option>';
				}
				$html .=  '</select><input type="hidden" name="'.$originalFieldName.'" value="'.$hour .':'. $minute .':' . $second.'" />';

				/**$html .= '
				<script>
				$(document).ready(function(){
				$("select").change(function(){

				var hours = $("select[name=\''.$originalFieldName.'_cms_hour\']").val();
				var mins = $("select[name=\''.$originalFieldName.'_cms_minute\']").val();
				var secs = $("select[name=\''.$originalFieldName.'_cms_seconds\']").val();

				$("input[name=\''.$originalFieldName.'\']").val(hours+":"+mins+":"+secs);
				});
				});
				</script>
				';
				**/

			}

			if($fieldType == 'auto_date'){
				$html = "<input type='hidden' value='".date('Y-m-d H:i:s')."' $name />";
				$html .= date('Y-m-d H:i:s');
			}



			if($fieldType == 'pdf_upload'){
				if(!empty($value)){
					$html .= '<br /><br /><div class="label">Current File: </div><a href="'.PUBLIC_HTML_SITE.DS.$value.'" target="_blank" title="View pdf" alt="PDF" />'.PUBLIC_HTML_SITE.DS.$value.'</a><br /><div class="label">&nbsp;</div>';
				}
			}

			if($fieldType == 'mp3_upload'){
				if(!empty($value)){
					$html .= '<br /><br /><div class="label">Current File: </div><a href="'.PUBLIC_HTML_SITE.DS.$value.'" target="_blank" title="Listen" alt="PDF" />'.PUBLIC_HTML_SITE.DS.$value.'</a><br /><div class="label">&nbsp;</div>';
				}
			}


			return $html;
		}


		## HTML IF A FOREIGN KEY EXISTS ##
		if($foreignKey){

			/*
			OLD LOGIC

			$html = '<select '.$fieldInfo.'>';
			$sql =  "SELECT  id,$foreign_field FROM `$foreign_table`";
			$resultSet = $this->adodb->Execute($sql);
			$html .= '<option value="" style="color:red">Select</option>';
			while($row = $resultSet->FetchRow()){
			$html .= '<option value="'.$row["id"].'" '.( $row['id'] == $value ? 'selected' : '' ) .'>'.$row[$foreign_field].'</option>';
			}
			$html .= '</select>';
			$html .= '&nbsp;<a href="'.pageLink('generate.php?table='.$foreign_table.'').'">Add a new entry</a>';
			return $html;
			*/
			TableTraversal::resetResult();
			$html = '<select '.$fieldInfo.'>';
			$html .= '<option value="">Please select...</option>';

			$sql =  "SELECT  * FROM `$foreign_table`";

			$parentStart = $foreign_table;
			//Array containing the parent tables of the starting foreign table
			$parentsTableArray [] = $parentStart;

			while( ($parent = $this->getRootTable($parentStart)) && ($parent != $parentsTableArray[0]) ){
				$parentsTableArray [] = $parent;
				$parentStart = $parent;
			}

			$parentsTableArray = array_reverse($parentsTableArray);

			$parameters = $this->getFieldParameters($originalFieldName);

			$menu = TableTraversal::createMenu($parentsTableArray[0], array ('parameters' =>$parameters));
			TableTraversal::$parentsTableArray = array (); //Reset
			TableTraversal::$visitedRows = array (); //Reset
			TableTraversal::$visited = array (); //Reset
			TableTraversal::$previousIndent = 0; //Reset



			drawSelectMenu($menu, $value , $parentsTableArray);
			$html .= TableTraversal::$result;


			$html .= '</select>';
			$html .= '&nbsp;<a class="ajaxGenerate"  data-fieldname="'.$fieldName.'" data-tablename="'.str_replace("'",'',$tableName).'" href="'.pageLink('generate.php?table='.$foreign_table.'').'">Add a new entry</a>';
			return $html;
		}

		## HTML IF THIS A POSITION FIELD ##
		if($fieldType == 'position'){
			$totalPositions = $this->totalRows(); //Total rows in the given table
			$html = '<select '.$fieldInfo.'>';
			for($i=1;$i<=$totalPositions;$i++){
				$html .= '<option value="'.$i.'" '.( $i == $value ? 'selected' : '' ) .'>'.$i.'</option>';
			}
			$html .= '</select>';
			return $html;
		}
		##ENUM##
		if($fieldType == 'enum'){
			$sql = "SHOW COLUMNS FROM ".$this->tableName." LIKE $fieldName";
			$result = $this->adodb->Execute($sql);
			$row = $result->FetchRow();
			$type = $row['Type'];
			preg_match('/enum\((.*)\)$/', $type, $matches);
			$vals = explode(',', $matches[1]);
			$html = '<select '.$fieldInfo.'>';
			$html .= '<option value="" style="color:red">Select</option>';
			foreach($vals as $enumvalue){
				$enumvalue = str_replace("'","",$enumvalue);
				$html .= '<option value="'.$enumvalue.'" '.( $enumvalue == $value ? 'selected' : '' ) .'>'.$enumvalue.'</option>';
			}
			$html .= '</select>';
			return $html;

		}
		##IF THE FIELD IS UNKOWN IN THE SYSTEM TABLE, OUTPUT A SIMPLE INPUT TEXT FIELD . THIS CASE IF VERY UNLIKELY ##
		return '<input type="text" '.$fieldInfo.' value="'.$value.'" />';

	}

}


?>