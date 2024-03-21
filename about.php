<!DOCTYPE HTML>
<html lang="en">

<?php
    include "head.inc.php";
    ?>

  <body>

  <?php
    include "header.inc.php";
    ?>
     
            
    <!-- Start of Navbar  -->
     <?php
        include "navbar.inc.php";
        ?>
     <!-- End of navbar  -->

    <!-- start of head section -->
    <section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)" >
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
              <li><a href="registration.php">Registration</a></li>
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
        <div class="col-md-5 mb-2">
          <h2 class="heading" data-aos="fade-up">About Us</h2>
        </div>
      </div>
    </div>
    
    <section class="py-5 bg-light">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-12 col-lg-7 ml-auto order-lg-2 position-relative mb-5" data-aos="fade-up">
            <img src="images/slider-7.jpg" alt="Image" class="img-fluid rounded">
          </div>
          <div class="col-md-12 col-lg-4 order-lg-1" data-aos="fade-up">
            <h2 class="heading">Welcome!</h2>
            <p class="mb-4">Welcome to our exquisite hotel, where luxury meets comfort and every stay is a memorable experience. Nestled in the heart of Singapore, our hotel boasts stunning views, modern amenities, and unparalleled hospitality. Whether you're here for a relaxing getaway, a business trip, or a special occasion, our dedicated team is committed to ensuring your stay is nothing short of perfection. From elegantly appointed rooms to gourmet dining options and top-notch services, we invite you to indulge in a world of sophistication and relaxation at our esteemed hotel.</p>
          </div>
          
        </div>
      </div>
    </section>



    <section class="section slider-section bg-light">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-md-7">
            <h2 class="heading" data-aos="fade-up">Photos</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="home-slider major-caousel owl-carousel mb-5" data-aos="fade-up" data-aos-delay="200">
              <div class="slider-item">
                <a href="images/slider-1.jpg" data-fancybox="images" data-caption="Courtyard Room Picture"><img src="images/slider-1.jpg" alt="Image placeholder" class="img-fluid"></a>
              </div>
              <div class="slider-item">
                <a href="images/slider-2.jpg" data-fancybox="images" data-caption="Presidential Room Picture"><img src="images/slider-2.jpg" alt="Image placeholder" class="img-fluid"></a>
              </div>
              <div class="slider-item">
                <a href="images/slider-3.jpg" data-fancybox="images" data-caption="Single Room Picture"><img src="images/slider-3.jpg" alt="Image placeholder" class="img-fluid"></a>
              </div>
              <div class="slider-item">
                <a href="images/slider-4.jpg" data-fancybox="images" data-caption="Suite"><img src="images/slider-4.jpg" alt="Image placeholder" class="img-fluid"></a>
              </div>
              <div class="slider-item">
                <a href="images/slider-5.jpg" data-fancybox="images" data-caption="Room 2"><img src="images/slider-5.jpg" alt="Image placeholder" class="img-fluid"></a>
              </div>
              <div class="slider-item">
                <a href="images/slider-6.jpg" data-fancybox="images" data-caption="Room 3"><img src="images/slider-6.jpg" alt="Image placeholder" class="img-fluid"></a>
              </div>
              <div class="slider-item">
                <a href="images/slider-7.jpg" data-fancybox="images" data-caption="Room 4"><img src="images/slider-7.jpg" alt="Image placeholder" class="img-fluid"></a>
              </div>
            </div>
            <!-- END slider -->
          </div>
        
        </div>
      </div>
    </section>
    
 <!-- Start of footer -->
 <?php
    include "footer.inc.php";
    ?>
<!-- End of footer -->
    
  </body>
</html>