<!DOCTYPE HTML>
<html lang="en">

<?php
    include "inc/head.inc.php";
    ?>

  <body>

  <?php
    include "inc/header.inc.php";
    ?>
     
            
            <!-- Start of Navbar  -->
            <?php
            include "inc/navbar.inc.php";
            ?>
            <!-- End of navbar  -->

    <!-- start of head section -->
    <section class="site-hero inner-page overlay">
      <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
          <div class="col-md-10 text-center" data-aos="fade">
            <h1 class="heading mb-3">About us</h1>
            <ul class="custom-breadcrumbs mb-4">
              <li><a href="index.php">Home</a></li>
              <li>&bullet;</li>
              <li><a href="rooms.php">Rooms</a></li>
              <li>&bullet;</li>
              <li>About</li>
              <li>&bullet;</li>
              <li><a href="login.php">Login</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>
<!-- end of head section -->

    <div class="container section">

      <div class="row justify-content-center text-center mb-5">
        <div class="col-md-7 mb-5">
          <h2 class="heading" data-aos="fade-up">About Us</h2>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
          <div class="block-2">
            <div class="flipper">
  
              <p> some flowery language here</p>
            </div>
          </div>
        </div>
      </div>
    </div>



    <div class="section">
      <div class="container">

        <div class="row justify-content-center text-center mb-5">
          <div class="col-md-7 mb-5">
            <h2 class="heading" data-aos="fade">History</h2>
          </div>
        </div>

        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="timeline-item" date-is='2019' data-aos="fade">
              <h3>Milestone #3</h3>
              <p>some shit</p>
            </div>
            
            <div class="timeline-item" date-is='2011' data-aos="fade">
              <h3>Milestone #2</h3>
              <p>some shit</p>
            </div>
            
            <div class="timeline-item" date-is='2008' data-aos="fade">
              <h3>Milestone #1</h3>
              <p>some shit</p>
            </div>
          </div>
        </div>
        

      </div>
    </div>

    
 <!-- Start of footer -->
 <?php
    include "inc/footer.inc.php";
    ?>
<!-- End of footer -->
    
  </body>
</html>