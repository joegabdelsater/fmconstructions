<!-- top navbar-->
<header class="topnavbar-wrapper">
    <!-- START Top Navbar-->
    <nav role="navigation" class="navbar topnavbar">
        <!-- START navbar header-->
        <div class="navbar-header">
            <a href="#/" class="navbar-brand">
                <div class="brand-logo">
                    <img src="images/logo-header.png" alt="App Logo" class="img-responsive">
                </div>
                <div class="brand-logo-collapsed">
                    <img src="images/logo-header-mobile.png" alt="App Logo" class="img-responsive">
                </div>
            </a>
        </div>
        <!-- END navbar header-->
        <!-- START Nav wrapper-->
        <div class="nav-wrapper">
            <!-- START Left navbar-->
            <ul class="nav navbar-nav">
                <li>
                    <!-- Button used to collapse the left sidebar. Only visible on tablet and desktops-->
                    <a href="#" data-toggle-state="aside-collapsed" class="hidden-xs">
                        <em class="fa fa-navicon"></em>
                    </a>
                    <!-- Button to show/hide the sidebar on mobile. Visible on mobile only.-->
                    <a href="#" data-toggle-state="aside-toggled" data-no-persist="true" class="visible-xs sidebar-toggle">
                        <em class="fa fa-navicon"></em>
                    </a>
                </li>

                <li><a style='color:#fff'><?php echo CMSGEN_TITLE; ?> v<?php echo CMSGEN_VERSION; ?></a></li>
                <?php if(  LIVE_EDIT  ) { ?><li><a href="<?php echo PUBLIC_HTML_SITE.DS; ?>index.php">Switch to live editing</a></li><?php } ?>



                <li class='timerIcon'><a class="logHoursOpen" href="" style='font-size:20px'><em class='fa fa-clock-o'></em></a></li>
            </ul>
            <!-- END Left navbar-->
            <!-- START Right Navbar-->
            <ul class="nav navbar-nav navbar-right">
                <!-- Search icon-->
                <li>
                    <a href="logout.php" >
                        <em class="fa fa-sign-out"></em>
                    </a>
                </li>
                <!-- START Offsidebar button-->
                <li>
                    <a href="#" data-toggle-state="offsidebar-open" data-no-persist="true">
                        <em class="icon-notebook"></em>
                    </a>
                </li>
                <!-- END Offsidebar menu-->
            </ul>
            <!-- END Right Navbar-->
        </div>
        <!-- END Nav wrapper-->
        <!-- START Search form-->
        <!--<form role="search" action="search.html" class="navbar-form">
        <div class="form-group has-feedback">
        <input type="text" placeholder="Type and hit enter ..." class="form-control">
        <div data-search-dismiss="" class="fa fa-times form-control-feedback"></div>
        </div>
        <button type="submit" class="hidden btn btn-default">Submit</button>
        </form>-->
        <!-- END Search form-->
    </nav>
    <!-- END Top Navbar-->
