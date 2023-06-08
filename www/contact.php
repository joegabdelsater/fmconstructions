<?php
require('layout/header.php');
$main = new Main();
$contact = $main->getContact();
$social = $main->getSocialMedia();
?>

<style>
     #map {
       height: 30em;
     }

   </style>


    <main class="page-header-3">
      <div class="container">
        <div class="row">
          <div class="col-md-3">
            <div class="title-hr"></div>
          </div>
          <div class="col-md-8 col-lg-6"><h1 style="text-align:center">Contact Us</h1></div>
        </div>
      </div>
    </main>

    <div class="content">
   
      <div id="map" class="map"></div>
      <div style="text-align:center;text-align: center;margin-top: 20px;font-weight: bold;">
      <a href="https://www.google.com/maps/place/FMC+(Farah+%26+Mrad+Constructions)/@33.9881764,35.6404337,17z/data=!3m1!4b1!4m5!3m4!1s0x151f41a559a4f8b3:0xc01d970e5c289461!8m2!3d33.988172!4d35.6426224" target="_blank" style="color:white" >GET DIRECTIONS</a>
      </div>
      <div class="page-inner">
        <section>
          <div class="container">
            <div class="row">
              <div class="col-md-3">
                <div class="section-info">
                  <div class="title-hr"></div>
                  <div class="info-title">Keep in touch</div>
                </div>
              </div>
              <div class="col-md-9">
                <div class="row-contact row">
                  <div class="col-contact col-lg-6">
                    <h3 class="contact-title contact-top"><?php echo $contact['area_text']; ?></h3>
                    <p class="contact-address text-muted"><strong><?php echo $contact['address']?></strong></p>
                    <p class="contact-row"><strong class="text-dark">Email:</strong> <?php echo $contact['email']; ?></p>
                    <!-- <p class="contact-row"><strong class="text-dark">Skype:</strong>  bauhaus.arc</p> -->
                  </div>
                  <div class="col-contact col-lg-6">
                    <p class="contact-top"><strong class="text-muted">Call directly:</strong></p>
                    <p class="phone-lg text-dark"><?php echo $contact['number']; ?></p>
                    <p class="text-muted"><strong class="text-dark">Work Hours</strong><br>
                    <?php echo $contact['working_hours_1']; ?><br>
                    <?php echo $contact['working_hours_2']; ?></p>
                    <div class="text-muted"><strong class="text-dark">Follow us</strong><br>
                      <div class="contact-social social-list">

                        <a href="<?php echo $social['facebook'];?>" target="_blank" class="icon ion-social-facebook"></a>

                        <a href="<?php echo $social['linkedin'];?>" target="_blank" class="icon ion-social-linkedin"></a>
                        <a href="<?php echo $social['instagram'];?>" target="_blank" class="icon ion-social-instagram"></a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
        <section class="section-message section">
          <div class="container">
            <div class="row">
              <div class="col-md-3">
                <div class="section-info">
                  <div class="title-hr"></div>
                  <div class="info-title">You need help</div>
                </div>
              </div>
              <div class="col-md-9">
                <form class="js-form" method"POST" id="contact_form">
                  <div class="row">
                    <div class="form-group col-sm-6 col-lg-4">
                      <input class="input-gray" type="text" name="name" id="name" required placeholder="Name*">
                    </div>
                    <div class="form-group col-sm-6 col-lg-4">
                      <input class="input-gray" type="email" name="email" id="email" placeholder="Email">
                    </div>
                    <div class="form-group col-sm-12 col-lg-4">
                      <input class="input-gray" type="text" name="subject" id="subject" placeholder="Subject (Optinal)">
                    </div>
                    <div class="form-group col-sm-12">
                      <textarea class="input-gray" name="message"  required id="message" placeholder="Message*"></textarea>
                    </div>
                    <div class="col-sm-12"><button type="submit" class="btn-upper btn-yellow btn">Send Message</button></div>
                  </div>
                  <!-- <div class="success-message"><i class="fa fa-check text-primary"></i> Thank you!. Your message is successfully sent...</div> -->
                  <!-- <div class="error-message">We're sorry, but something went wrong</div> -->
                </form>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>

    <!-- Footer -->
    <script  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAu7CGqkEJxjXleCHS185bXh9f-kpjCITA"></script>

    <script>
         var map;
         var myLatlng = new google.maps.LatLng(<?php echo $contact['latitude']; ?>,<?php echo $contact['longitude']; ?>);
         function initMap() {
           map = new google.maps.Map(document.getElementById('map'), {
             center:myLatlng,
             zoom: 8
           });
         }

         var marker = new google.maps.Marker({
                    position: myLatlng,
                    title:"FMC Constructions"
                    });

// To add the marker to the map, call setMap();
        initMap();
        marker.setMap(map);

       </script>



        <?php
        require('layout/footer.php');
        ?>
