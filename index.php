<?php
session_start();

// Check if the user is logged in
if (isset($_SESSION['loggedin'])) {
    // Redirect to userIndex.php if the user is logged in
    header('Location: member_page.php');
    exit;
}
?>

<!DOCTYPE HTML>
<html>
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
    <section class="site-hero overlay" style="background-image: url(images/slider-6.jpg)" data-stellar-background-ratio="0.5">
      <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
          <div class="col-md-10 text-center" data-aos="fade-up">
            <span class="custom-caption text-uppercase text-white d-block  mb-3">Welcome To 5 Hotel</span>
            <h1 class="heading">A Best Place To Stay</h1>
            <ul class="custom-breadcrumbs mb-4">
              <li>Home</li>
              <li>&bullet;</li>
              <li><a href="rooms.php">Rooms</a></li>
              <li>&bullet;</li>
              <li><a href="about.php">About</a></li>
              <li>&bullet;</li>
              <li><a href="registration.php">Registration</a></li>
              <li>&bullet;</li>
              <li><a href="login.php">Login</a></li>
            </ul>
          </div>
            </div>
          </section>
    <!-- end of head section -->

    <section class="section bg-light pb-0"  >
      <div class="container">
       
        <div class="row check-availabilty" id="next">
          <div class="block-32" data-aos="fade-up" data-aos-offset="-200">

            <form id="availabilityForm">
              <div class="row">
                <div class="col-md-6 mb-3 mb-lg-0 col-lg-3">
                  <label for="checkin_date" class="font-weight-bold text-black">Check In</label>
                  <div class="field-icon-wrap">
                    <div class="icon"><span class="icon-calendar"></span></div>
                    <input type="text" id="checkin_date" class="form-control">
                  </div>
                </div>
                <div class="col-md-6 mb-3 mb-lg-0 col-lg-3">
                  <label for="checkout_date" class="font-weight-bold text-black">Check Out</label>
                  <div class="field-icon-wrap">
                    <div class="icon"><span class="icon-calendar"></span></div>
                    <input type="text" id="checkout_date" class="form-control">
                  </div>
                </div>
                <div class="col-md-6 mb-3 mb-md-0 col-lg-3">
                  <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                      <label for="adults" class="font-weight-bold text-black">No. of Rooms</label>
                      <div class="field-icon-wrap">
                        <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                        <select name="" id="adults" class="form-control">
                          <option value="">1</option>
                          <option value="">2</option>
                          <option value="">3</option>
                          <option value="">4</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6 mb-3 mb-md-0">
                      <label for="children" class="font-weight-bold text-black">Pax</label>
                      <div class="field-icon-wrap">
                        <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                        <select name="" id="children" class="form-control">
                          <option value="">1</option>
                          <option value="">2</option>
                          <option value="">3</option>
                          <option value="">4+</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-3 align-self-end">
                  <button type="submit" class="btn btn-primary btn-block text-white">Check Availabilty</button>
                </div>
              </div>
            </form>
            <div id="availabilityResult"></div>
          </div>
        </div>
      </div>
    </section>

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

    <section class="section">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-md-7">
            <h2 class="heading" data-aos="fade-up">Rooms &amp; Suites</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <a href="#" class="room">
              <figure class="img-wrap">
                <img src="images/img_1.jpg" alt="Free website template" class="img-fluid mb-3">
              </figure>
              <div class="p-3 text-center room-info">
                <h2>Single Room</h2>
                <span class="text-uppercase letter-spacing-1">90$ / per night</span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <a href="#" class="room">
              <figure class="img-wrap">
                <img src="images/img_2.jpg" alt="Free website template" class="img-fluid mb-3">
              </figure>
              <div class="p-3 text-center room-info">
                <h2>Family Room</h2>
                <span class="text-uppercase letter-spacing-1">120$ / per night</span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <a href="#" class="room">
              <figure class="img-wrap">
                <img src="images/img_3.jpg" alt="Free website template" class="img-fluid mb-3">
              </figure>
              <div class="p-3 text-center room-info">
                <h2>Presidential Room</h2>
                <span class="text-uppercase letter-spacing-1">250$ / per night</span>
              </div>
            </a>
          </div>


        </div>
      </div>
    </section>

    <!-- Start of events -->
    <section class="section blog-post-entry bg-light">
      <div class="container">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-md-7">
            <h2 class="heading" data-aos="fade-up">Events</h2>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-4 col-md-6 col-sm-6 col-12 post" data-aos="fade-up" data-aos-delay="100">

            <div class="media media-custom d-block mb-4 h-100">
              <a href="#" class="mb-4 d-block"><img src="images/maison.jpg" alt="Image placeholder" class="img-fluid"></a>
              <div class="media-body">
                <span class="meta-post">March 15, 2023</span>
                <h2 class="mt-0 mb-3"><a href="#">Maison Boulud</a></h2>
                <p>Set to make its much anticipated debut in Singapore. Maison Boulud is situated along the waterfront promenade in our hotel. With its interior exuding timeless charm and elegance, indulge in quintessential French dishes that beautifully encapsulates the essence of French cuisine.</p>
              </div>
            </div>

          </div>
          <div class="col-lg-4 col-md-6 col-sm-6 col-12 post" data-aos="fade-up" data-aos-delay="200">
            <div class="media media-custom d-block mb-4 h-100">
              <a href="#" class="mb-4 d-block"><img src="images/winedinner.jpg" alt="Image placeholder" class="img-fluid"></a>
              <div class="media-body">
                <span class="meta-post">March 20, 2023</span>
                <h2 class="mt-0 mb-3"><a href="#">Wakuda Grace Wine Dinner</a></h2>
                <p>Experience an exclusive evening with Head Winemaker Ayana Misawa as you savour an exquisite 5-course wine dinner paired with Grace wines from Yamanashi, Japan.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 col-sm-6 col-12 post" data-aos="fade-up" data-aos-delay="300">
            <div class="media media-custom d-block mb-4 h-100">
              <a href="#" class="mb-4 d-block"><img src="images/afternoontea.jpg" alt="Image placeholder" class="img-fluid"></a>
              <div class="media-body">
                <span class="meta-post">29 March, 2023</span>
                <h2 class="mt-0 mb-3"><a href="#">Renku Bar & Lounge: Afternoon Tea</a></h2>
                <p>Indulge in seasonal blooms and British delicacies with decadent three-tiered afternoon tea sets. Each creation is a delicious symphony of refined flavors that will mesmerize your taste buds.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End of events -->

  <!-- Start of footer -->
  <?php
    include "footer.inc.php";
    ?>
    <!-- End of footer -->
    <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
<script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
    
    <script src="js/bootstrap-datepicker.js"></script> 
    <script src="js/jquery.timepicker.min.js"></script> 
    <script src="js/main.js"></script>
  </body>
</html>