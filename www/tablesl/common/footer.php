
<!-- Modal -->
<div id="alert-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body" id='alert-modal-text'>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>


<div id="alert-modal-ajaxpopup" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div style="height: 400px;max-height: 80%;" class="modal-body" id='alert-modal-text-ajax'>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" id='alert-modal-ajax-close' data-target='' data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>

<!-- Page footer-->
<!--<footer>
<span>&copy; 2016 - Angle</span>
</footer>-->
</div>
<!-- =============== VENDOR SCRIPTS ===============-->
<!-- MODERNIZR-->
<script src="js/vendor/modernizr/modernizr.custom.js"></script>
<!-- JQUERY-->

<script src="js/vendor/jquery.datetimepicker.js"></script>
<!-- BOOTSTRAP-->
<script src="js/vendor/bootstrap/dist/js/bootstrap.js"></script>
<!-- STORAGE API-->
<script src="js/vendor/jQuery-Storage-API/jquery.storageapi.js"></script>
<!-- JQUERY EASING-->
<script src="js/vendor/jquery.easing/js/jquery.easing.js"></script>
<!-- ANIMO-->
<script src="js/vendor/animo.js/animo.js"></script>
<script src="js/vendor/bvalidator/jquery.bvalidator.js"></script>
<script src="js/vendor/select2/dist/js/select2.js"></script>

<!-- FLOT CHART-->
<script src="js/vendor/Flot/jquery.flot.js"></script>
<script src="js/vendor/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
<script src="js/vendor/Flot/jquery.flot.resize.js"></script>
<script src="js/vendor/Flot/jquery.flot.pie.js"></script>
<script src="js/vendor/Flot/jquery.flot.time.js"></script>
<script src="js/vendor/Flot/jquery.flot.categories.js"></script>
<script src="js/vendor/flot-spline/js/jquery.flot.spline.min.js"></script>


<!-- DATATABLES-->
<script src="js/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="js/vendor/datatables-colvis/js/dataTables.colVis.js"></script>
<script src="js/vendor/datatables/media/js/dataTables.bootstrap.js"></script>

<script src="js/vendor/jquery.jeditable.min.js"></script>


<script src="js/vendor/jquery.Jcrop.min.js"></script>
<link rel="stylesheet" href="css/jquery.Jcrop.css" type="text/css" />


<script src="js/vendor/qtip/qtip.js"></script>
<script src="js/vendor/jqueryui.js"></script>

<!-- LOCALIZE-->
<script src="js/vendor/jquery-localize-i18n/dist/jquery.localize.js"></script>
<!-- =============== PAGE VENDOR SCRIPTS ===============-->
<!-- =============== APP SCRIPTS ===============-->
<script src="js/app.js"></script>
<script src="js/scripts.js"></script>

<script>
	$(document).ready(function () {
		$('form').bValidator(options);
		<?php if(defined('DISABLE_SELECT2') && !DISABLE_SELECT2) : ?>
			$("select").not(".no-select2").select2({
				theme: 'bootstrap',
				dropdownAutoWidth:true,
			});
			<?php endif; ?>
	});
</script>
<?php
if(DEBUG){
	$t = new Table();
	echo '<pre>';
	echo '<ol>';
	echo 'BACK TRACE';
	foreach (  $t->adodb->showQAll()  as $value){
		if(is_array($value)){
			echo '<li > <a href="#" class="show-back-trace">Show Back Trace</a> <ul style="display:none;" class="back-trace"><li>';
			echo '<pre style="background-color:#fff;">';
			print_r($value);
			echo '</pre>';
			echo '</li></ul></li>';
		}   else {
			echo '<li>'.($value).'</li>';
		}
	}
	//  echo '<li>'.join('</li><li>' ,  $t->adodb->showQAll()).'</li>';
	echo '</ol>';
	echo '</pre>';

	?>
	<script type="text/javascript">
		$(document).ready ( function () {
			$(".show-back-trace").click ( function (e) {
				e.preventDefault();
				$(this).closest("li").find(".back-trace").toggle();
			});
		});
	</script>
	<?php
}
?>

</body>

</html>

<?php ob_flush(); ?>
