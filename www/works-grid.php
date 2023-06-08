<?php
require('layout/header.php');
$main = new Main();
$projects = $main->getProjects();
$types = $main->getTypes();
?>

    <!-- Pageheader -->

    <main class="page-header">
      <div class="container"><h1>“Good buildings come from good people, and all problems are solved by good design.” - Stephen Gardiner</h1></div>
    </main>

    <div class="content">
      <div class="projects">
        <div class="container">
          <div class="filter-content-2">
            <ul class="filter js-filter">
              <li class="active"><a href="#" data-filter="*">All</a></li>
              <?php foreach($types as $type): ?>
              <li><a href="#" data-filter=".type<?php echo $type['id']; ?>"><?php echo $type['name']; ?></a></li>
              <?php endForeach ?>
              <!-- <li><a href="#" data-filter=".villa">Villa</a></li>
              <li><a href="#" data-filter=".interior">Interior</a></li>
              <li><a href="#" data-filter=".exterior">Exterior</a></li> -->
            </ul>
          </div>
        </div>
        <div class="grid-items js-isotope js-grid-items">
            <?php foreach($projects as $project): ?>
          <div class="grid-item type<?php echo $project['type_id']; ?> js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <!-- <img alt="" class="img-responsive" src="<?php echo $project['cover_image']; ?>"> -->
              <div alt="" class="img-responsive" src="<?php echo $project['cover_image']?>" style="min-height:574px;min-width: 426px ;background-image:url('<?php echo $project['cover_image']?>'); background-position:center center; background-repeat:no-repeat; background-size:cover"> </div>
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title"><?php echo $project['title']; ?></h3>
                  <p class="project-description"><?php echo strip_tags($project['description']); ?></p>
                </div>
              </div>
              <a href="project-detail.php?project=<?php echo $project['id']; ?>" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
        </div>
        <?php endForeach; ?>


          <!-- <div class="grid-item building js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <img alt="" class="img-responsive" src="images/projects/2-426x574.jpg">
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title">Ocean<br>Museum<br>Italy</h3>
                  <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
                </div>
              </div>
              <a href="project-detail.php" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
          </div>
          <div class="grid-item villa js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <img alt="" class="img-responsive" src="images/projects/3-426x574.jpg">
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title">Milko<br>Co-Working<br>Building</h3>
                  <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
                </div>
              </div>
              <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
          </div>
          <div class="grid-item villa js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <img alt="" class="img-responsive" src="images/projects/4-426x574.jpg">
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title">Redesign<br>Interior For<br>Villa</h3>
                  <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
                </div>
              </div>
              <a href="project-detail.php" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
          </div>
          <div class="grid-item interior js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <img alt="" class="img-responsive" src="images/projects/5-426x574.jpg">
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title">Wooden<br>Hozirontal<br>Villa</h3>
                  <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
                </div>
              </div>
              <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
          </div>
          <div class="grid-item interior js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <img alt="" class="img-responsive" src="images/projects/6-426x574.jpg">
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title">Small<br>House Near<br>Wroclaw</h3>
                  <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
                </div>
              </div>
              <a href="project-detail.php" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
          </div>
          <div class="grid-item exterior js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <img alt="" class="img-responsive" src="images/projects/7-426x574.jpg">
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title">Bellecomde<br>Holiday<br>Residence</h3>
                  <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
                </div>
              </div>
              <a href="" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
          </div>
          <div class="grid-item exterior js-isotope-item js-grid-item">
            <div class="project-item item-shadow">
              <img alt="" class="img-responsive" src="images/projects/8-426x574.jpg">
              <div class="project-hover">
                <div class="project-hover-content">
                  <h3 class="project-title">Cubic<br>Inter Mesuem<br>In Rome</h3>
                  <p class="project-description">Lorem ipsum dolor sit amet, consectetur adipil pcing elit. Proin nunc leo, rhoncus sit amet tolil arcu vel, pharetra volutpat sem lorn Donec tincidunt velit nec laoreet semper...</p>
                </div>
              </div>
              <a href="project-detail.php" class="link-arrow">See project <i class="icon ion-ios-arrow-right"></i></a>
            </div>
          </div> -->
        </div>
      </div>
    </div>

    <!-- Footer -->


        <?php
        require('layout/footer.php');
        ?>
