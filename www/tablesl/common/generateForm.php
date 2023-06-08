<?php
if(!isset($id)){
	$id = NULL;
}

if(isset($_GET['version']) && !empty($_GET['version']) ){
	$versionId =  (int)$_GET['version'];  
}else{
	$versionId = null;
}

$originalTable = $table;
$originalId = $id;
$relatedTables = array ();
if(!empty($id)){
	$relatedTables = $table->getRelatedTables();
}
array_unshift($relatedTables, array ( 'originalTable' => true ,  'tableName' => $table->getRawTableName() , 'displayName' => printTableName($table->getRawTableName()) ) );

// remove link to same table
// remove link to same table
foreach ($relatedTables AS $k=>$v) {
	if ($v['tableName'] == $tableName && $v['originalTable'] != 1) {
		unset($relatedTables[$k]);
	}
}
?>
<iframe id="upload_target" name="upload_target" src="blank_iframe.html" style="width:0;height:0;border:0px solid #fff;"></iframe>

<?php if (count($relatedTables) > 1) { ?>
	<ul class="nav nav-tabs" id="navlist--">
		<?php  foreach($relatedTables as $relatedTable) { ?>
			<li class="<?php echo $relatedTable['tableName'] == $tableName ? 'active' : ''; ?>">
				<a data-toggle='tab' href="#tab_<?php echo $relatedTable['tableName']; ?>" ><?php echo $relatedTable['tableName'] == $tableName ? '' : 'Related '; ?>`<?= $relatedTable['displayName']; ?>`</a>
			</li>
			<?php } ?>
	</ul>
	<?php } ?>

<div class='tab-content'>
	<?php
	foreach($relatedTables as $relatedTable) {
		$isOriginalTable = false;
		$tableName = $relatedTable['tableName'];
		if($originalTable->getRawTableName() == $relatedTable['tableName']){
			$isOriginalTable = true;
			$id = $originalId;
		} else {
			$id = null;   
		}


		$table = new Table($relatedTable['tableName']);

		?>

		<div id="tab_<?php echo $relatedTable['tableName']; ?>" class="tab-pane fade <?php echo $isOriginalTable ? 'in active' : ''; ?>">
			<form role='form' action="<?php echo pageLink('actions/upload.php'); ?>" method="post" id="generateForm" class="form generateForm generateForm_<?php echo $tableName; ?> form-horizontal" enctype="multipart/form-data" target="upload_target"  onsubmit-="startUpload();">
				<div class='content-wrapper'>
				<div class="content-heading">
					<?php echo printTableName($tableName) ; ?> <em class="fa fa-chevron-circle-right"></em>

					<?php echo empty($id) ? ' Adding new entry' : 'Updating Entry #'.$id; ?> <span id="result" style='color:#6e6;font-weight: bold;'></span>

				</div>


				<div class="create active">
					<?php
					include("common/shortcodes.php"); 

					//Loop through the current table's fields and output the corresponding form
					foreach($table->returnFieldsNamesTypes() as $fieldName => $fieldTypeLength){
						if($fieldName == 'id' || $fieldName == 'pos'){ continue; }

						$tooltip = $table->getTooltip($fieldName);

						if(!isset($formOptions)){
							$formOptions = array ();
						}

						if(isset($relatedTable['foreignKey']) && $relatedTable['foreignKey'] == $fieldName ){
							$formOptions['forceValue'] = $originalId;
						} else {
							$formOptions = array ();
						}


						echo'<div class="form-group">';

						echo '<label class="col-md-2 control-label '. ($table->isRequired($fieldName) ? 'required' : '' ) .'">';
						echo printTableName($fieldName);
						echo '</label>';

						echo '<div class="col-md-10">';
						echo $table->getFormElement($fieldName,$id,$versionId,$formOptions); //Displays the corresponding form element to the following field

						if(!empty($tooltip)) {
							echo '<span title="'.$tooltip.'" class="tooltipMessage"></span>';
						}
						echo '</div>';

						echo '</div>';

					} // end for each
					?>
					<br style="clear: both;" />
					<div style="float: left; margin-top: 20px;">
						<?php if ($table->isPhotoGallery() && empty($id)) : ?>
							<input class="btn btn-primary btn-sm btn-success triggerFineUploader" type="button" value="Save" data-play='flash' data-animation />
							<?php else : ?>
							<input class="btn btn-primary btn-sm btn-success save-generate-form" id="save" type="submit" value="Save" data-table-name="<?php echo $tableName; ?>"  />
							<?php endif; ?>

						<input type="hidden" name="token" value="<?php echo $pageToken; ?>" />
						<input class="btn btn-primary btn-sm btn-warning"  type="reset" value="Reset" >
					</div>
					<input type="hidden" name="data[<?php echo $tableName; ?>][id]" value="<?php echo $id; ?>" />
					<input type="hidden" name="table" value="<?php echo $tableName; ?>" />

					<?php if ( !isset($_GET['ajax'])){ ?>
						<div class="clearFloat" style='padding-top: 10px;'>

							<?php if(!in_array($tableName,$oneRowTables)) :  ?>
								<a href="<?php echo pageLink('list.php?table='.$tableName); ?>" style="clear:both;" class="listAllRecordsLink">Back to list</a>
								<?php endif; ?>


							| <a href="<?php echo $table->returnFormatedLink($id); ?>">View on website</a>
						</div>
						<?php } else { ?>
						<input type='hidden' name='ajax'  value='1' />
						<?php } ?>

				</div>
			</form>


			<?php if(isset($relatedTable['foreignKey'])) { ?>
				<form action="<?php echo pageLink('actions/delete.php'); ?>" method="post" enctype="multipart/form-data">
					<hr>

					<div class="list in-active content-wrapper">
						<div class="content-heading">List of related `<?=$relatedTable['tableName'];?>` entries</div>

						<?php



						$allFields = $table->getTableFields();
						$childTableInformation = $table->getChildTableInformation();
						$allRows = $table->safeFind(array(
							'conditions' => array (
								$relatedTable['foreignKey'] => $originalId
							)
						));

						$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
						$perPage = $totalRows = 1000;
						$maxPages = ceil($totalRows/$perPage);//maximum number of pages that could be returned
						$pagination = new Pagination($page,$perPage,$totalRows);

						if(!empty($allRows)){
							include(ADMIN_PATH.DS."common/listTable.php");
						}else{
							echo "No records found. ";
						}



						?>

					</div>
				</form>
				<?php } ?>

		</div>

	</div>
	<?php } ?>
