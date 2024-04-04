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
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];
    $guest_phone = $_POST['guest_phone'];
    
    // Perform update query
    $sql = "UPDATE bookings 
            SET guest_name = '$guest_name', guest_email = '$guest_email', guest_phone = '$guest_phone' 
            WHERE booking_id = $booking_id";

    if ($conn->query($sql) === TRUE) {
        echo "Booking updated successfully.";
    } else {
        echo "Error updating booking: " . $conn->error;
    }
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
    echo "<main>";
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
        echo "<td>" . $row['booking_id'] . "</td>";
        echo "<td>" . $row['member_id'] . "</td>";
        echo "<td>" . $row['room_id'] . "</td>";
        echo "<td>" . $row['check_in_date'] . "</td>";
        echo "<td>" . $row['check_out_date'] . "</td>";
        echo "<td>" . $row['total_price'] . "</td>";
        echo "<td>" . $row['num_rooms'] . "</td>";
        echo "<td>" . $row['num_guests'] . "</td>";
        echo "<td><span contenteditable='true' id='guest_name_" . $row['booking_id'] . "'>" . $row['guest_name'] . "</span></td>";
        echo "<td><span contenteditable='true' id='guest_email_" . $row['booking_id'] . "'>" . $row['guest_email'] . "</span></td>";
        echo "<td><span contenteditable='true' id='guest_phone_" . $row['booking_id'] . "'>" . $row['guest_phone'] . "</span></td>";
        echo "<td>
            <button onclick='editRow(" . $row['booking_id'] . ")'>Edit</button>
            <button onclick='saveChanges(" . $row['booking_id'] . ")' style='display:none'>Save</button>
            <form method='post' style='display: inline;'>
                <input type='hidden' name='booking_id' value='" . $row['booking_id'] . "'>
                <input type='submit' name='delete_booking' value='Delete' onclick='return confirm(\"Are you sure?\")'>
            </form>
        </td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</main>";
} else {
    echo "No bookings found";
}
$conn->close();
}
?>

<script>
    function editRow(bookingId) {
        var elements = document.querySelectorAll('#guest_name_' + bookingId + ', #guest_email_' + bookingId + ', #guest_phone_' + bookingId);
        elements.forEach(element => {
            element.contentEditable = true;
        });
        document.querySelector('button[onclick="editRow(' + bookingId + ')"]').style.display = 'none';
        document.querySelector('button[onclick="saveChanges(' + bookingId + ')"]').style.display = 'inline-block';
    }

    function saveChanges(bookingId) {
        var guestName = document.getElementById('guest_name_' + bookingId).innerText;
        var guestEmail = document.getElementById('guest_email_' + bookingId).innerText;
        var guestPhone = document.getElementById('guest_phone_' + bookingId).innerText;
        var bookingIdField = document.createElement('input');
        bookingIdField.type = 'hidden';
        bookingIdField.name = 'booking_id';
        bookingIdField.value = bookingId;
        var form = document.createElement('form');
        form.method = 'post';
        form.appendChild(bookingIdField);
        form.innerHTML += '<input type="hidden" name="edit_booking">';
        form.innerHTML += '<input type="hidden" name="guest_name" value="' + guestName + '">';
        form.innerHTML += '<input type="hidden" name="guest_email" value="' + guestEmail + '">';
        form.innerHTML += '<input type="hidden" name="guest_phone" value="' + guestPhone + '">';
        document.body.appendChild(form);
        form.submit();
    }
</script>

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