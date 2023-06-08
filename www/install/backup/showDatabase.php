<?php
    require_once('../../server/api-adodb/config.php');

    if(!empty($_POST['database'])){
        $databaseName = $_POST['database'];
        overwriteDBLine($databaseName);
        //die;
        redirect_to("showDatabase.php?database=".$databaseName);
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
        <link href="<?php echo ADMIN_PATH_HTML.DS; ?>css/styles.css" media="all" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="<?php echo ADMIN_PATH_HTML.DS; ?>js/jquery.js"></script>
        <script type="text/javascript" src="<?php echo ADMIN_PATH_HTML.DS; ?>js/scripts.js"></script>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <div style="width:60%; background:#fff; padding:10px; margin:20px auto;border:thin solid #dcc6c6;" class="round drop_shadow" id="install">
            <a href="../install/"><img src="<?php echo ADMIN_PATH_HTML.DS; ?>images/logo-inside.png"  /></a><br /><br />
            <h1>Database: <?php echo $databaseName; ?></h1><br />
            <form action="go.php" method="post">
                <input type="submit" class="submit round drop_shadow" value="Generate CMS" /><br><br>
                <hr />
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
                    <label><input type="checkbox" name="disableCRUD-<?php echo $currentTable; ?>" class="styled" <?php $newCurTable = new Table($currentTable); if(!$newCurTable->isCrudEnabled()) echo 'checked="checked"'; ?>  /> Disable Add/Edit/Delete</label>
                    <label style="margin-left:50px;">Bulk Inserts
                        <select name="bulkInsert-<?php echo $currentTable; ?>" style="min-width:0;">
                            <?php for($numberOfInserts = 0;$numberOfInserts<=10;$numberOfInserts++){
                                    echo'<option value="'.$numberOfInserts.'">'.$numberOfInserts.'</option>';
                                }
                            ?>
                        </select> rows</label><br>
                    <div class="clear"></div>
                    <div class="columnName"><h3>Field Name</h3></div>
                    <div class="columnType"><h3>Field Type</h3></div>
                    <?php
                        $newTable = new Table($currentTable);
                        $fields = $newTable->getTableFields();
                    ?>
                    <?php foreach($fields as $key=>$field):
                            $fieldName = $field;
                            $currentFieldType = $newTable->returnFieldType($field);
                            if($key == 0 && $currentFieldType =='foreign'){
                                $currentFieldType = 'id';
                            }
                        ?>
                        <div class="entry">
                            <div class="columnName <?php echo $currentFieldType == 'Unkown' ? 'unknown' : ''; ?>"><span class="fieldName"><?php echo $fieldName; ?></span></div>
                            <div class="columnType <?php echo $currentFieldType == 'Unkown' ? 'unknown' : ''; ?>">
                                <select name="<?php echo $currentTable.'--'.$fieldName; ?>">
                                    <?php foreach($allAvailableFieldTypes as $key=>$fieldType): ?>
                                        <option value="<?php echo $fieldType; ?>" <?php echo $fieldType == $currentFieldType ? 'selected' :''; ?>><?php echo field_name($fieldType); ?></option>
                                        <?php endforeach; ?>
                                </select>
                                <div class="foreignDiv">
                                    <?php if($currentFieldType == 'foreign') : ?>

                                        <select name="foreignTable <?php echo $fieldName; ?>" fieldName="<?php echo $currentTable.'--'.$fieldName; ?>" class="foreignTable left" style="margin-top:2px;">

                                            <?php
                                                $foreignTableSearch = $newTable->getForeignTable($fieldName);
                                                echo'<option disabled="disabled" '. (empty($foreignTableSearch) ? 'selected="selected"' : '') .'>Select Table</option>';
                                                foreach($allTables as $curtable=>$s):
                                                ?>
                                                <option value="<?php echo $curtable; ?>" <?php echo ($foreignTableSearch == $curtable) ? 'selected="selected"' : ''; ?>><?php echo $curtable; ?></option>
                                                <?php endforeach; ?>
                                        </select>
                                        <div class="foreignFieldDiv left">
                                            <select name="foreignField <?php echo $fieldName ?>" style="margin-top:2px;" <?php echo ($foreignTableSearch !== false) ? '' : 'disabled="disabled"'; ?>>
                                                <option>Select Field</option>
                                                <?php
                                                    if($foreignTableSearch !== false) :
                                                    ?>
                                                    <?php
                                                        $foreignTableNew = new Table($foreignTableSearch);
                                                        $ForeignFields = $foreignTableNew->getTableFields();
                                                        $foreignFieldSearch = $newTable->getForeignField($fieldName);
                                                        foreach($ForeignFields as $key=>$foreignFieldFromTable):
                                                        ?>
                                                        <option value="<?php echo $foreignTableSearch.'--'.$foreignFieldFromTable; ?>" <?php echo ($foreignFieldSearch == $foreignFieldFromTable) ? 'selected="selected"' : ''; ?>><?php echo $foreignFieldFromTable; ?></option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                    <?php if($currentFieldType == 'photo_upload') : ?>
                                        <?php
                                            $_GET['fieldName'] = $currentTable.'--'.$fieldName;
                                            include("actions/showPhotoUploadOptions.php");
                                        ?>
                                        <?php endif; ?>
                                </div>
                                <div style="position:absolute; left:260px; top:0px;">
                                    <label style="padding-top:-4px;"><input type="checkbox" name="cmsgen_mandatory--<?php echo $currentTable.'--'.$fieldName;  ?>" class="styled" <?php echo (($newTable->isMandatoryField($fieldName,$currentFieldType) == true) ? 'checked' : '' ); ?>/>Mandatory Field</label>
                                </div>

                                <div style="position:absolute; left:390px; top:0px;">

                                    <label style="padding-top:-4px;"><input type="checkbox" name="cmsgen_active--<?php echo $currentTable.'--'.$fieldName;  ?>" class="styled" <?php echo (($newTable->isActiveField($fieldName,$currentFieldType) == true) ? 'checked' : '' ); ?>/>Visible</label>

                                </div>

                            </div>
                            <div class="clear"></div>
                        </div>
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