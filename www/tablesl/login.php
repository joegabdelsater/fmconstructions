<?php
require_once('common/custom-config.php');
require_once(CUSTOM_SERVER_CONFIG);

require_once(LIB_PATH.DS.'user.class.php');
require_once(LIB_PATH.DS.'session.class.php');
$user = new User();

// try to login when form submitted

if($session->is_logged_in()) {
    redirect_to("dashboard.php");
}
if (isset($_POST) && !empty($_POST)) { // Form has been submitted.

    try {
        CSRF::validate($_POST['token']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);


        // Check database to see if username/password exist.
        $found_user = User::authenticate($username, $password);

        
        if ($found_user) {

            //Login the user
            $session->login($found_user);
            $session->message("Logged in!");

            redirect_to("dashboard.php");
        } else {
            //User not found
            $session->message("Username/Password combination wrong!<br /><br />");

            redirect_to("login.php");
        }
    } catch(Exception $e){
        $session->message("Invalid request!<br /><br />");
        redirect_to("login.php");
    }
} else { // Form has not been submitted.
    $username = "";
    $password = "";
}

$site = new Site();
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
         <title><?php echo $site->project_name; ?> Administration</title>

        <!-- =============== VENDOR STYLES ===============-->
        <!-- FONT AWESOME-->
        <link rel="stylesheet" href="js/vendor/fontawesome/css/font-awesome.min.css">
        <!-- SIMPLE LINE ICONS-->
        <link rel="stylesheet" href="js/vendor/simple-line-icons/css/simple-line-icons.css">
        <!-- =============== BOOTSTRAP STYLES ===============-->
        <link rel="stylesheet" href="css/bootstrap.css" id="bscss">
        <!-- =============== APP STYLES ===============-->
        <link rel="stylesheet" href="css/app.css" id="maincss">
         <link rel="stylesheet" href="css/theme-grind.css" id="maincss">

    </head>

    <body>
        <div class="wrapper">
            <div class="block-center mt-xl wd-xl">
                <!-- START panel-->
                <div class="panel panel-dark panel-flat">
                    <div class="panel-heading text-center">
                        <a href="#">
                            <img src="images/logo-outside.png" alt="Image" class="block-center img-rounded">
                        </a>
                    </div>
                    <div class="panel-body" style='padding-bottom:0'>
                        <p class="text-center pv">SIGN IN TO CONTINUE.</p>
                        <form role="form" data-parsley-validate="" novalidate="" class="mb-lg" style='margin-bottom: 0!important;' method="post">
                            <div class="form-group has-feedback">
                                <input id="" type="text" placeholder="Enter username" autocomplete="off" required class="form-control" name='username'>
                                <span class="fa fa-user form-control-feedback text-muted"></span>
                            </div>
                            <div class="form-group has-feedback">
                                <input id="" type="password" placeholder="Password" required class="form-control" name='password'>
                                <span class="fa fa-lock form-control-feedback text-muted"></span>
                            </div>
                            <div class="clearfix">
                                <div class="checkbox c-checkbox pull-left mt0">
                                    <label>
                                        <input type="checkbox" value="" name="remember">
                                        <span class="fa fa-check"></span>Remember Me</label>
                                </div>
                                <!--<div class="pull-right"><a href="forget_password.php" class="text-muted">Forgot your password?</a>
                                </div>-->
                            </div>
                            <input type="hidden" name="token" value="<?php echo CSRF::generateToken(); ?>" />
                            <input type="submit" class="btn btn-block btn-primary mt-lg" value='Login' />
                            <br>
                        </form>

                    </div>
                </div>
                <!-- END panel-->
                <div class="p-lg text-center">
                    <span>&copy;</span>
                    <span><?= date('Y');?> <?php echo $site->project_name; ?></span>

                </div>
            </div>
        </div>
        <!-- =============== VENDOR SCRIPTS ===============-->
        <!-- MODERNIZR-->
        <script src="../vendor/modernizr/modernizr.custom.js"></script>
        <!-- JQUERY-->
        <script src="../vendor/jquery/dist/jquery.js"></script>
        <!-- BOOTSTRAP-->
        <script src="../vendor/bootstrap/dist/js/bootstrap.js"></script>
        <!-- STORAGE API-->
        <script src="../vendor/jQuery-Storage-API/jquery.storageapi.js"></script>
        <!-- PARSLEY-->
        <script src="../vendor/parsleyjs/dist/parsley.min.js"></script>
        <!-- =============== APP SCRIPTS ===============-->
        <script src="js/app.js"></script>
    </body>

</html>
