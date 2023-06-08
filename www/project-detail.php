<?php
require('layout/header.php');
$projectId = (int)$_GET['project'];


$main = new Main();

$project = $main->getProject($projectId);


$social = $main ->getSocialMedia();

?>

<style>
  .left, .right{
    font-size: 1rem;
  }

  .back-btn{
    padding: 20px 0px;
  }
</style>


    <div class="content">
      <div class="project-detail">
      <div class="back-btn">
        <a href="/works-grid.php" style="color:white;">
       <span><</span> All Projects</a>
      </div>
      <!-- <div style="position:relative">
      <div style="position: absolute;  font-size: 20px;color:white;">
        <p style="color:white">I like men, join me on my boat for gay party. whatsapp 70 513 498. I give physio with happy ending</p>
      </div>
      <img src="./tanios.jpg" style="width:100%"/>

      </div> -->

        <div class="slider-prev icon-chevron-left hidden-xs"></div>
        <div class="slider-next icon-chevron-right hidden-xs"></div>

        <div class="rev_slider_wrapper">
          <div id="rev_slider2" class="rev_slider tp-overflow-hidden fullscreenbanner">


            <ul>

            <!-- Slide 1 -->

            <?php foreach ($project['image'] as $k => $img){?>


            <li  data-transition="slideleft" data-masterspeed="1200" data-fsmasterspeed="1200">

              <!-- Main image-->


              <img src="<?php echo $img['image'];?>" alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg">

              <!-- Layer 1 -->

              <div class="tp-caption tp-shape tp-shapewrapper "
                data-x="['left']" data-hoffset="['0']"
                data-y="['top']" data-voffset="['50','50','40','40']"
                data-width="528"
                data-minwidth="528"
                data-whitespace="normal"
                data-type="shape"
                data-responsive_offset="on"
                data-frames='[{"from":"opacity:0;z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;","mask":"x:0px;y:0px;","speed":1500,"to":"o:1;","delay":0,"ease":"Power3.easeInOut"},{"delay":"wait","speed":400,"to":"opacity:0;","ease":"Power3.easeInOut"}]'
                data-textAlign="['left','left','left','left']"
                data-paddingtop="[0,0,0,0]"
                data-paddingright="[0,0,0,0]"
                data-paddingbottom="[0,0,0,0]"
                data-paddingleft="[0,0,0,0]">
                <?php if(!$k){ ?>
                  <div class="project-detail-info">
                    <div class="project-detail-control"><span class="hide-info">hide information</span><span class="show-info">show information</span></div>
                    <div class="project-detail-content">
                    <h3 class="project-detail-title" style="font-size:3.2rem !important;"><?php echo $project['project']['title'];?></h3>
                    <p class="project-detail-text" style="font-size:1rem !important;"><?php echo strip_tags($project['project']['description']);?></p>
                    <ul class="project-detail-list text-dark">
                      <!-- <li>
                        <span class="left">Clients:</span>
                        <span class="right">Ethan Hunt</span>
                      </li> -->
                      <li>
                        <span class="left">Status:</span>
                        <span class="right"><?php echo $project ['project']['status'];?></span>
                      </li>
                      <li>
                        <span class="left">Project Type:</span>
                        <span class="right"><?php echo $project['project']['name']; ?></span>
                      </li>

                      <li>
                        <span class="left">Area:</span>
                        <span class="right"><?php echo $project['project']['area']; ?></span>
                      </li>

                      <!-- <li>
                        <span class="left">Architects:</span>
                        <span class="right">Logan Cee</span>
                      </li> -->
                    </ul>
                    <div class="project-detail-meta">
                      <span class="left text-dark hidden-xs pull-sm-left">Share:</span>
                      <div class="social-list pull-sm-right">
                        <!-- <a href="" class="icon ion-social-twitter"></a> -->
                        <a href="<?php echo $social['facebook'];?>" target="_blank" class="icon ion-social-facebook"></a>
                        <!-- <a href="" class="icon ion-social-googleplus"></a> -->
                        <a href="<?php echo $social['linkedin'];?>" target="_blank" class="icon ion-social-linkedin"></a>
                        <a href="<?php echo $social ['instagram'];?>" target="_blank    " class="icon ion-social-instagram"></a>
                      </div>
                    </div>
                    </div>
                  </div>
                <?php } ?>

                </div>
              </li>
<?php } ?>
              <!-- Slide 2 -->

              <!-- Slide 3 -->

            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <?php
    require('layout/footer.php');
    ?>
