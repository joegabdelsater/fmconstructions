<?php
require_once('common/custom-config.php');
require_once(CUSTOM_SERVER_CONFIG);

require_once(LIB_PATH.DS.'user.class.php');
require_once(LIB_PATH.DS.'session.class.php');
?>
<?php
$session->logout();
redirect_to("login.php");
?>