<?php

require('layout/header.php');
$main = new Main();
$slider = $main->getHomePageSliders();
$about = $main->getAbout();
$projects = $main->getProjects();
$types = $main->getTypes();
$news = $main->getNews(true);

?>

<style>
  .tp-mask-wrap{
    overflow: visible !important;
   
  }

  .tp-splitted > div{
    text-shadow: 1px 1px #c0c0c0;
  }
  </style>
    <main class="jumbotron">

      <!-- Start revolution slider -->

      <div class="rev_slider_wrapper">
        <div id="rev_slider" class="rev_slider tp-overflow-hidden fullscreenbanner">
          <ul>

            <!-- Slide 1 -->
            <?php foreach($slider as $slide){ ?>
            <li  data-transition='slideleft' data-slotamount='default' data-masterspeed="1000" data-fsmasterspeed="1000">

              <!-- Main image-->

              <img src="<?php echo $slide['image']?>" data-bgparallax="5"  alt="" data-bgposition="center 0" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg">

              <!-- Layer 1 -->

              <div class="tp-caption tp-shape tp-shapewrapper  tp-resizeme"
                data-x="['left']" data-hoffset="['100']"
                data-y="['middle','middle','middle','middle']" data-voffset="['-250']"
                data-width="270"
                data-height="5"
                data-whitespace="nowrap"
                data-type="shape"
                data-responsive_offset="on"
                data-frames='[{"from":"x:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;","speed":1000,"to":"o:1;","delay":0,"ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"to":"opacity:0;","ease":"nothing"}]'
                data-textAlign="['left','left','left','left']"
                data-paddingtop="[0,0,0,0]"
                data-paddingright="[0,0,0,0]"
                data-paddingbottom="[0,0,0,0]"
                data-paddingleft="[0,0,0,0]"

                style="background-color:#cee002;"> </div>

              <!-- Layer 2 -->

              <div class="tp-caption tp-shape tp-shapewrapper  tp-resizeme"
                data-x="['left']" data-hoffset="['370']"
                data-y="['middle','middle','middle','middle']" data-voffset="['19']"
                data-width="5"
                data-height="544"
                data-whitespace="nowrap"
                data-type="shape"
                data-responsive_offset="on"
                data-frames='[{"from":"y:[-100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;","speed":1000,"to":"o:1;","delay":600,"ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"to":"opacity:0;","ease":"nothing"}]'
                data-textAlign="['left','left','left','left']"
                data-paddingtop="[0,0,0,0]"
                data-paddingright="[0,0,0,0]"
                data-paddingbottom="[0,0,0,0]"
                data-paddingleft="[0,0,0,0]"
                style="background-color:#cee002;"> </div>


              <!-- Layer 3 -->

              <div class="tp-caption tp-shape tp-shapewrapper  tp-resizeme"
                data-x="['left']" data-hoffset="['100']"
                data-y="['middle','middle','middle','middle']" data-voffset="['289']"
                data-width="270"
                data-height="5"
                data-whitespace="nowrap"
                data-type="shape"
                data-responsive_offset="on"
                data-frames='[{"from":"x:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;","speed":1000,"to":"o:1;","delay":1200,"ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"to":"opacity:0;","ease":"nothing"}]'
                data-textAlign="['left','left','left','left']"
                data-paddingtop="[0,0,0,0]"
                data-paddingright="[0,0,0,0]"
                data-paddingbottom="[0,0,0,0]"
                data-paddingleft="[0,0,0,0]"

                style="background-color:#cee002;"> </div>


              <!-- Layer 4 -->

              <div class="tp-caption tp-shape tp-shapewrapper  tp-resizeme"
                data-x="['left']" data-hoffset="['100']"
                data-y="['middle','middle','middle','middle']" data-voffset="['19']"
                data-width="5"
                data-height="544"
                data-whitespace="nowrap"
                data-type="shape"
                data-responsive_offset="on"
                data-frames='[{"from":"y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;","speed":1000,"to":"o:1;","delay":1800,"ease":"Power3.easeInOut"},{"delay":"wait","speed":300,"to":"opacity:0;","ease":"nothing"}]'
                data-textAlign="['left','left','left','left']"
                data-paddingtop="[0,0,0,0]"
                data-paddingright="[0,0,0,0]"
                data-paddingbottom="[0,0,0,0]"
                data-paddingleft="[0,0,0,0]"
                style="background-color:#cee002;"> </div>

              <!-- Layer 5 -->

              <div class="slider-title tp-caption tp-resizeme"
                data-x="['left']" data-hoffset="['156']"
                data-y="['middle','middle','middle','middle']" data-voffset="['-30']"
                data-textAlign="['left']"
                data-fontsize="['72', '63','57','50']"
                data-lineheight="['72','68', '62','54']"
                data-height="none"
                data-whitespace="nowrap"
                data-transform_idle="o:1;"
                data-transform_in="x:[-155%];z:0;rX:0deg;rY:0deg;rZ:0deg;sX:1;sY:1;skX:0;skY:0;s:2000;e:Power2.easeInOut;"
                data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                data-mask_in="x:50px;y:0px;s:inherit;e:inherit;"
                data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                data-start="500"
                data-splitin="chars"
                data-splitout="none"
                data-responsive_offset="on"
                data-elementdelay="0.05" style="font-weight:600; letter-spacing:-0.05em;"> <?php echo $slide['description'];?>
              </div>


              <!-- Layer 6 -->

              <div class="slider-title tp-caption"
                data-x="['left']" data-hoffset="['156']"
                data-y="['middle','middle','middle','middle']" data-voffset="['-170']"
                data-textAlign="['left']"
                data-fontsize="['18']"
                data-lineheight="['20']"
                data-height="none"
                data-whitespace="nowrap"
                data-transform_idle="o:1;"
                data-transform_in="x:[155%];z:0;rX:0deg;rY:0deg;rZ:0deg;sX:1;sY:1;skX:0;skY:0;s:2000;e:Power2.easeInOut;"
                data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                data-mask_in="x:50px;y:0px;s:inherit;e:inherit;"
                data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                data-start="1000"
                data-splitin="chars"
                data-splitout="none"
                data-responsive_offset="on"
                data-elementdelay="0.05" style="font-weight:600; letter-spacing:0.1em; text-transform:uppercase;"><?php echo $slide['small_text']; ?>
              </div>

              <!-- Layer 7 -->

              <div class="slider-title tp-caption"
                data-x="['left']" data-hoffset="['156']"
                data-y="['middle','middle','middle','middle']" data-voffset="['230']"
                data-textAlign="['left']"
                data-fontsize="['18']"
                data-lineheight="['20']"
                data-height="none"
                data-whitespace="nowrap"
                data-transform_idle="o:1;"
                data-transform_in="x:[-105%];z:0;rX:0deg;rY:0deg;rZ:0deg;sX:1;sY:1;skX:0;skY:0;s:2000;e:Power2.easeInOut;"
                data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;"
                data-mask_in="x:50px;y:0px;s:inherit;e:inherit;"
                data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                data-start="1500"
                data-splitin="none"
                data-splitout="none"
                data-responsive_offset="on"
                data-elementdelay="0.05" style="font-weight:600;">
                <?php if($slide['link'] !== ""){?>
                  <a href="<?php echo $slide['link'];  ?>" class="link-arrow">See project <i class="icon ion-ios-arrow-thin-right"></i>
                <?php } ?>
                </a>
              </div>
            </li>

            <?php } ?>
          </ul>
        </div>
      </div>
    </main>

    <div class="content">

      <!-- Section About -->

      <section class="section-about">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <strong class="section-subtitle">about us</strong>
              <h2 class="section-title section-about-title"> <?php echo $about['title']?> </h2>
              <p><?php echo $about['description']?></p>
              <div class="experience-box">
                <div class="experience-border"></div>
                <div class="experience-content">
                  <div class="experience-number"><?php echo $about['year']?></div>
                  <div class="experience-info wow fadeInDown">Years<br>Experience<br>Working</div>
                </div>
              </div>
            </div>
            <div class="col-md-5 col-md-offset-1">
              <div class="dots-image">


                <img alt="" class="about-img img-responsive" src="<?php echo $about['image']?>">
                <div class="dots"></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Section Projects -->

      <section class="section-projects section">
        <div class="container">
          <div class="row">
            <div class="col-lg-5">
               <h2 class="section-title">Latest Projects</h2>
            </div>
            <div class="col-lg-7">
              <div class="filter-content">
                <ul class="filter-carousel filter pull-lg-right js-filter-carousel">
                  <li class="active"><a href="#" class="all" data-filter="*">All</a></li>
                  <?php foreach($types as $type){ ?>
                  <li><a href="#" data-filter=".type<?php echo $type['id']; ?>"><?php echo $type['name']; ?> </a></li>
                <?php } ?>
                  <!-- <li><a href="#" data-filter=".interior-exterior">Interior & Exterior </a></li> -->
                </ul>
                <a href="works-grid.php" class="view-projects">View All Projects</a>
              </div>
            </div>
          </div>
        </div>
        <div class="project-carousel owl-carousel">
        <?php foreach($projects as $project){ ?>
          <div class="project-item item-shadow type<?php echo $project['type_id'] ?>" >
            <div alt="" class="img-responsive" src="<?php echo $project['cover_image']?>" style="min-height:574px;min-width: 426px ;background-image:url('<?php echo $project['cover_image']?>'); background-position:center center; background-repeat:no-repeat; background-size:cover"> </div>
            <!-- <img alt="" class="img-responsive" src="images/projects/1-426x574.jpg"> -->

            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title"><?php echo $project['title']; ?></h3>
                <p class="project-description"><?php echo strip_tags($project['description']); ?></p>
              </div>
            </div>
            <a href="project-detail.php?project=<?php echo $project['id'];?>" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div>
        <?php } ?>
          <!-- <div class="project-item item-shadow building">
            <img alt="" class="img-responsive" src="images/projects/2-426x574.jpg">
            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title">Ocean<br>Museum<br>Italy</h3>
                <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
              </div>
            </div>
            <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div>
          <div class="project-item item-shadow building">
            <img alt="" class="img-responsive" src="images/projects/3-426x574.jpg">
            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title">Milko<br>Co-Working<br>Building</h3>
                <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
              </div>
            </div>
            <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div>
          <div class="project-item item-shadow building">
            <img alt="" class="img-responsive" src="images/projects/4-426x574.jpg">
            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title">Redesign<br>Interior For<br>Villa</h3>
                <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
              </div>
            </div>
            <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div>
          <div class="project-item item-shadow interior-exterior">
            <img alt="" class="img-responsive" src="images/projects/5-426x574.jpg">
            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title">Wooden<br>Hozirontal<br>Villa</h3>
                <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
              </div>
            </div>
            <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div>
          <div class="project-item item-shadow interior-exterior">
            <img alt="" class="img-responsive" src="images/projects/6-426x574.jpg">
            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title">Small<br>House Near<br>Wroclaw</h3>
                <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
              </div>
            </div>
            <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div>
          <div class="project-item item-shadow interior-exterior">
            <img alt="" class="img-responsive" src="images/projects/7-426x574.jpg">
            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title">Bellecomde<br>Holiday<br>Residence</h3>
                <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
              </div>
            </div>
            <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div>
          <div class="project-item item-shadow interior-exterior">
            <img alt="" class="img-responsive" src="images/projects/8-426x574.jpg">
            <div class="project-hover">
              <div class="project-hover-content">
                <h3 class="project-title">Cubic<br>Inter Mesuem<br>In Rome</h3>
                <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
              </div>
            </div>
            <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
          </div> -->
        </div>
      </section>


      <!-- Section Clients -->

    <!-- <section class="section-clients section bg-dots">
        <div class="container">
          <h2 class="section-title">From Great Our Clients</h2>
          <div class="client-carousel owl-carousel">
            <div class="client-carousel-item">
              <img alt="" class="client-img" src="images/clients/1-92x92.jpg">
              <div class="client-box">
                <img alt="" class="image-quote" src="images/image-icons/icon-quote.png">
                <div class="client-title">
                  <span class="client-name">Adam Stone</span>
                  <span class="client-company">/ CEO at Google INC</span>
                </div>
                <p class="client-description">Sed elit quam, iaculis sed semper sit amet udin vitae nibh. at magna akal semperFusce commodo molestie luctus.Lorem ipsum Dolor tusima olatiup.</p>
              </div>
            </div>
            <div class="client-carousel-item">
              <img alt="" class="client-img" src="images/clients/2-92x92.jpg">
              <div class="client-box">
                <img alt="" class="image-quote" src="images/image-icons/icon-quote.png">
                <div class="client-title">
                  <span class="client-name">Anabella Kleva </span>
                  <span class="client-company">/ Managerment at Envato</span>
                </div>
                <p class="client-description">Sed elit quam, iaculis sed semper sit amet udin vitae nibh. at magna akal semperFusce commodo molestie luctus.Lorem ipsum Dolor tusima olatiup.</p>
              </div>
            </div>
            <div class="client-carousel-item">
              <img alt="" class="client-img" src="images/clients/1-92x92.jpg">
              <div class="client-box">
                <img alt="" class="image-quote" src="images/image-icons/icon-quote.png">
                <div class="client-title">
                  <span class="client-name">Adam Stone</span>
                  <span class="client-company">/ CEO at Google INC</span>
                </div>
                <p class="client-description">Sed elit quam, iaculis sed semper sit amet udin vitae nibh. at magna akal semperFusce commodo molestie luctus.Lorem ipsum Dolor tusima olatiup. Sed elit quam, iaculis sed semper sit amet udin vitae nibh</p>
              </div>
            </div>
            <div class="client-carousel-item">
              <img alt="" class="client-img" src="images/clients/2-92x92.jpg">
              <div class="client-box">
                <img alt="" class="image-quote" src="images/image-icons/icon-quote.png">
                <div class="client-title">
                  <span class="client-name">Adam Stone</span>
                  <span class="client-company">/ CEO at Google INC</span>
                </div>
                <p class="client-description">Sed elit quam, iaculis sed semper sit amet udin vitae nibh. at magna akal semperFusce commodo molestie luctus.Lorem ipsum Dolor tusima olatiup.</p>
              </div>
            </div>
          </div>
          <div class="partner-carousel owl-carousel">
            <div class="partner-carousel-item">
              <img alt="" src="images/partners/1.png">
            </div>
            <div class="partner-carousel-item">
              <img alt="" src="images/partners/2.png">
            </div>
            <div class="partner-carousel-item">
              <img alt="" src="images/partners/3.png">
            </div>
            <div class="partner-carousel-item">
              <img alt="" src="images/partners/4.png">
            </div>
            <div class="partner-carousel-item">
              <img alt="" src="images/partners/5.png">
            </div>
          </div>
        </div>
    </section>  -->

      <!-- Section News -->

      <section class="section-news section">
        <div class="container">
          <h2 class="section-title">Latest News <a href="news-listing.php" class="link-arrow-2 pull-right">All Articles <i class="icon ion-ios-arrow-right"></i></a></h2>
          <div class="news-carousel owl-carousel">
              <?php foreach ($news as $info){ ?>  



            <div class="news-item" style="min-height: 370px;min-width: 370px ;background-image:url('<?php echo $info['cover_image']; ?>'); background-position:center center; background-repeat:no-repeat; background-size:cover"> 
        <!-- <img alt="" src="images/news/2-370x370.jpg" /> -->

              <div class="news-hover">
                <div class="hover-border"><div></div></div>
                <div class="content">
                  <div class="time"><?php echo $info['date']?></div>
                  <h3 class="news-title"><?php echo $info['title']?></h3>
                  <p class="news-description"><?php echo $info['description']?></p>
                </div>
                <a class="read-more" href="post-gallery.php?news=<?php echo $info['id']; ?>">Continue</a>
              </div>
            </div>
        <?php } ?>

            <!-- <div class="news-item">
              <img alt="" src="images/news/2-370x370.jpg">
              <div class="news-hover">
                <div class="hover-border"><div></div></div>
                <div class="content">
                  <div class="time">Dec 15th, 2016</div>
                  <h3 class="news-title">Discover Architecture Of Bario</h3>
                  <p class="news-description">Lorem ipsum dolor sit amet, consect etur adipiscing elit. Mauris vel auctorol est. Integer nunc ipsum...</p>
                </div>
                <a class="read-more" href="post-gallery.php">Continue</a>
              </div>
            </div> -->

            <!-- <div class="news-item">
              <img alt="" src="images/news/3-370x370.jpg">
              <div class="news-hover">
                <div class="hover-border"><div></div></div>
                <div class="content">
                  <div class="time">Dec 15th, 2016</div>
                  <h3 class="news-title">Discover Architecture Of Bario</h3>
                  <p class="news-description">Lorem ipsum dolor sit amet, consect etur adipiscing elit. Mauris vel auctorol est. Integer nunc ipsum...</p>
                </div>

                <a class="read-more" href="post-gallery.php">Continue</a>
              </div>
            </div> -->
          </div>
        </div>
      </section>
    </div>


    <?php
    require('layout/footer.php');
    ?>
