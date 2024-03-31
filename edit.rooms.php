<?php
session_start();

$headSection = "nonmember_head.inc.php"; // Default to non-member head
$navBar = "navbar.inc.php"; // Default to non-member navbar

// Check if the authentication cookie exists
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Include the member head section if the auth cookie exists
    $headSection = "admin.head.inc.php";
    $navBar = "admin_navbar.inc.php";
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
    renderNavbar('Edit Rooms');
    ?>

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
    }

    $conn->close();
}
?>

<section class="section">
  <div class="container">
    <div class="row">
      <?php foreach ($rooms as $room): ?>
        <div class="col-md-6 col-lg-4 mb-5" data-aos="fade-up">
          <div class="room">
            <figure class="img-wrap">
              <img src="<?php echo htmlspecialchars($room['room_image_path'])?>" alt="<?php echo htmlspecialchars($room['room_type_name'])?>" class="img-fluid mb-3">
            </figure>
            <div class="p-3 text-center room-info">
              <h2><?php echo htmlspecialchars($room['room_type_name']); ?></h2>
              <span class="text-uppercase letter-spacing-1">$ <?php echo htmlspecialchars($room['room_price_sgd']); ?> / per night</span>
              <button onclick="editRoom(<?php echo $room['room_type_id']; ?>)">Edit</button>
            </div>
          </div>
          <div class="edit-form" style="display: none;">
            <form method="POST" action="update_room.php">
              <input type="hidden" name="room_type_id" value="<?php echo $room['room_type_id']; ?>">
              <label for="room_type_name">Room Type:</label>
              <input type="text" id="room_type_name" name="room_type_name" value="<?php echo htmlspecialchars($room['room_type_name']); ?>"><br>
              <label for="room_description">Description:</label>
              <textarea id="room_description" name="room_description"><?php echo htmlspecialchars($room['room_description']); ?></textarea><br>
              <label for="room_bed">Room Bed:</label>
              <textarea id="room_bed" name="room_bed"><?php echo htmlspecialchars($room['room_bed']); ?></textarea><br>
              <label for="room_pax">Room Pax:</label>
              <input type="number" id="room_pax" name="room_pax" value="<?php echo htmlspecialchars($room['room_pax']); ?>"><br>
              <label for="room_size">Room Size:</label>
              <textarea id="room_size" name="room_size"><?php echo htmlspecialchars($room['room_size']); ?></textarea><br>
              <label for="room_price_sgd">Price Per Night:</label>
              <input type="number" id="room_price_sgd" name="room_price_sgd" value="<?php echo $room['room_price_sgd']; ?>"><br>
              <label for="room_image_path">Room Image Path:</label>
              <textarea id="room_image_path" name="room_image_path"><?php echo htmlspecialchars($room['room_image_path']); ?></textarea><br>
              <label for="room_amenities">Room Amenities:</label>
              <textarea id="room_amenities" name="room_amenities"><?php echo htmlspecialchars($room['room_amenities']); ?></textarea><br>
              <label for="room_features">Room Features:</label>
              <textarea id="room_features" name="room_features"><?php echo htmlspecialchars($room['room_features']); ?></textarea><br>
              <button type="submit">Save</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
      
      <?php if (empty($rooms)): ?>
        <p>No rooms available.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
function editRoom(roomId) {
  const roomDiv = document.querySelector(`.room[data-room-id='${room_type_id}']`);
  const editForm = document.querySelector(`.edit-form[data-room-id='${room_type_id}']`);
  
  if (roomDiv && editForm) {
    roomDiv.style.display = 'none';
    editForm.style.display = 'block';
  }
}
</script>
    
    
   <!-- Start of footer -->
   <?php
    include "footer.inc.php";
    ?>

  </body>
</html>