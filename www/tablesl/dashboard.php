<?php require("common/header.php");?>
<?php require("common/menu.php");?>

<?php
/*$log = new Log();
$allLogs = $log->findAll();
$stats = new Statistics(false);*/
?>
<!-- Main section-->
<section>
    <!-- Page content-->
    <div class="content-wrapper">
        <?php $pageTitle = "Dashhboard"; require('common/header_page.php');?>

        

        <?php

        if ($user->user_level == 9) {
            require("dashboard_admin.php");
        }
        else if ($user->level == 5) {
        }
        else if ($user->user_level == 3) {
            
        }
        ?>

    </div>
</section>

<?php require("common/footer.php");?>