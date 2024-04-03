<?php
session_start();
$_SESSION['loggedin'] = false;
require 'functions.php'; // Assumes this file contains the rememberMe function among others.
$secretKey = $_ENV['SECRET_KEY']; // Ensure the secret key is defined in your environment variables.

if (isset($_COOKIE['rememberme'])) {
    $validationResult = rememberMe($secretKey);
    if ($validationResult) {
        header('Location: member_page.php');
        exit;
    }
}

$headSection = "nonmember_head.inc.php";

?>

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
            include $headSection;
            renderNavbar('Home');
            ?>
            <!-- End of navbar  -->

   <main>
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
                    <input type="text" id="checkin_date" class="form-control" style="color: #333;">
                  </div>
                </div>
                <div class="col-md-6 mb-3 mb-lg-0 col-lg-3">
                  <label for="checkout_date" class="font-weight-bold text-black">Check Out</label>
                  <div class="field-icon-wrap">
                    <div class="icon"><span class="icon-calendar"></span></div>
                    <input type="text" id="checkout_date" class="form-control" style="color: #333;">
                  </div>
                </div>
                <div class="col-md-6 mb-3 mb-md-0 col-lg-3">
                  <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                      <label for="adults" class="font-weight-bold text-black">No. of Rooms</label>
                      <div class="field-icon-wrap">
                        <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                        <select name="" id="adults" class="form-control" style="color: #333;">
                          <option value="" style="color: #333;">1</option>
                          <option value="" style="color: #333;">2</option>
                          <option value="" style="color: #333;">3</option>
                          <option value="" style="color: #333;">4</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6 mb-3 mb-md-0">
                      <label for="pax" class="font-weight-bold text-black">Pax</label>
                      <div class="field-icon-wrap">
                        <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                        <select name="" id="pax" class="form-control" style="color: #333;">
                          <option value="" style="color: #333;">1</option>
                          <option value="" style="color: #333;">2</option>
                          <option value="" style="color: #333;">3</option>
                          <option value="" style="color: #333;">4+</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-3 align-self-end">
                <button type="submit" class="btn btn-primary btn-block" style="color: #333;">Check Availability</button>
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
            <p class="mb-4" style="color: #333;">Welcome to our exquisite hotel, where luxury meets comfort and every stay is a memorable experience. Nestled in the heart of Singapore, our hotel boasts stunning views, modern amenities, and unparalleled hospitality. Whether you're here for a relaxing getaway, a business trip, or a special occasion, our dedicated team is committed to ensuring your stay is nothing short of perfection. From elegantly appointed rooms to gourmet dining options and top-notch services, we invite you to indulge in a world of sophistication and relaxation at our esteemed hotel.</p>
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
        <?php
          // Initialize variables
          $errorMsg = "";
          $rooms = [];
          $availabilityData = [];
          $reviewsData = [];
          $success = true;
          $room_listing_count = 0;
          
          // Create database connection using the existing config file
          $config = parse_ini_file('/var/www/private/db-config.ini');
          if (!$config) {
              $errorMsg = "Failed to read database config file.";
              $success = false;
          } else {
              $conn = new mysqli(
                  $config['servername'],
                  $config['username'],
                  $config['password'],
                  $config['dbname']
              );
              // Check connection
              if ($conn->connect_error) {
                  $errorMsg = "Connection failed: " . $conn->connect_error;
                  $success = false;
              } else {
                  // Prepare the SQL statement to select room data
                  //$sql = "SELECT room_type_id, room_type_name, room_description, room_bed, roon_pax, room_size, room_price_sgd, room_image_path, room_amenities FROM room_details";
                  $sql = "SELECT * FROM room_details";
                  $result = $conn->query($sql);
          
                  if ($result && $result->num_rows > 0) {
                      // Fetch all room data
                      while($row = $result->fetch_assoc()) {
                          $rooms[] = $row;
                      }
                  } else {
                      $errorMsg = "No rooms found.";
                      $success = false;
                  }

            // Get average rating and review count for each room
            $reviewSql = "SELECT room_type_id, AVG(rating) AS average_rating, COUNT(*) AS review_count FROM reviews GROUP BY room_type_id";
            $reviewResult = $conn->query($reviewSql);

            if ($reviewResult && $reviewResult->num_rows > 0) {
              // Fetch all review data
              while ($reviewRow = $reviewResult->fetch_assoc()) {
                $reviewsData[$reviewRow['room_type_id']] = $reviewRow;
              }
            }
              }
              $conn->close();
          }

          foreach ($rooms as $room): 
            if ($room_listing_count < 3) { ?>
          <div class="col-md-6 col-lg-4" data-aos="fade-up">
            <a href="/rooms.php" class="room">
              <figure class="img-wrap">
                <img src="<?php echo htmlspecialchars($room['room_image_path'])?>" alt="<?php echo htmlspecialchars($room['room_type_name'])?> image" class="img-fluid mb-3">
              </figure>
              <div class="p-3 text-center room-info">
                <h2><?php echo htmlspecialchars($room['room_type_name']); ?></h2>
                <?php if (isset($reviewsData[$room['room_type_id']])) : ?>
                <div class="room-rating">
                  <span class="average-rating">
                    <?php
                    // Display solid stars for the whole number part of the rating
                    for ($i = 0; $i < floor($reviewsData[$room['room_type_id']]['average_rating']); $i++) {
                      echo '<i class="fa fa-star" aria-hidden="true"></i>';
                    }
                    // If there's a half, display a half star
                    if ($reviewsData[$room['room_type_id']]['average_rating'] - floor($reviewsData[$room['room_type_id']]['average_rating']) >= 0.5) {
                      echo '<i class="fa fa-star-half-alt" aria-hidden="true"></i>';
                    }
                    ?>
                  </span>
                  <span class="review-count">(<?php echo $reviewsData[$room['room_type_id']]['review_count']; ?> reviews)</span>
                </div>
              <?php else : ?>
                <div class="room-rating">
                  <span class="no-reviews">No reviews yet</span>
                </div>
              <?php endif; ?>
                <span class="text-uppercase letter-spacing-1" style="color: #333;">$ <?php echo htmlspecialchars($room['room_price_sgd']); ?> / per night</span>
              </div>
            </a>
          </div>
          <?php $room_listing_count++;} endforeach; ?>
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
              <span class="meta-post" style="color: #333;">March 15, 2023</span>
                <h2 class="mt-0 mb-3"><a href="#">Maison Boulud</a></h2>
                <p>Set to make its much anticipated debut in Singapore. Maison Boulud is situated along the waterfront promenade in our hotel. With its interior exuding timeless charm and elegance, indulge in quintessential French dishes that beautifully encapsulates the essence of French cuisine.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 col-sm-6 col-12 post" data-aos="fade-up" data-aos-delay="200">
            <div class="media media-custom d-block mb-4 h-100">
              <a href="#" class="mb-4 d-block"><img src="images/winedinner.jpg" alt="Image placeholder" class="img-fluid"></a>
              <div class="media-body">
              <span class="meta-post" style="color: #333;">March 20, 2023</span>
                <h2 class="mt-0 mb-3"><a href="#">Wakuda Grace Wine Dinner</a></h2>
                <p>Experience an exclusive evening with Head Winemaker Ayana Misawa as you savour an exquisite 5-course wine dinner paired with Grace wines from Yamanashi, Japan.</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6 col-sm-6 col-12 post" data-aos="fade-up" data-aos-delay="300">
            <div class="media media-custom d-block mb-4 h-100">
              <a href="#" class="mb-4 d-block"><img src="images/afternoontea.jpg" alt="Image placeholder" class="img-fluid"></a>
              <div class="media-body">
              <span class="meta-post" style="color: #333;">29 March, 2023</span>
                <h2 class="mt-0 mb-3"><a href="#">Renku Bar & Lounge: Afternoon Tea</a></h2>
                <p>Indulge in seasonal blooms and British delicacies with decadent three-tiered afternoon tea sets. Each creation is a delicious symphony of refined flavors that will mesmerize your taste buds.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End of events -->
</main>

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