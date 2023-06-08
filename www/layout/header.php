<!DOCTYPE HTML>

<?php 

require(dirname(__FILE__) . '/../common/myclass.php');

$main = new Main();
$social = $main->getSocialMedia();
?>

<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">

<!-- Favicons -->
<link rel="shortcut icon" href="favicon.png">
<link rel="apple-touch-icon" href="apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="72x72" href="apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="114x114" href="apple-touch-icon-114x114.png">

<title>FMC Construction</title>

<!-- Styles -->
<link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i|Poppins:300,400,500,600,700" rel="stylesheet">
<link href="./css/style.css" rel="stylesheet" media="screen">
</head>
<body>
<div class="animsition">
  <div class="wrapper boxed">

    <!-- Content CLick Capture-->

    <div class="click-capture"></div>

    <!-- Sidebar Menu-->

    <div class="menu">
      <span class="close-menu icon-cross2 right-boxed"></span>
      <!-- <div class="menu-lang right-boxed">
        <a href="" class="active">Eng</a>
        <a href="">Fra</a>
        <a href="">Ger</a>
      </div> -->
      <ul class="menu-list right-boxed">
        <li class="active">
          <a  href="index.php">Home</a>
          <!-- <ul>
            <li><a href="../light/index.html">Classic</a></li>
            <li><a href="home-fullpage.html">Full page</a></li>
            <li class="active"><a href="index.html">Dark</a></li>
          </ul> -->
        </li>
        <li>
          <a href="works-grid.php">Projects</a>
          <!-- <ul>
            <li><a href="works-grid.html">Grid</a></li>
            <li><a href="works-masonry.html">Masonry</a></li>
            <li><a href="works-carousel.html">Carousel</a></li>
            <li><a href="project-detail.html">Project Detail</a></li>
          </ul> -->
        </li>
        <li>
          <a href="news-listing.php">News</a>
          <!-- <ul>
            <li><a href="news-grid.html">Grid</a></li>
            <li><a href="news-listing.html">Listing</a></li>
            <li><a href="news-masonry.html">Masonry</a></li>
          </ul> -->
        </li>
        <!-- <li>
          <a href="#">Post detail</a>
          <ul>
            <li><a href="post-image.html">Image</a></li>
            <li><a href="post-gallery.html">Gallery</a></li>
            <li><a href="post-video.html">Video</a></li>
            <li><a href="post-right-sidebar.html">Right Sidebar</a></li>
          </ul>
        </li> -->
        <li>
          <a href="contact.php">Contact Us</a>
          <!-- <ul>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
          </ul> -->
        </li>
      </ul>
      <div class="menu-footer right-boxed">
        <div class="social-list">
          <!-- <a href="" class="icon ion-social-twitter"></a> -->
          <a href="<?php echo $social['facebook'];?>" target="_blank" class="icon ion-social-facebook"></a>
          <!-- <a href="" class="icon ion-social-googleplus"></a> -->
          <a href="<?php echo $social['linkedin'];?>" target="_blank" class="icon ion-social-linkedin"></a>
          <!-- <a href="" class="icon ion-social-dribbble-outline"></a> -->
          <a href="<?php echo $social['instagram'];?>" target="_blank" class="icon ion-social-instagram"></a>
        </div>
        <!-- <div class="copy">© Bauhaus 2017. All Rights Reseverd<br> Design by LoganCee</div> -->
      </div>
    </div>

    <!-- Navbar -->

    <header class="navbar boxed js-navbar">
      
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
      <p style="position:absolute; margin-top:-5px;margin-left:-50px;color:white;">Menu</p>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>

      <a class="brand" href="index.php">
        <img alt="" src="../images/fmc_white.png" style="height:5rem; margin-top:-15px;"/>
        <!-- <div class="brand-info">
          <div class="brand-name">FM</div>
          <div class="brand-text">Constructions</div>
        </div> -->
      </a>

      <div class="social-list hidden-xs">
        <!-- <a href="" class="icon ion-social-twitter"></a> -->
        <a href="<?php echo $social['facebook'];?>" target="_blank" class="icon ion-social-facebook"></a>
        <!-- <a href="" class="icon ion-social-googleplus"></a> -->
        <a href="<?php echo $social['linkedin'];?>" target="_blank" class="icon ion-social-linkedin"></a>
        <!-- <a href="" class="icon ion-social-dribbble-outline"></a> -->
        <a href="<?php echo $social['instagram'];?>" target="_blank" class="icon ion-social-instagram"></a>
      </div>

      <div class="navbar-spacer hidden-sm hidden-xs"></div>

      <address class="navbar-address hidden-sm hidden-xs"> Call Us: <span class="text-dark"> (+961) 3 626 109 / (+961) 3 949 988</span></address>
    </header>

    <!-- Jumbotron -->
