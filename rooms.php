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

    <?php
    
// Initialize variables
$errorMsg = "";
$rooms = [];
$availabilityData = [];
$success = true;

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
        $sql = "SELECT room_type, description, price_per_night, image_path FROM room_types";
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
              while($row = $result->fetch_assoc()) {
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

<section class="section">
  <div class="container">
    <div class="row">
      <?php foreach ($rooms as $room): ?>
        <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
          <a href="#" class="room">
            <figure class="img-wrap">
              <img src="images/<?php echo htmlspecialchars($room['image_path'])?>" alt="Room image" class="img-fluid mb-3">
            </figure>
            <div class="p-3 text-center room-info">
              <h2><?php echo htmlspecialchars($room['room_type']); ?></h2>
              <span class="text-uppercase letter-spacing-1"><?php echo htmlspecialchars($room['price_per_night']); ?>$ / per night</span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
      
      <?php if (empty($rooms)): ?>
        <p>No rooms available.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
    
    <!-- <section class="section">
      <div class="container">
        
        <div class="row">
          <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
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

          <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
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

          <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
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

          <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
            <a href="#" class="room">
              <figure class="img-wrap">
                <img src="images/img_5.jpg" alt="Free website template" class="img-fluid mb-3">
              </figure>
              <div class="p-3 text-center room-info">
                <h2>Courtyard Room</h2>
                <span class="text-uppercase letter-spacing-1">150$ / per night</span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
            <a href="#" class="room">
              <figure class="img-wrap">
                <img src="images/img_2.jpg" alt="Free website template" class="img-fluid mb-3">
              </figure>
              <div class="p-3 text-center room-info">
                <h2>Quay Room</h2>
                <span class="text-uppercase letter-spacing-1">200$ / per night</span>
              </div>
            </a>
          </div>

          <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
            <a href="#" class="room">
              <figure class="img-wrap">
                <img src="images/img_4.jpg" alt="Free website template" class="img-fluid mb-3">
              </figure>
              <div class="p-3 text-center room-info">
                <h2>Presidential Suite</h2>
                <span class="text-uppercase letter-spacing-1">350$ / per night</span>
              </div>
            </a>
          </div>

        </div>
      </div>
    </section> -->
    
    <section class="section bg-light">

      <div class="container">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-md-7">
            <h2 class="heading" data-aos="fade">Great Offers</h2>
          </div>
        </div>
      
        <div class="site-block-half d-block d-lg-flex bg-white" data-aos="fade" data-aos-delay="100">
          <a href="#" class="image d-block bg-image-2" style="background-image: url('images/img_3.jpg');"></a>
          <div class="text">
            <span class="d-block mb-4"><span class="display-4 text-primary">$249</span> <span class="text-uppercase letter-spacing-2">/ per night</span> </span>
            <h2 class="mb-4">Presidential Room</h2>
            <p>388–420sqf | 36–39sqm | Bay view</p>
            <p><a href="login.php" class="btn btn-primary text-white">Book Now</a></p>
          </div>
        </div>
        <div class="site-block-half d-block d-lg-flex bg-white" data-aos="fade" data-aos-delay="200">
          <a href="#" class="image d-block bg-image-2 order-2" style="background-image: url('images/img_4.jpg');"></a>
          <div class="text order-1">
            <span class="d-block mb-4"><span class="display-4 text-primary">$349</span> <span class="text-uppercase letter-spacing-2">/ per night</span> </span>
            <h2 class="mb-4">Presidential Suite</h2>
            <p>970sqf | 90sqm | Bay view / City view | Club access</p>
            <p><a href="login.php" class="btn btn-primary text-white">Book Now</a></p>
          </div>
        </div>

      </div>
    </section>
    
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