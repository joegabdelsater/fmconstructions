<?php

    @include_once('../../../server/api-adodb/config.php');

   // echo $_GET['fieldName'];
    $fieldName = $_GET['fieldName'];
    $fieldName = str_replace("data","",$fieldName);
    $fieldName = str_replace("[","",$fieldName);

    list($tableName,$fieldName,$columnName) = explode(']',$fieldName);

?>
<label><input name="data[<?php echo $tableName; ?>][<?php echo $fieldName; ?>][parameters][max_image_width]" placeholder="Max Image Width (px)" /></label>
<label><input name="data[<?php echo $tableName; ?>][<?php echo $fieldName; ?>][parameters][max_image_height]" placeholder="Max Image Height (px)" /></label>
<label><input name="data[<?php echo $tableName; ?>][<?php echo $fieldName; ?>][parameters][image_proportions]" placeholder="Image Proportions (float)" /></label>