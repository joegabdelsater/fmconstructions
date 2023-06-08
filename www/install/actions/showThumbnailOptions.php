<?php

    @include_once('../../../server/api-adodb/config.php');

    // echo $_GET['fieldName'];
    $fieldName = $_GET['fieldName'];
    $fieldName = str_replace("data","",$fieldName);
    $fieldName = str_replace("[","",$fieldName);

    list($tableName,$fieldName,$columnName) = explode(']',$fieldName);
    $table = new Table($tableName);
    $fields = $table->getTableFields();

?>
<label>
    <select class='required' name="data[<?php echo $tableName; ?>][<?php echo $fieldName; ?>][parameters][field_name]" style="margin-top:2px;">
        <option value=''>Thumbnail for which field</option>
        <?php foreach($fields as $key=>$field): ?>
            <option value="<?php echo $field; ?>"><?php echo $field; ?></option>
            <?php endforeach; ?>
    </select>

</label>

<label><input name="data[<?php echo $tableName; ?>][<?php echo $fieldName; ?>][parameters][thumbnail_preset_ratio]" placeholder="Thumbnail Ratio" /></label>