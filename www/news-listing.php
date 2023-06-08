<?php
require('layout/header.php');

$main = new Main();

$news = $main->getNews(true, 300);

?>

    <div class="content">
      <div class="container">
        <div class="filter-content-4">
          <!-- <ul class="filter js-filter">
            <li class="active"><a href="#" data-filter="*">All</a></li>
            <li><a href="#" data-filter=".inspiration">Inspiration </a></li>
            <li><a href="#" data-filter=".architecture-inteior">Architecture & Inteior</a></li>
            <li><a href="#" data-filter=".decoration">Decoration</a></li>
            <li><a href="#" data-filter=".plants">Plants</a></li>
          </ul> -->
        </div>
        <div class="js-isotope">



        <?php foreach($news as $n): ?>
          <div class="plants card-row js-isotope-item" style="width:100%;">
            <div class="card-row-img col-md-7 col-lg-8 hidden-sm hidden-xs" style="background-image:url(<?php echo $n['cover_image']; ?>); background-repeat: no-repeat;
    background-position: center center;
    background-size: cover;"></div>
            <!-- <img class="visible-sm visible-xs img-responsive" alt="" style="width:100%;" src="<?php //echo $n['cover_image']; ?>"> -->
            <div class="card-block col-md-offset-7 col-lg-offset-8">
              <div class="card-posted"> on <?php echo $n['date']; ?></div>
              <h4 class="card-title"><?php echo $n['title']; ?></h4>
              <div class="card-text"><?php echo $n['description']; ?></div>
              <a href="post-gallery.php?news=<?php echo $n['id']; ?>" class="card-read-more">Continue</a>
            </div>
          </div>
          <?php endForeach; ?>
        </div>
      </div>
    </div>

    <!-- Footer -->


        <?php
        require('layout/footer.php');
        ?>
