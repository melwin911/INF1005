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
              <img src="images/<?php echo htmlspecialchars($room['image_path'])?>" alt="Room image" class="img-fluid mb-3">
            </figure>
            <div class="p-3 text-center room-info">
              <h2><?php echo htmlspecialchars($room['room_type']); ?></h2>
              <span class="text-uppercase letter-spacing-1"><?php echo htmlspecialchars($room['price_per_night']); ?>$ / per night</span>
              <button onclick="editRoom(<?php echo $room['id']; ?>)">Edit</button>
            </div>
          </div>
          <div class="edit-form" style="display: none;">
            <form method="POST" action="update_room.php">
              <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
              <label for="room_type">Room Type:</label>
              <input type="text" id="room_type" name="room_type" value="<?php echo htmlspecialchars($room['room_type']); ?>"><br>
              <label for="description">Description:</label>
              <textarea id="description" name="description"><?php echo htmlspecialchars($room['description']); ?></textarea><br>
              <label for="price_per_night">Price Per Night:</label>
              <input type="number" id="price_per_night" name="price_per_night" value="<?php echo $room['price_per_night']; ?>"><br>
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
  const roomDiv = document.querySelector(`.room[data-room-id='${roomId}']`);
  const editForm = document.querySelector(`.edit-form[data-room-id='${roomId}']`);
  
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