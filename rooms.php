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
  renderNavbar('Rooms');
  ?>

<main>
  <section class="section bg-light pb-0">
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
                      <select name="" id="adults" class="form-control" style="color: #333;">
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
                      <select name="" id="children" class="form-control" style="color: #333;">
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
                <button type="submit" class="btn btn-primary btn-block" style="color: #333;">Check Availabilty</button>
              </div>
            </div>
          </form>
          <div id="availabilityResult"></div>
        </div>
      </div>
    </div>
  </section>

  <?php

  // Initialize variables
  $errorMsg = "";
  $rooms = [];
  $availabilityData = [];
  $success = true;
  $reviewsData = [];

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
        while ($row = $result->fetch_assoc()) {
          $rooms[] = $row;
        }
      } else {
        $errorMsg = "No rooms found.";
        $success = false;
      }
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

    if ($conn->connect_error) {
      $errorMsg = "Connection failed: " . $conn->connect_error;
      $success = false;
    } else {
      // Check if the form data is sent via POST
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize and validate form data (to prevent SQL injection, etc.)
        $checkin_date = $_POST['checkin_date'];
        $checkout_date = $_POST['checkout_date'];
        $rooms_requested = $_POST['rooms'];
        $guests = $_POST['guests'];

        // Perform any necessary processing on form data

        // Query to fetch availability data from the database
        $sql = "SELECT rt.room_type, rt.price_per_night, SUM(r.availability) AS total_availability FROM room_types rt JOIN rooms r ON rt.room_type_id = r.room_type_id GROUP BY rt.room_type, rt.price_per_night";

        // Execute the query
        $result = $conn->query($sql);

        // Check if the query was successful
        if ($result) {
          // Fetch the data from the result set
          while ($row = $result->fetch_assoc()) {
            $availabilityData[] = $row;
          }
          exit(); // Stop further execution
        } else {
          // Handle database query errors
          $errorMsg = "Database query error.";
          $success = false;
        }
      }
    }
    $conn->close();
  }
  ?>

  <div class="row justify-content-center text-center mb-5">
    <div class="col-md-5 mb-2">
      <br>
      <h2 class="heading" data-aos="fade-up">Our Rooms</h2>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <?php foreach ($rooms as $room) : ?>
        <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
          <a class="room_info_card" id="<?php echo htmlspecialchars($room['room_type_id']) ?>" data-toggle="modal" data-target=".room_page_<?php echo htmlspecialchars($room['room_type_id']) ?>" href="">
            <figure class="img-wrap">
              <img src="<?php echo htmlspecialchars($room['room_image_path']) ?>" alt="<?php echo htmlspecialchars($room['room_type_name']) ?> image" class="img-fluid mb-3">
            </figure>
            <div class="p-3 text-center room-info">
              <h2><?php echo htmlspecialchars($room['room_type_name']); ?></h2>
              <?php if (isset($reviewsData[$room['room_type_id']])) : ?>
                <div class="room-rating">
                  <span class="average-rating">
                    <?php
                    // Display solid stars for the whole number part of the rating
                    for ($i = 0; $i < floor($reviewsData[$room['room_type_id']]['average_rating']); $i++) {
                      echo '<i class="fa fa-star" aria-hidden="true" style="color: #333;"></i>';
                    }
                    // If there's a half, display a half star
                    if ($reviewsData[$room['room_type_id']]['average_rating'] - floor($reviewsData[$room['room_type_id']]['average_rating']) >= 0.5) {
                      echo '<i class="fa fa-star-half-alt" aria-hidden="true" style="color: #333;"></i>';
                    }
                    ?>
                  </span>
                  <span class="review-count" style="color: #333;">(<?php echo $reviewsData[$room['room_type_id']]['review_count']; ?> reviews)</span>
                </div>
              <?php else : ?>
                <div class="room-rating">
                  <span class="no-reviews" style="color: #333;">No reviews yet</span>
                </div>
              <?php endif; ?>
              <span class="text-uppercase letter-spacing-1" style="color: #333;">$ <?php echo htmlspecialchars($room['room_price_sgd']); ?> / per night</span>
            </div>
          </a>
        </div>
        <div>
          <div class="modal fade room_page_<?php echo htmlspecialchars($room['room_type_id']) ?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-label="Room Details" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <div class="modal-header" style="border-bottom: none;">
                  <h3 class="modal-title" style="font-weight: bold;" id="room_page_<?php echo htmlspecialchars($room['room_type_id']) ?>_title"><?php echo htmlspecialchars($room['room_type_name']) ?></h3>
                  <button type="button" class="close modal-title" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <img src="<?php echo htmlspecialchars($room['room_image_path']) ?>" alt="<?php echo htmlspecialchars($room['room_type_name']) ?> image" class="img-fluid mb-3">
                  <?php if (isset($reviewsData[$room['room_type_id']])) : ?>
                    <a href="reviews.php?room_type_id=<?php echo $room['room_type_id']; ?>" class="room-rating-link">
                      <div class="room-rating">
                        <span class="average-rating">
                          <?php
                          // Display solid stars for the whole number part of the rating
                          for ($i = 0; $i < floor($reviewsData[$room['room_type_id']]['average_rating']); $i++) {
                            echo '<i class="fa fa-star" aria-hidden="true" style="color: #333;"></i>';
                          }
                          // If there's a half, display a half star
                          if ($reviewsData[$room['room_type_id']]['average_rating'] - floor($reviewsData[$room['room_type_id']]['average_rating']) >= 0.5) {
                            echo '<i class="fa fa-star-half-alt" aria-hidden="true" style="color: #333;"></i>';
                          }
                          ?>
                        </span>
                        <span class="review-count" style="color: #333;">(<?php echo $reviewsData[$room['room_type_id']]['review_count']; ?> reviews)</span>
                      </div>
                    </a>
                  <?php else : ?>
                    <div class="room-rating">
                      <span class="no-reviews" style="color: #333;">No reviews yet</span>
                    </div>
                  <?php endif; ?>
                  <p><?php echo htmlspecialchars($room['room_description']) ?> pax</p>
                  <img src="images/person-fill.svg" alt="Person" height="24px"><span class="fw-bold ps-2">Number of Guests</span>
                  <p class="ps-2"><?php echo htmlspecialchars($room['room_pax']) ?></p>
                  <img src="images/bed_icon.png" alt="Bed" height="24px"><span class="fw-bold ps-2">Number of Beds</span>
                  <p class="ps-2"><?php echo htmlspecialchars($room['room_bed']) ?></p>
                  <img src="images/arrows-angle-expand.svg" alt="Size" height="20px"><span class="fw-bold ps-3">Room Size</span>
                  <p class="ps-2"><?php echo htmlspecialchars($room['room_size']) ?></p>
                  <img src="images/lamp-fill.svg" alt="Lamp" height="24px"><span class="fw-bold ps-2">Room Amenitites</span>
                  <p class="ps-2"><?php echo htmlspecialchars($room['room_amenities']) ?></p>
                </div>
                <div class="modal-footer" style="border-top: none;">
                  <span class="display-4 text-primary" style="color: #000000 !important; background-color: #ffffff !important; font-size: 2.5rem;">$<?php echo htmlspecialchars($room['room_price_sgd']) ?></span> <span class="text-uppercase letter-spacing-1">/ per night</span>
                  <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
                    <p><a href="booking.php?room_type_id=<?php echo $room['room_type_id']; ?>" class="btn btn-primary text-black" style="color: #333 !important;">Book Now</a></p>
                  <?php else : ?>
                    <p><button class="btn btn-primary text-black" disabled data-toggle="tooltip" data-placement="top" title="Please log in to book rooms." style="color: #333 !important;">Book Now</button></p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($rooms)) : ?>
        <p>No rooms available.</p>
      <?php endif; ?>
    </div>
  </div>

  <section class="section bg-light">

    <div class="container">
      <div class="row justify-content-center text-center mb-5">
        <div class="col-md-7">
          <h2 class="heading" data-aos="fade">Great Offers</h2>
        </div>
      </div>

      <?php foreach ($rooms as $room) :
        if ($room['room_type_id'] == 11) { ?>
          <div class="site-block-half d-block d-lg-flex bg-white" data-aos="fade" data-aos-delay="100">
            <a href="/rooms.php" aria-label="First Room Image" class="image d-block bg-image-2" style="background-image: url('images/img_3.jpg');"></a>
            <div class="text">
              <span class="d-block mb-4"><span class="display-4 text-primary" style="color: #000000 !important; background-color: #ffffff !important;">$<?php echo htmlspecialchars($room['room_price_sgd']) ?></span> <span class="text-uppercase letter-spacing-2">/ per night</span> </span>
              <h2 class="mb-4" aria-label="Room Type"><?php echo htmlspecialchars($room['room_name']) ?></h2>
              <p><?php echo htmlspecialchars($room['room_size']) ?> | <?php echo htmlspecialchars($room['room_features']) ?></p>
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
                <p><a href="/booking.php?room_type_id=<?php echo htmlspecialchars($room['room_type_id']) ?>" class="btn btn-primary text-black" style="color: #333 !important;">Book Now</a></p>
              <?php else : ?>
                <p><button class="btn btn-primary text-black" disabled data-toggle="tooltip" data-placement="top" title="Please log in to book rooms." style="color: #333 !important;">Book Now</button></p>
              <?php endif; ?>
            </div>
          </div>
        <?php }
        if ($room['room_type_id'] == 17) { ?>
          <div class="site-block-half d-block d-lg-flex bg-white" data-aos="fade" data-aos-delay="200">
            <a href="/rooms.php" aria-label="Second Room Image" class="image d-block bg-image-2 order-2" style="background-image: url('images/img_4.jpg');"></a>
            <div class="text order-1">
              <span class="d-block mb-4"><span class="display-4 text-primary" style="color: #000000 !important; background-color: #ffffff !important;">$<?php echo htmlspecialchars($room['room_price_sgd']) ?></span> <span class="text-uppercase letter-spacing-2">/ per night</span> </span>
              <h2 class="mb-4" aria-label="Room Type"><?php echo htmlspecialchars($room['room_name']) ?></h2>
              <p><?php echo htmlspecialchars($room['room_size']) ?> | <?php echo htmlspecialchars($room['room_features']) ?> | Club access</p>
              <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) : ?>
                <p><a href="/booking.php?room_type_id=<?php echo htmlspecialchars($room['room_type_id']) ?>" class="btn btn-primary text-black" style="color: #333 !important;">Book Now</a></p>
              <?php else : ?>
                <p><button class="btn btn-primary text-black" disabled data-toggle="tooltip" data-placement="top" title="Please log in to book rooms." style="color: #333 !important;">Book Now</button></p>
              <?php endif; ?>
            </div>
          </div>
      <?php }
      endforeach; ?>
    </div>
  </section>
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
  <script>
    $(document).ready(function() {
      $('[data-toggle="tooltip"]').tooltip(); // Initialize Bootstrap tooltips
    });
  </script>

</body>

</html>