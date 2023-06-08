<?php

    require_once('../../server/api-adodb/config.php');
    if(!empty($_POST['database'])){
        $databaseName = $_POST['database'];

        if($databaseName != DB_NAME){
            die("db does not match config settings!");
        }

        //overwriteDBLine($databaseName);
        //die;
        header("Location: showDatabase.php?database=".$databaseName);
    }else{
        $databaseName = !empty($_GET['database']) ? $_GET['database']: NULL;
        if($databaseName != DB_NAME){
            redirect_to("index.php");
        }
        $table = new Table();
        $table->createBackend();

    }
    if(empty($databaseName)){
        redirect_to("index.php");
    }

    $table = new Table();

    $allTables = $table->listAllTables();

    //Unset admin tables
    //These tables have their structure already inserted inside the 'system' table
    unset($allTables['admin_advanced_settings']);
    unset($allTables['site_options']);
    unset($allTables['cmsgen_default_values']);


    $allAvailableFieldTypes = $table->returnAllAvailableFieldTypes();

?>
<!DOCTYPE>
<html>
    <head>
        <title>Generating CMS for database "<?php echo $databaseName; ?>"</title>
        <link href="css/styles2.css" media="all" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="tools/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="tools/liveedit/livequery.js"></script>
        <script type="text/javascript" src="tools/jquery.datetimepicker.js"></script>
        <script type="text/javascript" src="scripts.js"></script>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <script>
            $(document).ready(function(){
                    $("#generateForm").submit(function(e){
                            var allFilled = true;

                            $(".required").each(function(index,element){
                                    if ( $(this).val() == '' ) {
                                        $(this).css('color','red');
                                        allFilled = false;
                                    }
                            });

                            if (!allFilled) {
                                alert("Please select required fields!");
                                e.preventDefault();
                                return false;
                            }

                    });
            });
        </script>
    </head>
    <body>
        <div style="width:90%; background:#fff; padding:10px; margin:20px auto;border:thin solid #dcc6c6;" class="round drop_shadow" id="install">
            <a href="../install/"><img src="<?php echo ADMIN_PATH_HTML.DS; ?>images/logo-outside.png"  /></a><br /><br />
            <h1>Database: <?php echo $databaseName; ?></h1><br />
            <form action="go.php" method="post" id='generateForm'>
                <input type="submit" class="submit round drop_shadow" value="Generate CMS" /><br><br>
                <hr />


                <h2>License Details</h2>
                <div class="entry">
                    <div class="columnName">
                        <span class="fieldName">
                            <label> Allowed on (Comma separated domains) <input type="text" name="licenseDetails[allowedDomains]" class="styled" placeholder="grindd.com,example.com"> </label>
                        </span>
                        <br />
                        <span class="fieldName">
                            <label> Client Name   <br /><input type="text" name="licenseDetails[client]" class="styled" > </label>
                        </span>
 <br />
                        <span class="fieldName">
                            <label> Maximum Domains  <br /> <input type="text" name="licenseDetails[maximumDomains]" class="styled" placeholder="Leave empty for unlimited" > </label>
                        </span>
 <br />
                        <span class="fieldName">
                            <label> Expiry Date   <br /><input type="text" name="licenseDetails[expiryDate]" class="styled" placeholder="Leave empty for unlimited" value="<?php echo date('Y-m-d', strtotime('+1 year')); ?>" > </label>
                        </span>

                    </div>
                </div>
                <div class="clear"></div>



                <h2>Website hosted with US</h2>
                <div class="entry">
                    <div class="columnName"><label><span class="fieldName"><input type="checkbox" name="lsdHosting" class="styled" <?php if(HOSTED_WITH_US) echo 'checked="checked"'; ?>>LSD Hosting</span></label></div>
                </div>
                <div class="clear"></div>
                <h2>Check the preset modules you'd want installed:</h2>

                <?php
                    //List all the install module links
                    $allMethods = get_class_methods("Table");
                    foreach($allMethods as $methodName):
                        $originalMethodName = $methodName;
                        if(strpos($methodName,'install') === false){
                            continue;
                        }
                        $methodName = str_replace('install','',$methodName);
                        $methodName = str_replace('_',' ',$methodName);
                        $methodName = preg_replace('/(?<!\ )[A-Z]/', ' $0', $methodName);
                        $methodName = ucwords($methodName);
                    ?>
                    <div class="entry moduleInstallLink">
                        <div class="columnName"><label><span class="fieldName"><input type="checkbox" name="<?php echo $originalMethodName; ?>PresetMod" class="styled">Install <?php echo  $methodName; ?></span></label></div>

                    </div>
                    <?php endforeach; ?>


                <div class="clear"></div>
                <hr />
                <?php foreach($allTables as $currentTable=>$id): ?>
                    <h1 class="title" style="padding-bottom:10px;"><?php echo $currentTable; ?></h1>
                    <label><input type="checkbox" name="data[<?php echo $currentTable; ?>][disableCrud]" class="styled" <?php $newCurTable = new Table($currentTable); if(!$newCurTable->isCrudEnabled()) echo 'checked="checked"'; ?>  /> Disable Add/Edit/Delete</label>
                    <label style="margin-left:50px;">Bulk Inserts
                        <select name="data[<?php echo $currentTable;?>][bulkInsert]" style="min-width:0;">
                            <?php for($numberOfInserts = 0;$numberOfInserts<=10;$numberOfInserts++){
                                    echo'<option value="'.$numberOfInserts.'">'.$numberOfInserts.'</option>';
                                }
                            ?>
                        </select> rows</label>
                    <label  style="margin-left:50px;">
                        <b>Display fields</b>
                        <select class='required' name="tableOptions[<?php echo $currentTable;?>][display_fields]">
                            <option value=''>Select Field</option>
                            <?php
                                $foreignTableNew = new Table($currentTable);
                                $displayField = $foreignTableNew->getDisplayField();
                                $ForeignFields = $foreignTableNew->getTableFields();
                                foreach($ForeignFields as $key=>$foreignFieldFromTable):
                                ?>
                                <option value="<?php echo $foreignFieldFromTable; ?>" <?php echo $displayField == $foreignFieldFromTable ? 'selected="selected"' : '' ; ?>><?php echo $foreignFieldFromTable; ?></option>
                                <?php endforeach; ?>
                        </select>
                    </label>

                    <label  style="margin-left:50px;">
                        <?php
                            $linkFormat = $newCurTable->getTableOptions('link_format');
                            if(empty($linkFormat)){
                                $linkFormat = $newCurTable->getRawTableName().'.php?id=$id';
                            }
                        ?>
                        <b>Link Format</b>
                        <input type="text" name="tableOptions[<?php echo $currentTable;?>][link_format]" value="<?php echo $linkFormat; ?>" />

                    </label>
                    <br>
                    <div class="clear"></div>

                    <div class="columnName"><h3>Field Name</h3></div>
                    <div class="columnType"><h3>Field Type</h3></div>
                    <?php
                        $newTable = new Table($currentTable);
                        $fields = $newTable->getTableFields();
                    ?>
                    <?php foreach($fields as $key=>$field):
                            $fieldName = $field;
                            $parameters = $newTable->getFieldParameters($fieldName);
                            $currentFieldType = $newTable->returnFieldType($field);
                            if($key == 0 && $currentFieldType =='foreign'){
                                $currentFieldType = 'id';
                            }
                        ?>
                        <div class="entry">
                            <div class="columnName <?php echo $currentFieldType == 'Unkown' ? 'unknown' : ''; ?>"><span class="fieldName"><?php echo $fieldName; ?></span></div>
                            <div class="columnType <?php echo $currentFieldType == 'Unkown' ? 'unknown' : ''; ?>">
                                <select name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][fieldType]">
                                    <?php foreach($allAvailableFieldTypes as $key=>$fieldType): ?>
                                        <option value="<?php echo $fieldType; ?>" <?php echo $fieldType == $currentFieldType ? 'selected' :''; ?>><?php echo field_name($fieldType); ?></option>
                                        <?php endforeach; ?>
                                </select>
                                <div class="foreignDiv">
                                    <?php if($currentFieldType == 'foreign') : ?>

                                        <select name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][foreignTable]" fieldName="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][foreignField]" class="foreignTable required left" style="margin-top:2px;">

                                            <?php
                                                $foreignTableSearch = $newTable->getForeignTable($fieldName);
                                                echo'<option disabled="disabled" '. (empty($foreignTableSearch) ? 'selected="selected"' : '') .'>Select Table</option>';
                                                foreach($allTables as $curtable=>$s):
                                                ?>
                                                <option value="<?php echo $curtable; ?>" <?php echo ($foreignTableSearch == $curtable) ? 'selected="selected"' : ''; ?>><?php echo $curtable; ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                        <div class="foreignFieldDiv left">
                                            <select class="required" name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][foreignField]" style="margin-top:2px;" <?php echo ($foreignTableSearch !== false) ? '' : 'disabled="disabled"'; ?>>
                                                <option value=''>Select Field</option>
                                                <?php
                                                    if($foreignTableSearch !== false) :
                                                    ?>
                                                    <?php
                                                        $foreignTableNew = new Table($foreignTableSearch);
                                                        $ForeignFields = $foreignTableNew->getTableFields();
                                                        $foreignFieldSearch = $newTable->getForeignField($fieldName);
                                                        foreach($ForeignFields as $key=>$foreignFieldFromTable):
                                                        ?>
                                                        <option value="<?php echo $foreignFieldFromTable; ?>" <?php echo ($foreignFieldSearch == $foreignFieldFromTable) ? 'selected="selected"' : ''; ?>><?php echo $foreignFieldFromTable; ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                            </select>

                                            <label>Multi Foreign:<input name="data[<?php echo $currentTable; ?>][<?php echo $fieldName; ?>][parameters][multi]" value="1" type="checkbox" <?php echo isset($parameters['multi']) ? 'checked="checked"' : '' ; ?> /></label>

                                        </div>
                                        <?php endif; ?>

                                    <?php if($currentFieldType == 'photo_upload') : ?>
                                        <?php
                                            $_GET['fieldName'] = 'data['.$currentTable.']['.$fieldName.']';
                                            include("actions/showPhotoUploadOptions.php");
                                        ?>
                                        <?php endif; ?>

                                    <?php if($currentFieldType == 'thumbnail') : ?>
                                        <?php
                                            $_GET['fieldName'] = 'data['.$currentTable.']['.$fieldName.']';
                                            include("actions/showThumbnailOptions.php");
                                        ?>
                                        <?php endif; ?>

                                    <?php if($currentFieldType == 'limited_textarea') : ?>
                                        <?php
                                            $_GET['fieldName'] = 'data['.$currentTable.']['.$fieldName.']';
                                            include("actions/showLimitedTextAreaOptions.php");
                                        ?>
                                        <?php endif; ?>


                                </div>
                                <div style="position:absolute; left:260px; top:0px;">
                                    <label style="padding-top:-4px;"><input type="checkbox" name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][mandatory]" class="styled" <?php echo (($newTable->isMandatoryField($fieldName,$currentFieldType) == true) ? 'checked' : '' ); ?>/>Mandatory Field</label>
                                </div>

                                <div style="position:absolute; left:390px; top:0px;">
                                    <label style="padding-top:-4px;"><input type="checkbox" name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][active]" class="styled" <?php echo (($newTable->isActiveField($fieldName,$currentFieldType) == true) ? 'checked' : '' ); ?>/>Editable</label>
                                </div>

                                <div style="position:absolute; left:550px; top:0px;">
                                    <label style="padding-top:-4px;"><input type="checkbox" name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][is_visible]" class="styled" <?php echo (($newTable->isVisibleField($fieldName,$currentFieldType) == true) ? 'checked' : '' ); ?>/>Visible Column</label>
                                </div>

                                <?php
                                    $tooltip = $newTable->getTooltip($fieldName);
                                ?>
                                <div style="position:absolute; left:470px; top:0px;">
                                    <label style="padding-top:-4px;"><input type="checkbox" name="tooltipCheckbox" class="styled" <?php echo empty($tooltip) ? '' : 'checked="checked"' ; ?> />Tooltip</label>
                                </div>
                            </div>
                            <div class="clear"></div>

                            <div class="entry tooltipEntry" style="margin-bottom:10px; <?php echo empty($tooltip) ? 'display: none;' : '' ; ?>">
                                <input type="text" name="data[<?php echo $currentTable; ?>][<?php echo $fieldName;?>][tooltip]" style="width:100%;" placeholder="Write Tooltip message" value="<?php echo $tooltip; ?>" />
                            </div>
                        </div>
                        <div class="clear"></div>

                        <div class="clear"></div>
                        <?php endforeach; ?>

                    <div class="clear"></div>

                    <hr />
                    <?php endforeach; ?>


                <br />
                <br />
                <input type="submit" class="submit round drop_shadow" value="Generate CMS" align="center" />
            </form>
        </div>
    </body>
    </html>
