<?php
require('layout/header.php');

$newsId = (int) $_GET['news'];

$main = new Main();

$newsImages = $main->getNewsDetail($newsId);


?>
<style>
  .row-images .col-image {
    margin-top: 20px !important;
  }
</style>

<main style="margin-bottom: 2em;">
  <div class="container">
    <div class="row">
      <div class="col-md-10 col-lg-8">
        <div class="title-info">News post</div>

        <h1 class="display-1"><?php echo $newsImages['new'][0]['title'] ?></h1>
      </div>
    </div>
  </div>  
</main>

<div class="post-gallery">
  <div class="slider-prev icon-chevron-left hidden-xs"></div>
  <div class="slider-next icon-chevron-right hidden-xs"></div>
  <div class="rev_slider_wrapper">
    <div id="rev_slider3" class="rev_slider tp-overflow-hidden fullscreenbanner">
      <ul>

        <!-- Slide 1 -->
        <?php foreach ($newsImages['image'] as $newsImage) { ?>

          <li data-transition="slotzoom-horizontal" data-slotamount="7" data-masterspeed="1000" data-fsmasterspeed="1000">

            <!-- Main image-->
            <img src="<?php echo $newsImage['image']; ?>" alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg">
          </li>
        <?php } ?>

        <!-- Slide 2 -->

        <!-- <li data-transition="slotzoom-horizontal"  data-slotamount="7" data-masterspeed="1000" data-fsmasterspeed="1000"> -->

        <!-- Main image-->

        <!-- <img src="images/news/2-1920x670.jpg" alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg">
            </li> -->

        <!-- Slide 3 -->

        <!-- <li  data-transition="slotzoom-horizontal"  data-slotamount="7" data-masterspeed="1000" data-fsmasterspeed="1000"> -->

        <!-- Main image-->

        <!-- <img src="images/news/3-1920x670.jpg" alt="" data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg">
            </li> -->
      </ul>
    </div>
  </div>
</div>

