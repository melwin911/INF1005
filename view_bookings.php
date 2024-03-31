<?php
session_start();

$headSection = "admin.head.inc.php";

// Check if the user is not logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to index.php if the user is not logged in
    header('Location: index.php');
    exit;
}
$email = $_SESSION['email'];
?>

<!DOCTYPE HTML>
<html lang="en">
<?php
    include "head.inc.php";
    ?>

  <body>
  <?php
    include "header.inc.php";
    include "admin_navbar.inc.php";
    include $headSection;
    renderNavbar('View Bookings');
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
if (isset($_POST['edit_booking'])) {
    $booking_id = $_POST['booking_id'];
    // Redirect to edit booking page with booking ID as a query parameter
    header("Location: view_bookings.php?action=edit&id=$booking_id");
    exit;
}

// Handle deletion
if (isset($_POST['delete_booking'])) {
    $booking_id = $_POST['booking_id'];
    // Perform delete query
    $sql = "DELETE FROM bookings WHERE booking_id = $booking_id";
    if ($conn->query($sql) === TRUE) {
        echo "Booking deleted successfully.";
    } else {
        echo "Error deleting booking: " . $conn->error;
    }
}

// Retrieve booking data from the database
$sql = "SELECT * FROM bookings";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Display data in a table format
    echo "<h2 style='padding-top: 100px; padding-left: 100px; padding-right: 100px; margin-left: 100px;'>Total Bookings</h2>";
echo "<table border='1' style='margin-top: 50px; margin-bottom: 50px; padding-left: 100px; padding-right: 100px; margin-left: 100px;'>";
echo "<tr>
    <th style='text-align: center;'>Booking ID</th>
    <th style='text-align: center;'>Member ID</th>
    <th style='text-align: center;'>Room ID</th>
    <th style='text-align: center;'>Check-in Date</th>
    <th style='text-align: center;'>Check-out Date</th>
    <th style='text-align: center;'>Total Price</th>
    <th style='text-align: center;'>Number of Rooms</th>
    <th style='text-align: center;'>Number of Guests</th>
    <th style='text-align: center;'>Guest Name</th>
    <th style='text-align: center;'>Guest Email</th>
    <th style='text-align: center;'>Guest Phone</th>
    <th style='text-align: center;'>Actions</th>
    </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td style='text-align: center;'>" . $row['booking_id'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['member_id'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['room_id'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['check_in_date'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['check_out_date'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['total_price'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['num_rooms'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['num_guests'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['guest_name'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['guest_email'] . "</td>";
    echo "<td style='text-align: center;'>" . $row['guest_phone'] . "</td>";
    echo "<td style='text-align: center;'>
        <form method='post'>
            <input type='hidden' name='booking_id' value='" . $row['booking_id'] . "'>
            <input type='submit' name='edit_booking' value='Edit'>
            <input type='submit' name='delete_booking' value='Delete' onclick='return confirm(\"Are you sure?\")'>
        </form>
    </td>";
    echo "</tr>";
}
echo "</table>";
} else {
    echo "No bookings found";
}
$conn->close();
}
?>

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