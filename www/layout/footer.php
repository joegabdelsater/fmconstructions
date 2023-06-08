
    <!-- Footer -->

    <footer id="footer" class="footer section">
      <div class="footer-flex">
        <div class="flex-item">
          <a class="brand pull-left" href="index.php">
            <img alt="" src="../images/fmc_white.png">
            <div class="brand-info">
              <div class="brand-name">Farah & Mrad</div>
              <div class="brand-text">Construction</div>
            </div>
          </a>
        </div>
        <div class="flex-item">
          <div class="inline-block">© XTND 2020<br>All Rights Resevered</div>
        </div>
         <!-- <div class="flex-item">
          <ul>
            <li><a href="">Site Map</a></li>
            <li><a href="">Term & Conditions</a></li>
            <li><a href="">Privacy Policy</a></li>
            <li><a href="">Help</a></li>
            <li><a href="">Affiliatep</a></li>
          </ul>
        </div> -->
        <div class="flex-item">
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="works-grid.php">Projects</a></li>
            <li><a href="news-listing.php">News</a></li>
            <li><a href="contact.php">Contact Us</a></li>
          </ul>
        </div>
        <!-- <div class="flex-item">
          <ul>
            <li class="active"><a href="">ENG</a></li>
            <li><a href="">FRA</a></li>
            <li><a href="">GER</a></li>
          </ul>
        </div> -->
        <div class="flex-item">
          <div class="social-list">
            <!-- <a href="" class="icon ion-social-twitter"></a> -->
            <a href="<?php echo $social['facebook'];?>" target="_blank"class="icon ion-social-facebook"></a>
            <!-- <a href="" class="icon ion-social-googleplus"></a> -->
            <a href="<?php echo $social['linkedin'];?>"target="_blank" class="icon ion-social-linkedin"></a>
            <!-- <a href="" class="icon ion-social-dribbble-outline"></a> -->
            <a href="<?php echo $social['instagram'];?>" target="_blank" class="icon ion-social-instagram"></a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</div>

<!-- jQuery -->




<script src="js/jquery.min.js"></script>
<script src="js/animsition.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/smoothscroll.js"></script>
<script src="js/jquery.validate.min.js"></script>
<script src="js/wow.min.js"></script>
<script src="js/jquery.stellar.min.js"></script>
<script src="js/jquery.magnific-popup.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/isotope.pkgd.min.js"></script>
<script src="js/imagesloaded.pkgd.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/sly.min.js"></script>


<!-- Slider revolution -->
<script src="js/rev-slider/jquery.themepunch.tools.min.js"></script>
<script src="js/rev-slider/jquery.themepunch.revolution.min.js"></script>

<!-- Slider revolution 5x Extensions   -->
<script src="js/rev-slider/revolution.extension.actions.min.js"></script>
<script src="js/rev-slider/revolution.extension.carousel.min.js"></script>
<script src="js/rev-slider/revolution.extension.kenburn.min.js"></script>
<script src="js/rev-slider/revolution.extension.layeranimation.min.js"></script>
<script src="js/rev-slider/revolution.extension.migration.min.js"></script>
<script src="js/rev-slider/revolution.extension.navigation.min.js"></script>
<script src="js/rev-slider/revolution.extension.parallax.min.js"></script>
<script src="js/rev-slider/revolution.extension.slideanims.min.js"></script>
<script src="js/rev-slider/revolution.extension.video.min.js"></script>


<!-- Scripts -->
<script src="js/scripts.js"></script>
<script src="js/rev-slider-init.js"></script>


<script>
$(document).ready(function(){
    $("#contact_form").submit(function(event){
	event.preventDefault(); //prevent default action

     var name = $("#name").val();
     var email = $("#email").val();
     var subject = $("#subject").val();
     var message = $("#message").val();

     console.log(name,email,subject,message);

	var post_url = $(this).attr("action"); //get form action url
	var request_method = $(this).attr("method"); //get form GET/POST method
	var form_data = $(this).serialize(); //Encode form elements for submission

	$.ajax({
		url : './common/form.php',
		type: 'POST',
		data : {
            name: name,
            email: email,
            subject: subject,
            message:message
        }
	}).done(function(response){ //
		// $("#server-results").html(response);
        alert('Your form was successfully submitted!');
	});
});
})
</script>

</body>
</html>
