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
      die("Connection failed: " . $conn->connect_error);
    } 

  // Handle editing
if (isset($_POST['edit_rooms'])) {
  $room_type_id = $_POST['room_type_id'];
  $room_type_name = $_POST['room_type_name'];
  $room_price_sgd = $_POST['room_price_sgd'];

  
  // Perform update query
  $sql = "UPDATE room_details 
          SET room_type_name = '$room_type_name', room_price_sgd = ' $room_price_sgd' 
          WHERE room_type_id = $room_type_id";

  if ($conn->query($sql) === TRUE) {
      echo "Rooms details updated successfully.";
  } else {
      echo "Error updating rooms: " . $conn->error;
  }
}


// Retrieve booking data from the database
$sql = "SELECT * FROM room_details";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // Display data in a table format
  echo "<h2 style='padding-top: 100px; padding-left: 100px; padding-right: 100px; margin-left: 100px;'>Room Details</h2>";
  echo "<table border='1' style='margin-top: 50px; margin-bottom: 50px; padding-left: 50px; padding-right: 50px; margin-left: 50px; margin-right: 30px;'>";
  echo "<tr>
  <th style='text-align: center;'>Room ID</th>
  <th style='text-align: center;'>Room Type Name</th>
  <th>Room Description</th>
  <th style='text-align: center;'>Number of Beds</th>
  <th style='text-align: center;'>Room Pax</th>
  <th style='text-align: center;'>Room Size</th>
  <th style='text-align: center;'>Room Price</th>
  <th style='text-align: center;'>Room Image</th>
  <th style='text-align: center;'>Room Amenities</th>
  <th style='text-align: center;'>Room Features</th>
  <th style='text-align: center;'>Actions</th>
  </tr>";

  while ($row = $result->fetch_assoc()) {
      echo "<tr>";
      echo "<td>" . $row['room_type_id'] . "</td>";
      echo "<td><span contenteditable='true' id='room_type_name_" . $row['room_type_id'] . "'>" . $row['room_type_name'] . "</span></td>";
      echo "<td>" . $row['room_description'] . "</td>";
      echo "<td>" . $row['room_bed'] . "</td>";
      echo "<td>" . $row['room_pax'] . "</td>";
      echo "<td>" . $row['room_size'] . "</td>";
      echo "<td><span contenteditable='true' id='room_price_sgd_" . $row['room_type_id'] . "'>" . $row['room_price_sgd'] . "</span></td>";
      echo "<td>" . $row['room_image_path'] . "</td>";
      echo "<td>" . $row['room_amenities'] . "</td>";
      echo "<td>" . $row['room_features'] . "</td>";
      echo "<td>
          <button onclick='editRow(" . $row['room_type_id'] . ")'>Edit</button>
          <button onclick='saveChanges(" . $row['room_type_id'] . ")' style='display:none'>Save</button>
      </td>";
      echo "</tr>";
  }
  echo "</table>";
} else {
  echo "No rooms found";
}

    $conn->close();
}
?>

<script>
    function editRow(roomId) {
        var elements = document.querySelectorAll('#room_type_name_' + roomId + ', #room_price_sgd_' + roomId);
        elements.forEach(element => {
            element.contentEditable = true;
        });
        document.querySelector('button[onclick="editRow(' + roomId + ')"]').style.display = 'none';
        document.querySelector('button[onclick="saveChanges(' + roomId + ')"]').style.display = 'inline-block';
    }

    function saveChanges(roomId) {
        var roomName = document.getElementById('room_type_name_' + roomId).innerText;
        var roomPrice = document.getElementById('room_price_sgd_' + roomId).innerText;
        var roomIdField = document.createElement('input');
        roomIdField.type = 'hidden';
        roomIdField.name = 'room_type_id';
        roomIdField.value = roomId;
        var form = document.createElement('form');
        form.method = 'post';
        form.appendChild(roomIdField);
        form.innerHTML += '<input type="hidden" name="edit_rooms">';
        form.innerHTML += '<input type="hidden" name="room_type_name" value="' + roomName + '">';
        form.innerHTML += '<input type="hidden" name="room_price_sgd" value="' + roomPrice + '">';
        document.body.appendChild(form);
        form.submit();
    }
</script>


   <!-- Start of footer -->
   <?php
    include "footer.inc.php";
    ?>

  </body>
</html>