<?php

    require_once('../../../server/api-adodb/config.php');
    $tableName = !empty($_GET['table']) ? $_GET['table'] : NULL;

    $table = new Table($tableName);
    $fields = $table->getTableFields();
    $fieldName = $_GET['fieldName'];

    // list($tableName,$fieldName) = explode('--',$fieldName);
?>
<select name="<?php echo $fieldName;?>" style="margin-top:2px;">
    <option>Select Field</option>
    <?php foreach($fields as $key=>$field): ?>
        <option value="<?php echo $field; ?>"><?php echo $field; ?></option>
        <?php endforeach; ?>
</select>
<?php
    $fieldName = $_GET['fieldName'];
    $fieldName = str_replace("data","",$fieldName);
    $fieldName = str_replace("[","",$fieldName);

    list($tableName,$fieldName,$columnName) = explode(']',$fieldName);
?>                           
<label>Multi Foreign:<input name="data[<?php echo $tableName; ?>][<?php echo $fieldName; ?>][parameters][multi]" value="1" type="checkbox" /></label>