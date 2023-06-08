
<div class="panel panel-default" >
    <div class="panel-body" id="list" style="overflow: auto;">
        <table class="table data-table table-striped table-hover">
            <thead>

                <tr class="ui-state-disabled">
                    <th style="text-align: center;width:50px!important">
                        <input type="checkbox" id="select-all" />
                    </th>
                    <th style="text-align: center;width:50px!important">Edit</th>
                    <?php

                    foreach($allFields as $fieldName):

                        if(!$table->isFieldActive($fieldName) || !$table->isVisible($fieldName)){
                            continue;
                        }
                        ?>

                        <th style='text-align: center'><?php echo printTableName($fieldName); ?></th>
                        <?php endforeach; ?>

                </tr>
            </thead>
            <tbody class="content">
                <?php foreach($allRows as $row) : ?>
                    <tr height="30" style="border-bottom:thin solid #eeeee1;line-height:30px;" id="sort_<?php echo $row['id']; ?>">
                        <td style="text-align: center;padding-right: 21px;">

                        <div class='form-group'>
                            <div class="checkbox c-checkbox">
                                <label>
                                    <input type="checkbox" name="ids[]" value="<?php echo $row['id']; ?>"  style='display:none' />
                                    <span class="fa fa-check"></span></label>
                            </div>
                            </div>

                        </td>
                        <td width="30" style="text-align: center;"><a href="<?php echo pageLink('generate.php?table='.$tableName.'&id='.$row['id']); ?>"><em alt="Edit" class="showAddChildrenTooltip fa fa-edit" style="font-size: 20px;"></em></a>

                            <br />

                            <div class="tooltipAddChildrenText">
                                <a href="<?php echo pageLink('generate.php?table='.$tableName.'&id='.$row['id']); ?>">Edit this entry</a>
                                <br />
                                <br />

                                <?php foreach($childTableInformation as $childTable) : ?>
                                    <a style="font-size:10px;" href="<?php echo pageLink('generate.php?table='.$childTable['tableName'].'&'.$childTable['fieldName'].'='.$row['id']); ?>">Add <?php echo printTableName($childTable['tableName']); ?></a> <br />
                                    <?php endforeach; ?>
                                <?php if(VIEW_ON_WEBSITE_LINK) : ?>
                                    <a href="<?php  echo $table->returnFormatedLink($row['id']);   ?>">View on website</a>
                                    <?php endif; ?>
                            </div>
                        </td>

                        <?php
                        $positionColumnNumber = 0;
                        $currentColumnNumber = 0;
                        foreach ($row as $fieldName => $value):

                            $fieldValueDisplay = '';
                            if(!$table->isFieldActive($fieldName)  || !$table->isVisible($fieldName)){
                                continue;
                            }

                            $fieldType = $table->returnFieldType($fieldName);

                            if ( $fieldType == 'position' ){
                                $positionColumnNumber = $currentColumnNumber;
                            }

                            // editable fields
                            $jeditable = '';
                            $nonEditable = array('id','photo_upload','checkbox','date','foreign','thumbnail','textarea','textarea_nostyles','enum','mp3_upload','select');
                            if (!in_array($fieldType,$nonEditable)){
                                $jeditable = 'jeditable';
                            }
                            if ($fieldType == 'checkbox') {
                            	$jeditable = 'jeditableSelect';
							}


                            if($fieldType == 'foreign'){

                                //  $row[$fieldName] = $table->getForeignKeyValue($row[$fieldName],$fieldName);

                                $row[$fieldName] = join(' > ',$table->createAdminBreadCrumb($row[$fieldName],$fieldName));
                                /*     
                                $element = new FormElement($table->getRawTableName(), $fieldName, $row['id']);
                                $fieldValue = '';
                                $fieldValueDisplay = $element->displayFormElement();
                                */

                            }elseif($fieldType == 'colorpicker'){
                                $withHash = strpos($row[$fieldName],'#') !== false ? $row[$fieldName] : '#'.$row[$fieldName];
                                $row[$fieldName] = '<div style="width:30px; display:inline-block; height:30px; background:'. $withHash .'"></div><div style="display:inline-block; left: 10px;    position: relative;   top: -10px;">' .$withHash.'</div>';

                            }elseif($fieldType == 'checkbox'){
                                $row[$fieldName] = $row[$fieldName]==1 ? 'Yes' : 'No';
                            }elseif($fieldType == 'date'){
                                $row[$fieldName] = formatDate($row[$fieldName]);
                            }elseif($fieldType == 'photo_upload'){

                                if(empty($row[$fieldName])){
                                    $row[$fieldName] = 'No image added.';   
                                } else {

                                    $row[$fieldName] = '    <div class="thumbnail-item">
                                    <a href="#"><img src="'.thumbnailLink($row[$fieldName],50,50).'" class="thumbnail"/></a>
                                    <div class="tooltip">
                                    <img src="'.thumbnailLink($row[$fieldName],330,185).'" alt="" width="330" height="185" />
                                    <span class="overlay"></span>
                                    </div>
                                    </div> ';
                                }

                            } else if ( $fieldType == 'thumbnail'){
                                if(empty($row[$fieldName])){
                                    $row[$fieldName] = 'No thumbnail added.';   
                                } else {

                                    $row[$fieldName] = '    <div class="thumbnail-item">
                                    <a href="#"><img src="'.thumbnailLink($row[$fieldName],50,50).'" class="thumbnail"/></a>
                                    <div class="tooltip" style="border:0;outline:0;">
                                    <img src="'.thumbnailLink($row[$fieldName],100,null,false).'" alt=""  />
                                    <span class="overlay"></span>
                                    </div>
                                    </div> ';
                                }
                            }

                            if($fieldType != 'foreign'){
                                $fieldValue = truncateUtf8(strip_tags($row[$fieldName]),80);
                            }else{
                                $fieldValue=$row[$fieldName];
                            }
                            $fieldValueJeditable = '';
                            if ($jeditable != '') { $fieldValueJeditable = $fieldValue;}

                            if($fieldType == 'photo_upload' || $fieldType == 'thumbnail' || $fieldType == 'colorpicker'){
                                $fieldValue = $row[$fieldName];
                            }

                            $fieldValueDisplay = $fieldValue;

                            ?>
                            <td data-coltype="<?php echo $fieldType;?>" align="center" class='<?php echo h($jeditable);?>' id='<?php echo h($fieldName);?>|<?php echo $row['id']?>|<?php echo h($fieldValue);?>'><?php echo $fieldValueDisplay; ?></td>

                            <?php $currentColumnNumber++;?>
                            <?php endforeach; ?>


                    </tr>
                    <?php endforeach; ?>
            </tbody>



        </table><br />
        <?php echo $pagination->displayPagination(); ?>
        <div class="clear"></div>

        <?php if($table->isCrudEnabled()) { ?>
            <div class='list-crud-container'>
                <input type="hidden" name="table" value="<?php echo $tableName; ?>" />


                <input type="hidden" name="token" value="<?php echo CSRF::generateToken(); ?>" />

                <a href="<?php echo pageLink('generate.php?table='.$tableName);?>" type="button" class="btn btn-primary btn-sm btn-success " />Add</a>

                <input type="submit" id="delete" name="delete" class="btn btn-primary btn-sm btn-danger" value='Delete' />
                <input type="button" id='advancedSearchBtn' class="btn btn-primary btn-sm " value='Advanced Search' />
                <input type="button" id='bulkEditBtn' class="btn btn-primary btn-sm " value='Bulk Edit' />
                <input type="button" id="inverse" class="btn btn-primary btn-sm " value='Inverse' />


                <!--<input type="submit" id="delete" name="delete" class="submit round drop_shadow" value="Delete" style="float:left;margin-left:4px;" />

                <input class="submit round drop_shadow" type="button" value="Inverse" id="inverse" style="float:left;margin-left:4px;" />

                <input class="submit round drop_shadow bulkEditBtn" type="button" value="Bulk Edit" id="bulkEditBtn" style="float:left;margin-left:4px;" />

                <input class="submit round drop_shadow advancedSearchBtn" type="button" value="Advanced Search" id="advancedSearchBtn" style="float:left;margin-left:4px;" />-->
            </div>
            <?php } ?>



        <a href="<?php echo pageLink('csvExport.php?table='.$tableName.''); ?>" class="btn btn-labeled btn-info">
            <span class="btn-label">
                <i class="fa fa-exclamation"></i>
            </span>
            Export to CSV
        </a>
    </div>
</div>