<div class="page-content">
  <div class="primary">
    <div class="container">
      <article class="post">
        <div class="row">
          <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
            <div class="posted-on">
              <!-- <a class="url fn n" href="#">Admin</a> -->
              <?php echo $newsImages['new'][0]['date'] ?>
            </div>
          </div>
        </div>
        <div class="entry-content">
          <div class="row">
            <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
              <h3 class="entry-description"><?php echo $newsImages['new'][0]['title']; ?></h3>
              <p><?php echo $newsImages['new'][0]['description']; ?></p>
              <!-- <p>One touch of a red-hot stove is usually all we need to avoid that kind of discomfort in the future. The same is true as we experience the emotional sensation of stress from our first instances of social rejection or ridicule. We quickly learn to fear and thus automatically avoid potentially stressful situations of all kinds, including the most common of all: making mistakes. </p> -->
            </div>
          </div>

          <?php
          $grid = 12 / count($newsImages['image_grid']);
          ?>
          <div class="row-images row">
            <?php foreach ($newsImages['image_grid'] as $img) { ?>
              <div class="col-image col col-sm-<?php echo $grid; ?>"><img src="<?php echo $img; ?>"></div>
              <!-- <div class="col-image col col-sm-<?php echo $grid; ?>"><img src="images/news/1-570x572.jpg"></div>
                <div class="col-image col col-sm-<?php echo $grid; ?>"><img src="images/news/1-570x572.jpg"></div> -->
            <?php } ?>
          </div>
          <!-- <div class="row">
                <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
                  <h4>Defaulting to Min</h4>
                  <p>Everything along the way, to and from, fascinated her: every pebble, ant, stick, leaf, blade of grass, and crack in the sidewalk was something to be picked up, looked at, tasted, smelled, and shaken. Everything was interesting to her. She knew nothing. I knew everything…been there, done that. She was in the moment, I was in the past. She was mindful. I was mindless.</p>
                  <blockquote><p>Our greatest weakness lies in giving up. The most certain way to succeed is always to try just one more time.</p>
                  </blockquote>
                  <p class="blockquote-cite"><cite>Logan Cee</cite>Envato Author</p>
                  <p>Both of these assumptions, of course, could be entirely false. Self-censoring is firmly rooted in our experiences with mistakes in the past and not the present. The brain messages arising from those experiences can be deceptive. </p>
                </div>
              </div> -->
        </div>
        <!-- <div class="entry-footer">
              <div class="row">
                <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
                  <div class="tags-links">
                    <span>Tags:</span>
                    <a href="">Inspiration</a>,
                    <a href="">Workspace</a>,
                    <a href="">Minimal</a>,
                    <a href="">Decoation</a>
                  </div>
                  <div class="post-share">
                    <span>Share:</span>
                    <a href="" class="icon ion-social-facebook"></a>
                    <a href="" class="icon ion-social-twitter"></a>
                    <a href="" class="icon ion-social-pinterest"></a>
                  </div>
                </div>
              </div>
            </div> -->
      </article>
      <!-- <section class="related-posts">
            <div class="row">
              <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
                <h6 class="related-post-title">Related Posts</h6>
              </div>
            </div>
            <div class="news-carousel owl-carousel">
              <div class="news-item">
                <img alt="" src="images/news/1-370x370.jpg">
                <div class="news-hover">
                  <div class="hover-border"><div></div></div>
                  <div class="content">
                    <div class="time">Dec 15th, 2016</div>
                    <h3 class="news-title">Discover Architecture Of Bario</h3>
                    <p class="news-description">Lorem ipsum dolor sit amet, consect etur adipiscing elit. Mauris vel auctorol est. Integer nunc ipsum...</p>
                  </div>
                  <a class="read-more" href="#">Continue</a>
                </div>
              </div>
              <div class="news-item">
                <img alt="" src="images/news/2-370x370.jpg">
                <div class="news-hover">
                  <div class="hover-border"><div></div></div>
                  <div class="content">
                    <div class="time">Dec 15th, 2016</div>
                    <h3 class="news-title">Discover Architecture Of Bario</h3>
                    <p class="news-description">Lorem ipsum dolor sit amet, consect etur adipiscing elit. Mauris vel auctorol est. Integer nunc ipsum...</p>
                  </div>
                  <a class="read-more" href="#">Continue</a>
                </div>
              </div>
              <div class="news-item">
                <img alt="" src="images/news/3-370x370.jpg">
                <div class="news-hover">
                  <div class="hover-border"><div></div></div>
                  <div class="content">
                    <div class="time">Dec 15th, 2016</div>
                    <h3 class="news-title">Discover Architecture Of Bario</h3>
                    <p class="news-description">Lorem ipsum dolor sit amet, consect etur adipiscing elit. Mauris vel auctorol est. Integer nunc ipsum...</p>
                  </div>
                  <a class="read-more" href="#">Continue</a>
                </div>
              </div>
            </div>
          </section> -->
    </div>
  </div>
  <!-- <section class="section-comments">
          <div class="container">
            <div class="row">
              <div class="col-md-10 col-lg-8 col-md-offset-1 col-lg-offset-2">
                <div class="section-add-comment">
                  <div class="comments">
                    <div class="comments-area">
                      <h6 class="comments-title">02 Comments</h6>
                      <ol class="comment-list">
                        <li class="comment">
                          <article class="comment-body">
                            <div class="avatar">
                              <img alt="" src="images/users/1-70x70.jpg" height="70" width="70">
                            </div>
                            <div class="comment-content">
                              <div class="comment-metadata">
                                <b class="fn"><a href="">Daniel Vandaft</a></b> -
                                <a class="comment-time" href="http://test.paul-themes.com/wp/felix/felix-demo/2012/01/03/template-comments/#comment-6">
                                  <time datetime="">
                                    Jul 17,2015 at 15 hours ago </time>
                                </a>
                              </div>
                              <p>Comment example here. Nulla risus lacus, vehicula id mi vitae, auctor accumsan nulla. Sed a mi quam. Lorem In euismod urna ac massa adipiscing interdum.</p>
                              <div class="reply"><a rel="nofollow" class="comment-reply-link" href='#'>Reply</a></div>
                            </div>
                          </article>
                        </li>
                        <li class="comment">
                          <article class="comment-body">
                            <div class="avatar">
                              <img alt="" src="images/users/2-70x70.jpg" height="70" width="70">
                            </div>
                            <div class="comment-content">
                              <div class="comment-metadata">
                                <b class="fn"><a href="">Vanessa Elina</a></b> -
                                <a class="comment-time" href="http://test.paul-themes.com/wp/felix/felix-demo/2012/01/03/template-comments/#comment-6">
                                  <time datetime="">
                                    Jul 17,2015 at 15 hours ago </time>
                                </a>
                              </div>
                              <p>Comment example here. Nulla risus lacus, vehicula id mi vitae, auctor accumsan nulla. Sed a mi quam. Lorem In euismod urna ac massa adipiscing interdum.</p>
                              <div class="reply"><a rel="nofollow" class="comment-reply-link" href='#'>Reply</a></div>
                            </div>
                          </article>
                        </li>
                      </ol>
                    </div>
                  </div>
                  <div class="comment-respond">
                    <h6 class="comment-reply-title">Post a comment</h6>
                    <form class="js-form">
                      <div class="row">
                        <div class="form-group col-sm-6">
                          <input type="text" name="name" required placeholder="Name*">
                        </div>
                        <div class="form-group col-sm-6">
                          <input type="email" name="email" placeholder="Email">
                        </div>
                        <div class="form-group col-sm-12">
                          <input type="text" placeholder="Subject (Optinal)">
                        </div>
                        <div class="form-group col-sm-12">
                          <textarea name="message"  required  placeholder="Message*"></textarea>
                        </div>
                        <div class="col-sm-12"><button type="submit" class="btn">Post Comment</button></div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section> -->
</div>

<!-- Footer -->


<?php
require('layout/footer.php');
?>