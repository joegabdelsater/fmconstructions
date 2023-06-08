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

<!DOCTYPE>
<html lang='en'>
    <head>
        <title>Login to <?php echo $site->project_name; ?></title>

        <!--<link href="<?php echo ADMIN_PATH_HTML.DS; ?>css/styles.css" media="all" rel="stylesheet" type="text/css" />-->
<script type="text/javascript" src="css/metro/js/jquery-1.9.0.min.js"></script>
        <?php require("common/metro_includes.php");?>
        
        <!--<script type="text/javascript" src="<?php echo ADMIN_PATH_HTML.DS; ?>js/actions.js"></script>-->
        <script type="text/javascript" src="<?php echo ADMIN_PATH_HTML.DS; ?>js/scripts.js"></script>
        

        
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body class="metrouicss blueBg">
        <div class="page">
            <div class="page-region">
                <div class="page-region-content">

                    <div style='text-align: center;margin:80px 0 40px 0'>
                        <img src="images/logo-outside.png" />
                    </div>
                    <div id="loginPage" style='text-align: center; width:350px;margin:0 auto;'>
                        <form action="" method="post">
                            <?php echo $message; ?><br />

                            <div class="input-control text">
                                <input tabindex='1' type="text" name="username" placeholder="Username" />
                                <button class="btn-clear"></button>
                            </div>

                            <div class="input-control password">
                                <input tabindex="2" type="password" name="password" placeholder="Password" />
                                <button class="btn-reveal"></button>
                            </div>

                            <label class="input-control checkbox" id="loginRemember">
                                <input type="checkbox" name='remember'>
                                <span class="helper">Remember me for one week</span>
                            </label>

                            <br><br>


                            <input class="submit round drop_shadow" type="button" onClick="location.href='forgot_password.php'" name="forgot_password" style="background-color: #fff; color:#383838; border: 0; font-size:11px;" value="Forgot Password" />
                            <input class="submit round drop_shadow" type="submit" name="submit" value="Login" style="min-width:100px; border: 0; font-size:11px;" />

                            <input type="hidden" name="token" value="<?php echo CSRF::generateToken(); ?>" />
                        </form>

                        <br><br><br><br>
                        <a style="color:#666; text-decoration:none;" href="../index.php">GO TO WEBSITE</a>

                    </div>
                </div>
            </div>
        </div>
    </body>
</html>