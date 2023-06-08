<?php require("common/header.php");?>
<?php require("common/menu.php");?>
<?php $tableName = $_GET['table'];?>

<!-- Main section-->
<section>
	<!-- Page content-->
	<div class="content-wrapper">

		<div id='loader'>
			<div class="panel panel-default"  style="border: 0;background-color: transparent;">
				<div class="panel-body loader-demo" style="border: 0;padding-top: 20%;">
					<div class="ball-scale-ripple-multiple" >
						<div></div>
						<div></div>
						<div></div>
					</div>
				</div>
			</div>
		</div>


		<?php if(isset($_GET['action']) && $_GET['action'] == 'search') {
			$pageTitle = 'Advanced Search for '.printTableName($tableName).' <a href="'.pageLink('generate.php?table='.$tableName).'">[ + ]</a>';
		} else {
			$pageTitle = 'Listing all Entries in `'.printTableName($tableName).'` <a href="'.pageLink('generate.php?table='.$tableName).'">[ + ]</a>';
		} 

		require('common/header_page.php');
		?>

		<?php

		if(!empty($_GET['table'])){
			$tableName = $_GET['table'];
		}else{
			$session->message('<strong>Please select a table.</strong>');
			redirect_to("dashboard.php?errorPage=list.php");
		}

		$table = new Table($tableName);
		$totalRows  = $table->countAllRows();//Total number of entries
		//Pagination
		$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
		$perPage = PER_PAGE;
		$maxPages = ceil($totalRows/$perPage);//maximum number of pages that could be returned
		$pagination = new Pagination($page,$perPage,$totalRows);


		$allRows = $table->listAllRows($pagination->offset(),$perPage);


		$childTableInformation = $table->getChildTableInformation();
		//If no data is found, redirect to add new data
		if(empty($allRows) && !isset($_GET['action']) ){
			redirect_to("generate.php?table=".$tableName);
		}elseif(empty($allRows) && isset($_GET['action']) && $_GET['action'] == 'search'){
			$session->message('<strong>No search results match your query.</strong>');
			redirect_to("list.php?table=".$tableName);
		}
		$allFields = $table->getTableFields();

		//$columnsCount = count($allFields);
		//bug fixed:
		$columnsCount = $table->findSql("SELECT COUNT(id) AS COUNT FROM system WHERE table_name = '".unhashTable($tableName)."' AND is_visible = 1");
		$columnsCount = $columnsCount[0]['COUNT'];

		//Delete IDs
		if(!empty($_POST['delete'])){
			$table->deleteRows($_POST['ids']);
			die;

		}

		$tableInfo = $table->getTableInfo();
		?>

		<?php include(ADMIN_PATH.DS."common/listForm.php"); ?>

		<!-- Advanced Search -->
		<style>
			.advanced-search textarea{
				max-width: 100%;
			}
		</style>
		<div class="advanced-search panel panel-default"  style="display: none;width:400px">
			<form name="advancedSearch " method="post"  style=" width: 400px; padding:10px;" action="<?php echo pageLink('list.php?table='.$tableName.'&action=search'); ?>">
				<fieldset>
					<legend><strong>Advanced Search</strong></legend>
					<?php 

					foreach($tableInfo as $field) :


						if(in_array($field['field_type'], array ('auto_date','position','id','photo_upload','thumbnail','pdf_upload','mp3_upload'))){
							continue;   
						}
						?>

						<div class="field form-group">
							<label class="input-control text"><strong><?php echo printTableName($field['field_name']); ?></strong>
								<?php 
								switch($field['field_type']){
									case 'checkbox':
										$element = new FormElement($table->getRawTableName(), $field['field_name'] ,null,null,array());
										$fieldInfo = $element->getFieldInfo();
										echo '<select '.$fieldInfo.'>';
										echo '<option value="">Any</option>';
										echo '<option value="1">Checked</option>';
										echo '<option value="0">Unchecked</option>';
										echo '</select>';
										break;
									default:
										echo $table->getFormElement($field['field_name']);
										break;   
								}

								?>
							</label>
						</div>

						<?php endforeach; ?>
					<input type="submit" name="submit" value="Search" />
					<input type="reset" name="reset" value="Reset" />
				</fieldset>
			</form>
		</div>
		<!-- END Advanced Search --> 

		<?php if($table->isCrudEnabled()) :  ?>
			<div class="modal hide"></div>
			<div class="hide shadow round bulkEditContainer" id="bulkEditContainer">
				<div class="closeBulkEditBtn close">x</div>
				<div class="title">
					<h3>Edit Multiple entries</h3>
					<div class="result"></div>
				</div>
				<form action="actions/bulkEdit.php" method="post" name="bulkEditForm" class="bulkEditForm">
					<input type="hidden" name="token" value="<?php echo CSRF::generateToken(); ?>" />
					<div class="highlight input-control select">
						<label for="cmsgen_rowsToEdit">Select the rows you want to edit:</label><br />
						<select name="cmsgen_rowsToEdit[]" multiple="multiple" id="cmsgen_rowsToEdit" class="fullWidth">
							<?php
							$displayField = $table->getDisplayField();
							foreach($allRows as $row) : ?>
								<option value="<?php echo $row['id']; ?>"><?php echo $row['id'] .' | '.$row[$displayField]; ?></option>
								<?php endforeach; ?>
						</select>
					</div>


					<?php
					//Loop through the current table's fields and output the corresponding form
					foreach($table->returnFieldsNamesTypes() as $fieldName => $fieldTypeLength){
						$fieldType = $table->getFieldType($fieldName);
						if(in_array($fieldName,array('id','pos')) || in_array($fieldType,array('photo_upload','thumbnail','textarea','mp3_upload','auto_date'))){
							continue;
						}


						echo'<div class="field"><label class="input-control text"><div class="label '. ($table->isRequired($fieldName) ? 'required' : '' ) .'">';

						$tooltip = $table->getTooltip($fieldName);

						echo printTableName($fieldName);
						echo'</div>';
						echo $table->getFormElement($fieldName); //Displays the corresponding form element to the following field
						if(!empty($tooltip))
							echo '<span title="'.$tooltip.'" class="tooltipMessage"></span>';

						echo '</label></div>';
						echo '<br />';

					}
					?>
					<br />
					<button class="submit round drop_shadow saveBulkEdit" style="float:left">Save</button>
					<input class="submit round drop_shadow"  type="reset" value="Reset" style="float:left;margin-left:4px;">
					<button class="submit round drop_shadow closeBulkEditBtn"  value="Close" style="float:left;margin-left:4px; background: red;">Close</button>
					<input type="hidden" value="<?php echo $tableName;?>" name="table" />
				</form>
			</div>
			<?php endif; ?>

	</div>

