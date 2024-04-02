<?php
session_start();

$headSection = "nonmember_head.inc.php"; // Default to non-member head
$navBar = "navbar.inc.php"; // Default to non-member navbar

// Check if the authentication cookie exists
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Include the member head section if the auth cookie exists
    $headSection = "member_head.inc.php";
    $navBar = "member_navbar.inc.php";
}
?>

<!DOCTYPE HTML>
<html lang="en">

<?php
    include "head.inc.php";
    ?>

  <body>

  <?php
    include "header.inc.php";
    include $navBar;
    include $headSection;
    renderNavbar('About');
    ?>

        <!-- start of head section -->
        <!-- <section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)" >
      <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
          <div class="col-md-10 text-center" data-aos="fade">
            <h1 class="heading mb-3">About Us</h1>
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
    </section> -->
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
        <h2 class="heading" data-aos="fade-up">Location</h2>
      </div>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-md">
        <!-- Google Map embed code -->
        <div class="google-map text-center">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.665394053245!2d103.84620671172271!3d1.3774387614783004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31da16e96db0a1ab%3A0x3d0be54fbbd6e1cd!2sSingapore%20Institute%20of%20Technology%20(SIT%40NYP)!5e0!3m2!1sen!2ssg!4v1711283076538!5m2!1sen!2ssg" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
      <div class="col-md">
        <h4>5 HOTEL</h4>
        <p><span class="d-block">Singapore Institite of Technology @ NYP</span></p>
        <p><span class="d-block"><span class="ion-ios-location h5 mr-3 text-primary"></span>172A Ang Mo Kio Avenue 8, Singapore 567739</span></p>
        <p><span class="d-block"><span class="ion-ios-telephone h5 mr-3 text-primary"></span><a href="tel:+6565921189">(+65) 6592 1189</a></span></p>
        <p><span class="d-block"><span class="ion-ios-email h5 mr-3 text-primary"></span> <a href="mailto:enquiries@fivestarhotel.com">enquiries@fivestarhotel.com</span></p>
      </div>
    </div>
  </div>
</section>

    <div class="section">
      <div class="container">

        <div class="row justify-content-center text-center mb-5">
          <div class="col-md-7 mb-5">
            <h2 class="heading" data-aos="fade">History</h2>
          </div>
        </div>

        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="timeline-item" date-is='2024' data-aos="fade">
              <h3>Today</h3>
              <p>Today, the building is home to the luxurious grande dame 400-room. Extraordinary for its historic architecture and for the scale and quality of its 21st century restoration, the hotel has won the hearts of many of our guests, as it provides a blend of luxurious living with a touch of elegance and the nostalgia of old. </p>
            </div>
            
            <div class="timeline-item" date-is='2009' data-aos="fade">
              <h3>Renovations Began</h3>
              <p>
                The hotel underwent yet another transformation. With the coming Millennium. the hotel grasped the mood of change saw the need to seek a new contemporary. The building was labelled a historial landmark, which meant that it was placed under conservation.
              </p>
            </div>
            
            <div class="timeline-item" date-is='2001' data-aos="fade">
              <h3>The Birth of the Hotel</h3>
              <p>
                The hotel was officially launched on 1st January 2001. The building played a significant role as the centre of Singapore's commercial, social and official life; a symbol of our lion city. 
              </p>
            </div>
          </div>
        </div>

        
      </div>
    </div>
    
 <!-- Start of footer -->
 <?php
    include "footer.inc.php";
    ?>
<!-- End of footer -->
<script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
<script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
<script src="js/main.js"></script>
    
  </body>
</html>