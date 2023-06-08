<?php
header("Content-Type: text/html; charset=utf-8");
require_once('custom-config.php');
require_once(CUSTOM_SERVER_CONFIG);
ob_start();
require_once(LIB_PATH.DS.'loggedin.php');

require_once(LIB_PATH.DS.'site.class.php');

$table = new Table();
$allTables = $table->listAllTables();
$currentPage = basename($_SERVER['REQUEST_URI']);
if(!empty($_GET['table'])){
    $user->allowed($_GET['table']);
}
//Checks if the user has changed the default username and password or not
if($user->passwordIsDefault() && !currentPageIs('administrators.php') && !currentPageIs('administrators_edit.php')) {
    $session->message("Please change the default username and password!");
    redirect_to("administrators.php");
}

$stats = new Statistics();
$site = new Site();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title><?php echo $site->project_name; ?> Administration</title>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo "../".$site->favicon; ?>" />
    <!-- =============== VENDOR STYLES ===============-->
    <!-- FONT AWESOME-->
    <link rel="stylesheet" href="js/vendor/fontawesome/css/font-awesome.min.css">
    <!-- SIMPLE LINE ICONS-->
    <link rel="stylesheet" href="js/vendor/simple-line-icons/css/simple-line-icons.css">
    <!-- ANIMATE.CSS-->
    <link rel="stylesheet" href="js/vendor/animate.css/animate.min.css">
    <!-- WHIRL (spinners)-->
    <link rel="stylesheet" href="js/vendor/whirl/dist/whirl.css">
    <link rel="stylesheet" href="js/vendor/bvalidator/bvalidator.css">
    <link rel="stylesheet" href="js/vendor/select2/dist/css/select2.css">
    <link rel="stylesheet" href="js/vendor/select2/dist/css/select2-bootstrap.css">
    <!-- =============== PAGE VENDOR STYLES ===============-->
    <!-- =============== BOOTSTRAP STYLES ===============-->
    <!-- DATATABLES-->
    <link rel="stylesheet" href="js/vendor/datatables-colvis/css/dataTables.colVis.css">
    <link rel="stylesheet" href="js/vendor/datatables/media/css/dataTables.bootstrap.css">
    <link rel="stylesheet" href="js/vendor/dataTables.fontAwesome/index.css">
    <link rel="stylesheet" href="js/vendor/jquery.datetimepicker.min.css">

    <link rel="stylesheet" href="js/vendor/qtip/qtip.css">

    <link rel="stylesheet" href="css/bootstrap.css" id="bscss">
    <!-- =============== APP STYLES ===============-->
    <link rel="stylesheet" href="css/app.css" id="maincss">
    <link rel="stylesheet" href="css/theme-grind.css" id="maincss">


    <!--    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">-->
    <!--    <link href="https://fonts.googleapis.com/css?family=Titillium+Web" rel="stylesheet">-->

    <script src="js/vendor/jquery/dist/jquery.js"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php require("header_functions.php");?>


</head>

<body>

<div class="wrapper">


<!--<div id="saveResult" class="drop_shadow round"></div>-->
