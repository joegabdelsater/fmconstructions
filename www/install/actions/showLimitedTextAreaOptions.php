<?php

    @include_once('../../../server/api-adodb/config.php');

    // echo $_GET['fieldName'];
    $fieldName = $_GET['fieldName'];
    $fieldName = str_replace("data","",$fieldName);
    $fieldName = str_replace("[","",$fieldName);

    list($tableName,$fieldName,$columnName) = explode(']',$fieldName);
    $table = new Table($tableName);
    $params = $table->getFieldParameters($fieldName);
    $maxLength = '';
    if(!empty($params) && isset($params['maxlength'])){
        $maxLength = $params['maxlength'];
    }

?>
<label><input name="data[<?php echo $tableName; ?>][<?php echo $fieldName; ?>][parameters][maxlength]" placeholder="Maximum Length" value="<?php echo $maxLength; ?>" /></label>