</header>
<!-- sidebar-->
<aside class="aside">
    <!-- START Sidebar (left)-->
    <div class="aside-inner">
        <nav data-sidebar-anyclick-close="" class="sidebar">
            <!-- START sidebar nav-->
            <ul class="nav">
                <!-- Iterates over all sidebar items-->
                <li class="nav-heading ">
                    <span data-localize1="sidebar.heading.HEADER">Menu Navigation</span>
                </li>



                <?php if ($user->user_level == 9) { ?>
                    <li>
                        <a href="<?php echo pageLink('dashboard.php'); ?>" class="<?php echo (strpos($currentPage, 'dashboard') !== false ? 'active' : '' )?>" title="Dashboard" >
                            <em class="icon-speedometer"></em>
                            <span data-localize="sidebar.nav.DASHBOARD">Dashboard</span>
                        </a>
                    </li>

                    <?php $checkExists = new Table(); if($checkExists->tableExists('cmsgen_galleries')) { ?>
                        <li>
                            <a data-toggle='collapse' href="<?php echo pageLink('zip_upload.php'); ?>" class="<?php echo (strpos($currentPage, 'zip_upload') !== false ? 'active' : '' )?>">
                                <span>
                                    Zip Upload
                                </span>
                            </a>
                        </li>
                        <?php } ?>

                    <?php if($checkExists->tableExists('cmsgen_mailing_list')) { ?>
                        <li>
                            <a  data-toggle='collapse' href="<?php echo pageLink('send_mailing_list.php'); ?>" class="<?php echo (strpos($currentPage, 'send_mailing_list') !== false ? 'active' : '' )?>">
                                <span>Send Mailing List</span>
                            </a>
                        </li>
                        <?php } ?>

                    <?php

                    // reorder tables as in language.php

                    $allTables = reorderTable($allTables);

                    $allTablesGrouped = array();

                    foreach ($allTables as $word=>$k) {
                        if (strpos($word,'_') !== false) {
                            // see if other tables have same prefix
                            $othersToo = false;
                            $prefixToCheck = explode('_',$word);
                            $prefixToCheck = $prefixToCheck[0].'_';


                            foreach ($allTables AS $tableName=>$k) {
                                if ($tableName != $word) {
                                    if ( substr($tableName, 0, strlen($prefixToCheck)) === $prefixToCheck){ 

                                        $othersToo = true;
                                    }
                                }
                            }
                            if ($othersToo) {
                                $allTablesGrouped [metaphone($word, 2)][] = $word;
                            } else {// otherwise do nothing
                                $allTablesGrouped [$word][] = $word;
                            }

                        } else {
                            $allTablesGrouped [$word][] = $word;
                        }

                    }


                    foreach($allTablesGrouped as $group=>$tablesSubGroup){
                        // get common prefix
                        $prefix = getArrayCommonPrefix($tablesSubGroup );
                        $prefixId = $prefix.rand();
                        $subTablesCount = count($tablesSubGroup);


                        if ($subTablesCount  > 1 ) {
                            ?>
                            <li>
                                <a href='#<?=$prefixId ;?>' data-prefix='<?=$prefix;?>' data-toggle='collapse' alt="<?=$group;?>" title="<?=$group;?>" >
                                    <div class="pull-right label label-info"><?=$subTablesCount;?></div>
                                    <em class="fa fa-folder-open-o"></em>
                                    <span data-localize="sidebar.nav.DASHBOARD"><?= str_replace("_"," ",ucwords($prefix));?></span>
                                </a> 
                                <ul id='<?=  $prefixId  ;?>' class="nav sidebar-subnav collapse">
                                    <?php  } 

                                $tablesSubGroupSorted = array();
                                foreach($tablesSubGroup as $k=>$tableName){
                                    $tableNameDisplay = $tableName;
                                    if ($subTablesCount > 1) {
                                        $iconDisplay = '';
                                        if ($tableName != $prefix && $tableName != $prefix.'s') {
                                            $tableNameDisplay = str_replace( rtrim($prefix,'_').'s','',$tableName); // locations_cities -> cities
                                            $tableNameDisplay = str_replace($prefix,'',$tableNameDisplay); // location_cities -> cities
                                            $tableNameDisplay = ltrim($tableNameDisplay,'_'); // _cities -> cities
                                        }
                                    }

                                    $tablesSubGroupSorted[$tableName] = $tableNameDisplay;
                                }
                                if ($subTablesCount  > 1 ) {        
                                    asort($tablesSubGroupSorted);
                                }

                                foreach($tablesSubGroupSorted  as $tableName=>$tableNameDisplay){

                                    if(in_array($tableName,$oneRowTables)){ continue; }
                                    if(strpos($tableName,"habtm_") !== false){ continue; }

                                    $iconDisplay = '<em class="fa fa-file-o"></em>';
                                    if ($subTablesCount > 1) {
                                        $iconDisplay = '';
                                    }
                                    ?>
                                    <li>

                                        <a alt="<?=printTableName($tableName);?>" title="<?=printTableName($tableName);?>"  href="<?php echo pageLink('list.php?table='.$tableName); ?>" <?php echo isMenuItemSelected($tableName) ? 'class="active"' : '' ; ?>>
                                            <?=$iconDisplay;?>
                                            <span>
                                                <?php echo printTableName($tableNameDisplay); ?>
                                            </span>
                                        </a> 
                                        <a  href='<?php echo pageLink('generate.php?table='.$tableName);?>'>
                                            <em class="fa fa-plus-square-o"></em>
                                        </a>
                                    </li>

                                    <?php 
                                } 

                                if ($subTablesCount  > 1 ) {
                                    ?>
                                </ul>
                            </li>
                            <?php
                        }
                    }

                    ?>

                    <!-- end menu -->



                    <li>
                        <a data-toggle='collapse' href="<?php echo pageLink('sitemap.php'); ?>" <?php echo isMenuItemSelected('sitemap') ? 'class="active"' : '' ; ?>>
                            <!--                            <span>Site Tree</span>-->
                        </a>
                    </li>

                    <?php } ?>


            </ul>
            <!-- END sidebar nav-->
        </nav>
    </div>
    <!-- END Sidebar (left)-->