</section>

<?php require("common/footer.php");?>
<?php include(ADMIN_PATH.DS."plugins".DS."imagepop.php"); ?>


<script>
	var oTable;
	/*
	* define the column number of position column
	*  function to read data from cells dynamically on every sort
	*/
	<?php if($table->isTableSortable()) { ?>
		//var posColumn = <?php echo $columnsCount - 1; ?>;
		var posColumn = <?php echo $positionColumnNumber + 2;?>;

		$.fn.dataTableExt.afnSortData['dom-text'] = function  ( oSettings, iColumn ){
			return $.map( oSettings.oApi._fnGetTrNodes(oSettings), function (tr, i) {
				return $('td:eq('+iColumn+')', tr).html();
			} );
		}
		<?php } ?>

	$(document).ready( function () {
		// initialize data tables
		// exclude sorting on last 2 columns
		// sort by 'pos' in case available
		var oTable = $('table').dataTable( {
			"iDisplayLength": 25,
			"aLengthMenu": [[10, 25, 50, 100, 150, 200, 300, -1], [10, 25, 50, 100, 150, 200, 300, "All"]],
			"sDom": 'C<"clear">lfrtip',
			"oColVis": {
				"aiExclude": [ 0,1 ]
			},
			"aoColumnDefs": [
				{ "bSortable": false, "aTargets": [ 0,1 ] }
			],
			"oLanguage": { "sSearch": "" }
			<?php if ($table->isTableSortable()): ?>
				,"aoColumns": [<?php for($i=1;$i<$columnsCount;$i++) {echo 'null,';}?>{ "sSortDataType": "dom-text" },null,null  ],
				"aaSorting": [[ posColumn, "asc" ]]
				<?php else : ?>
				,"aoColumns": [<?php for($i=1;$i<$columnsCount;$i++) {echo 'null,';}?>{ "sSortDataType": "dom-text" },null,null  ],
				"aaSorting": [[ 2, "desc" ]]
				<?php endif; ?>
		} );

		/*var dtInstance2 = $('table').dataTable({
		'paging':   true,  // Table pagination
		'ordering': true,  // Column ordering 
		'info':     true,  // Bottom left status text
		// Text translation options
		// Note the required keywords between underscores (e.g _MENU_)
		oLanguage: {
		sSearch:      'Search all columns:',
		sLengthMenu:  '_MENU_ records per page',
		info:         'Showing page _PAGE_ of _PAGES_',
		zeroRecords:  'Nothing found - sorry',
		infoEmpty:    'No records available',
		infoFiltered: '(filtered from _MAX_ total records)'
		}
		});
		var inputSearchClass = 'datatable_input_col_search';
		var columnInputs = $('tfoot .'+inputSearchClass);

		// On input keyup trigger filtering
		columnInputs
		.keyup(function () {
		dtInstance2.fnFilter(this.value, columnInputs.index(this));
		});

		*/

		// add placeholder text to the search field
		$('.dataTables_filter input').attr("placeholder", "Search...");
		<?php if($table->isTableSortable()) : ?>
			$("#list tbody.content").sortable({opacity: 0.6,cursor: 'move',items: "tr:not(.ui-state-disabled)",helper: function(e, tr){
				var $originals = tr.children();
				var $helper = tr.clone();
				$helper.children().each(function(index)
					{
						// Set helper cell sizes to match the original sizes
						$(this).width($originals.eq(index).width())
				});
				return $helper;
				},update: function() {
					var order = $(this).sortable("serialize") + '&action=updateRecordsListings&table=<?php echo $tableName; ?>&page=<?php echo $page; ?>';
					var sortedIDs = $( this ).sortable( "toArray" );

					$.post("<?php echo ADMIN_PATH_HTML; ?>/actions/order.php", order, function(theResponse){
						//                        var rows = oTable.fnGetNodes();
						var rows = $("#DataTables_Table_0 tr");
						var cells = [];
						console.log(posColumn);
						$.each(rows, function(i, row) {
							// $(row).find("td:eq("+posColumn+")").html(i);
							$(row).find('td[data-coltype="position"]').html(i);
						});

						//                        for(var i=0;i<rows.length;i++){
						//                            var newOrder = i;
						//                            $(rows[i]).find("td:eq(2)").html(i);
						//                        }
						$("#saveResult").html(theResponse);
						$("#saveResult").hide();
						$("#saveResult").html("<div class='response_class'>Rows order updated</div>");
						$('#saveResult').fadeIn('slow').delay(2000).fadeOut("fast");
					});
				}
			});
			$("#list tbody.content").disableSelection();
			<?php endif; ?>



		/* Apply the jEditable handlers to the table */
		$('td.jeditable', oTable.fnGetNodes()).editable( 'actions/jeditable.php', {
			"callback": function( sValue, y ) {
				var aPos = oTable.fnGetPosition( this );
				oTable.fnUpdate( sValue, aPos[0], aPos[1], false );
			},
			"submitdata": function ( value, settings ) {
				return {
					"row_id": this.parentNode.getAttribute('id'),
					"column": oTable.fnGetPosition( this )[2],
					"table" : "<?php echo $tableName; ?>",
					"token" : "<?php echo CSRF::generateToken(); ?>"
				};
			},
			cssclass : 'metroJedit',
			tooltip: 'Click to edit...',
			indicator: "<img src='images/loading.gif' />",

			intercept: function (jsondata) {
				console.log('test');
				obj = jQuery.parseJSON(jsondata);
				// do something with obj.status and obj.other
				if (obj.status == 'fail') {
					var msg = "An error occured, value was not changed!";
					if (obj.msg != '') {
						msg = obj.msg;
					}
					alert (msg);
				}
				return(obj.value);
			}
		} );

		$('td.jeditableSelect', oTable.fnGetNodes()).editable( 'actions/jeditable.php', {
			data   : "{'on':'Yes','off':'No'}",
			type: "select",
			onblur: 'ignore',
			"callback": function( sValue, y ) {
				var aPos = oTable.fnGetPosition( this );
				oTable.fnUpdate( sValue, aPos[0], aPos[1], false );
			},
			"submitdata": function ( value, settings ) {
				return {
					"row_id": this.parentNode.getAttribute('id'),
					"column": oTable.fnGetPosition( this )[2],
					"table" : "<?php echo $tableName; ?>",
					"token" : "<?php echo CSRF::generateToken(); ?>"
				};
			},
			cssclass : 'metroJedit',
			tooltip: 'Click to edit...',
			indicator: "<img src='images/loading.gif' />",

			intercept: function (jsondata) {
				console.log('test');
				obj = jQuery.parseJSON(jsondata);
				// do something with obj.status and obj.other
				if (obj.status == 'fail') {
					var msg = "An error occured, value was not changed!";
					if (obj.msg != '') {
						msg = obj.msg;
					}
					alert (msg);
				}
				return(obj.value);
			}
		} ).on('click', function () {
			if($(this).find('.select2').length <= 0){
				$(this).find('select').select2({});
			}
		});
		$(document).on('change', 'td.jeditableSelect select', function () {
			$(this).trigger("submit");
		})



	} );

	$(document).ready(function()
		{
			// MAKE SURE YOUR SELECTOR MATCHES SOMETHING IN YOUR HTML!!!
			$('.showAddChildrenTooltip').each(function() {
				$(this).qtip({
					content: {
						text: $(this).closest("td").find('.tooltipAddChildrenText')
					},
					position  : {
						my: 'top left',  // Position my top left...
						at: 'bottom right', 
					},
					hide: {
						fixed: true
					}
				});
			});
	});
</script>
