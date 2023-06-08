
<form action="<?php echo pageLink('actions/delete.php'); ?>" method="post" enctype="multipart/form-data">
    
    <?php if(isset($_GET['action']) && $_GET['action'] == 'search' && isset($_POST['data']['Search'] ) && !empty($_POST['data']['Search'] ) ) : ?>
        <h5>Searching for : </h5><?php
        foreach($_POST['data']['Search'] as $index => $value){
            if(empty($value)){
                continue;   
            }
            echo '<strong>' .printTableName($index) .'</strong> : '. $value .' <br /> ';   
        }
        ?>
        <?php endif; ?>


    <?php include(ADMIN_PATH.DS."common/listTable.php"); ?>
</form>