</aside>
<!-- offsidebar-->
<aside class="offsidebar hide">
    <!-- START Off Sidebar (right)-->
    <nav>
        <div role="tabpanel">
            <!-- Nav tabs-->
            <ul role="tablist" class="nav nav-tabs nav-justified">
                <li role="presentation" class="active">
                    <a href="#app-settings" aria-controls="app-settings" role="tab" data-toggle="tab">
                        <em class="icon-equalizer fa-lg"></em>
                    </a>
                </li>
                <li role="presentation">
                    <a href="#app-chat" aria-controls="app-chat" role="tab" data-toggle="tab">
                        <em class="icon-user fa-lg"></em>
                    </a>
                </li>

            </ul>
            <!-- Tab panes-->
            <div class="tab-content">
                <div id="app-settings" role="tabpanel" class="tab-pane fade in active">
                    <h3 class="text-center text-thin">Options</h3>
                    <ul class="" >

                        <?php if($user->isAllowed('cmsgen_cpanel_editor_links')) : ?>
                            <li><a href="<?php echo pageLink('generate.php?table=site_options&id=1'); ?>">Configure CMS</a></li>
                            <?php endif; ?>

                        <?php if(HOSTED_WITH_US && $user->user_level >= 9) : ?>
                            <li><a href="<?php echo pageLink('generate.php?table=admin_advanced_settings&id=1'); ?>">Advanced Settings</a></li>
                            <li><a href="<?php echo pageLink('list.php?table=cmsgen_cpanel_links'); ?>">Cpanel Links</a></li>
                            <?php endif; ?>

                        <?php
                        if($user->isAllowed('cmsgen_cpanel_editor_links')) :
                            $array = $table->listAllCpanelLinks();
                            if($array !== false) :
                                foreach($array as $link) :
                                    ?>
                                    <li><a alt="<?=$link['name'];?>" title="<?=$link['name'];?>" href="<?php echo $link['link']; ?>"><?php echo $link['name']; ?></a></li>
                                    <?php
                                    endforeach;
                                endif; // $array != false
                            endif; // IsAllowed() if
                        ?>

                    </ul>
                </div>
                <div id="app-chat" role="tabpanel" class="tab-pane fade">
                    <h3 class="text-center text-thin">Users</h3>
                    <ul>
                        <?php if($user->isAllowed('administrators_edit')) : ?>
                            <li><a href="<?php echo pageLink('administrators.php'); ?>">Edit</a></li>
                            <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <!-- END Off Sidebar (right)-->
</aside>