</div>


<?php if(!empty($id) && ($table->countContentHistoryById($id) > 0 )) : 
	$versions = $table->getContentHistoryByRowId($id);
	?>
	<br />
	<!-- Content version history drop down-->
	<div class="content-version">
		<?php echo $table->countContentHistoryById($id) . ' older versions you can revert back to. '; ?>

		<div class="field input-control text">
			<select name="versionId" id="changeDateSelect" data-current-table="<?php echo $tableName; ?>" data-current-id="<?php echo $id; ?>" onchange="document.location = this.value">
				<option value="<?php echo pageLink("generate.php?table={$tableName}&id={$id}"); ?>">No version selected</option>
				<?php $i=0; foreach($versions as $version) : $i++;
					if($i == 1){
						continue; 
					}
					?>
					<option value="<?php echo pageLink("generate.php?version={$version['id']}&table={$tableName}&id={$id}"); ?>" <?php echo $versionId == $version['id'] ? ' selected="selected" ' : ''; ?>><?php echo date("d F, Y @ h:i:s",strtotime($version['created'])); ?> -- by <?php echo User::getUsername($version['edited_by']); ?></option>
					<?php endforeach; ?>
			</select>
		</div>
	</div>
	<?php endif; ?>



<script type="text/javascript">

	function updateGenerateForm(){
		$(".tabbed-page").each( function(){
			$(this).closest(".generateForm").removeAttr("id"); 
			$(this).find(".save-generate-form").removeAttr("id"); 
		});
		$(".tabbed-page.active").closest(".generateForm").attr("id","generateForm");
		$(".tabbed-page.active").find(".save-generate-form").attr("id","save");
	}

	$(document).ready(function(){

		updateGenerateForm();


		$(".tabs li a").click(function(e){
			$(".tabs li a").removeClass("active");
			$(this).addClass("active");

			e.preventDefault();
			$(".tabbed-page").removeClass("active").addClass("in-active");
			$($(this).attr("href")).addClass("active").removeClass("in-active");

			updateGenerateForm();
		});
	});
</script>