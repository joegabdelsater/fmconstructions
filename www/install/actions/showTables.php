<?php

        @include_once('../../../server/api-adodb/config.php');


    $table2 = new Table();
    if(!isset($fieldName))
        $fieldName = isset($_GET['fieldName']) ? $_GET['fieldName'] : NULL;
    $allTables2 = $table2->listAllTables();

    $fieldName = str_replace("data","",$fieldName);
    $fieldName = str_replace("[","",$fieldName);
    list($currentTable,$fieldName,$columnName) = explode("]",$fieldName);

    $fieldNameTable = str_replace("fieldType","foreignTable",$fieldName);
?>
<select name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][foreignTable]" fieldName="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][foreignField]"  class="foreignTable left"  style="margin-top:2px;">
    <option disabled="disabled" selected="selected">Select Table</option>
    <?php foreach($allTables2 as $curtable=>$s): ?>
        <option value="<?php echo $curtable; ?>"><?php echo $curtable; ?></option>
        <?php endforeach; ?>
</select>
<div class="foreignFieldDiv left">
    <select name="foreignField" style="margin-top:2px;" disabled="disabled">
        <option>Select Field</option>
    </select>
                                    </div>