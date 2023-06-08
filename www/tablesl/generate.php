<?php
if ( isset($_GET['ajax'])){
	require_once('common/header.php');
}
else{
	require_once('common/header.php');
	require("common/menu.php");
}


if(!empty($_GET['table'])){
    $tableName = $_GET['table'];
}else{
    die("Please select a table!");
}

$pageToken = CSRF::generateToken();

$table = new Table($tableName);
$id = !empty($_GET['id']) ? $_GET['id'] : 0;
//oneRowTables have only one default row and users cannot add to it
if(!$table->isCrudEnabled() && empty($id)){
    redirect_to("dashboard.php");
}
?>
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

        <div >
            <?php include(ADMIN_PATH.DS."common/generateForm.php"); ?>
        </div>
        <!-- TinyMCE -->

    </div>

</section>


<?php require_once('common/footer.php'); ?>

<script type="text/javascript" src="<?php echo ADMIN_PATH_HTML;?>/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_PATH_HTML;?>/js/tinymce/jquery.tinymce.min.js"></script>

<script type="text/javascript">
    // Create the tooltips only on document load
    $(document).ready(function()
        {
            // By suppling no content attribute, the library uses each elements title attribute by default
            $('.tooltipMessage[title]').qtip({

            });

            // NOTE: You can even omit all options and simply replace the regular title tooltips like so:
            // $('#content a[href]').qtip();
    });
</script>



<script type="text/javascript">
    // initialize editors
    initMCE();

    $(document).ready(function(){
        if ( $('input[placeholder=Slug]') && $('input[placeholder=Slug]').val() == '' ) {
            // if ( $('input[placeholder=Title]') || $('input[placeholder=Name]') ) {
            // var field = ( $('input[placeholder=Title]') ) ? $('input[placeholder=Title]') : $('input[placeholder=Name]');
            // field.keyup(function(){
            // $('input[placeholder=Slug]').val(string_to_slug($(this).val()));
            // });
            // }
        }
        if ($('input[placeholder=Permalink]')) {
          var permalinkAlreadyFilled = false;
          if ( $('input[placeholder=Permalink]').val() != ''){
            permalinkAlreadyFilled = true;
          }
            if ($('input[placeholder=Title]')) {
                $('input[placeholder=Title]').keyup(function(){
                      if  ( !permalinkAlreadyFilled ) {
                        $('input[placeholder=Permalink]').val(string_to_slug($(this).val()));
                    }
                });
            }
        }
        if ($('input[placeholder=Slug]')) {
            var slugAlreadyFilled = false;
            if ( $('input[placeholder=Slug]').val() != ''){
              slugAlreadyFilled = true;
            }
            if ($('input[placeholder=Title]')) {
                $('input[placeholder=Title]').keyup(function(){
                    if  ( !slugAlreadyFilled ) {
                        $('input[placeholder=Slug]').val(string_to_slug($(this).val()));
                    }
                });
            }
        }
    });

    function string_to_slug(str) {
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
        var to   = "aaaaeeeeiiiioooouuuunc------";
        for (var i=0, l=from.length ; i<l ; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
        .replace(/\s+/g, '-') // collapse whitespace and replace by -
        .replace(/-+/g, '-'); // collapse dashes

        return str;
    }
</script>
<!-- /TinyMCE -->
