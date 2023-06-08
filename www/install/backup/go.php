<?php
    require('../../server/api-adodb/config.php');

    $postValues = $_POST;
    $table= new Table();

    if(!empty($_POST)){
        overwriteHostedWithLSD($_POST['lsdHosting']);
    }

    //Install Default Modules
    foreach($postValues as $key=>$value){
        if(strpos($key,'PresetMod') !== false){

            $methodName = str_replace('PresetMod','',$key);

            if(method_exists($table,$methodName)){
                $table->$methodName();
                unset($postValues[$key]);
                unset($_POST[$key]);
            }
        }
    }
    /*
    if(isset($postValues['newsPresetMod'])){
        $table->installNewsModule();
        unset($postValues['newsPresetMod']);
        unset($_POST['newsPresetMod']);
    }

    if(isset($postValues['cvPresetMod'])){
        $table->installCVModule();
        unset($postValues['cvPresetMod']);
        unset($_POST['cvPresetMod']);
    }

    if(isset($postValues['socialPresetMod'])){
        $table->installSocialModule();
        unset($postValues['socialPresetMod']);
        unset($_POST['socialPresetMod']);
    }

    if(isset($postValues['mainPresetMod'])){
        $table->installMainModule();
        unset($postValues['mainPresetMod']);
        unset($_POST['mainPresetMod']);
    }

    if(isset($postValues['galleryPresetMod'])){
        $table->installGalleryModule();
        unset($postValues['galleryPresetMod']);
        unset($_POST['galleryPresetMod']);
    }

    if(isset($postValues['mediaPresetMod'])){
        $table->installMediaModule();
        unset($postValues['mediaPresetMod']);
        unset($_POST['mediaPresetMod']);
    }

    if(isset($postValues['instructionsPresetMod'])){
        $table->installInstructionsModule();
        unset($postValues['instructionsPresetMod']);
        unset($_POST['instructionsPresetMod']);
    }
    */

    //End installation
    foreach($postValues as $field_table_name=>$fieldType){

        $foreignTable="";
        $foreignField="";

        //Search for the disableCRUD-tableName checkboxes
        //If found, save them in a separate table (table_options)
        if(strpos($field_table_name,'disableCRUD') !== false){
            if($fieldType == 'on'){
                list($dummy,$disableTableName) = explode('disableCRUD-',$field_table_name);
                $table->saveTableOptions($disableTableName,'disable_crud',1);
            }
            continue; // Skip to the next $_POST item
        }

        if(strpos($field_table_name,'cmsparameters') !== false){
            if(!empty($fieldType)){
                list($dummy,$parameterName,$tableName,$fieldName) = explode('--',$field_table_name);
                $table->saveFieldParameters($fieldType,$parameterName,$tableName,$fieldName);
            }
            continue; // Skip to the next $_POST item
        }

        if($fieldType=='foreign'){
            continue;
        }
        if(strpos($field_table_name,'foreignTable') !== false){
            continue;
        }
        if(strpos($field_table_name,'cmsgen_mandatory') !== false){
            continue;
        }


        if(strpos($field_table_name,'cmsgen_active') !== false){
            continue;
        }
        $mandatory = !empty($_POST['cmsgen_mandatory--'.$field_table_name]) ? 1 : 0;
        $active = !empty($_POST['cmsgen_active--'.$field_table_name]) ? 1 : 0;

        if(strpos($field_table_name,'foreignField') !== false){
            list($tableName,$fieldName) = explode('--',$field_table_name);
            list($foreignTable,$foreignField) = explode('--',$fieldType);
            $fieldType='foreign';
            // $field_table_name.'::::::::::::Field Name:::::::::::::'.$fieldName.'::::::::::::Table Name:::::::::::::'.$tableName.'::::::::::::FieldType:::::::::::::'.$fieldType.'----ForeignTable::::::::::::'.$foreignTable.'::::::::::::Foreign Field:::::::::::::'.$foreignField.'<br />';
        }else{
            list($tableName,$fieldName) = explode('--',$field_table_name);
            //echo '::::::::::::Field Name:::::::::::::'.$fieldName.'::::::::::::Table Name:::::::::::::'.$tableName.'::::::::::::Value:::::::::::::'.$fieldType.'----'.$mandatory.'<br />';
        }

        $table->saveSystemField($tableName,$fieldName,$fieldType,$mandatory,$foreignTable,$foreignField,$active);

        //Check for bulk inserts
        if(strpos($field_table_name,'bulkInsert') !== false){

            //Decide how many inserts to do
            $numberOfInserts = (!empty($fieldType) && $fieldType > 0 && $fieldType <= 10) ? (int)$fieldType : 0;
            if($numberOfInserts > 0){
                list($dummy,$bulkInsertTableName) = explode('bulkInsert-',$field_table_name);

                $table->bulkInsert($numberOfInserts,$bulkInsertTableName);
            }
        }
    }

?>
<!DOCTYPE>
<html>
    <head>
        <title>CMS Generated</title>
        <link href="<?php echo ADMIN_PATH_HTML.DS; ?>css/styles.css" media="all" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="<?php echo ADMIN_PATH_HTML.DS; ?>js/jquery.js"></script>
        <script type="text/javascript" src="<?php echo ADMIN_PATH_HTML.DS; ?>js/scripts.js"></script>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width:300px; background:#fff; padding:10px; margin:20px auto;border:thin solid #dcc6c6;text-align:center;" class="round drop_shadow" id="install">
            <a href="../install/"><img src="<?php echo ADMIN_PATH_HTML.DS; ?>images/logo-inside.png"  /></a><br /><br />
            <h1>CMS Generated successfully!</h1><br />
            <a class="link-cust" href="../admin">Visit CMS</a> - <a class="link-cust" href="../index.php">Visit SITE</a>
            <br><br>
            <h2>Dont forget to delete the "install" folder from the server!</h2>
        </div>
    </body>
    </html>