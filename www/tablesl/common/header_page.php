<div class="content-heading">
<?php if(!empty($message)) : ?>
    <div class="message round drop_shadow <?php echo isSuccess($session->message()) ? 'success' : 'fail'; ?>">
        <?php echo str_replace('@success@','',$session->message()); ?>
    </div>
    <br><br>
    <?php endif; ?>
    
    <?=$pageTitle;?>
    <?php
    if ( basename($_SERVER['PHP_SELF']) == 'dashboard.php') { ?>
        <small data-localize1="dashboard.WELCOME">
            <b>Howdy <?php echo $user->name(); ?></b> -  <?php echo date('l jS \of F Y'); ?>
        </small>
        <?php } ?>
</div>