<?php
session_start();
// Check if the item_id is set in the session
if (!isset($_SESSION['edit_booking_id'])) {
    header('Location: view_cart.php');
    exit;
}

$bookingId = $_SESSION['edit_booking_id'];

// Initialize variables
$memberId = $_SESSION['member_id'];
$checkInDate = $checkOutDate = $numRooms = $numGuests = $guestName = $guestEmail = $guestPhone = "";
$success = true;
$errorMsg = "";

// Check if form fields are set
if (empty($_POST["check_in_date"])) {
    $errorMsg .= "Check in date is required.";
    $success = false;
} else {
    $checkInDate = sanitize_input($_POST["check_in_date"]);
}

if (empty($_POST["check_out_date"])) {
    $errorMsg .= "Check out date is required.";
    $success = false;
} else {
    $checkOutDate = sanitize_input($_POST["check_out_date"]);
}

if (empty($_POST["num_rooms"])) {
    $errorMsg .= "Number of rooms is required.";
    $success = false;
} else {
    $numRooms = sanitize_input($_POST["num_rooms"]);
}

if (empty($_POST["num_guests"])) {
    $errorMsg .= "Number of guests is required.";
    $success = false;
} else {
    $numGuests = sanitize_input($_POST["num_guests"]);
}

if (empty($_POST["guest_name"])) {
    $errorMsg .= "Guest name is required.";
    $success = false;
} else {
    $guestName = sanitize_input($_POST["guest_name"]);
}

if (empty($_POST["guest_email"])) {
    $errorMsg .= "Guest email is required.<br>";
    $success = false;
} else {
    $guestEmail = sanitize_input($_POST["guest_email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.";
        $success = false;
    }
}

if (empty($_POST["guest_phone"])) {
    $errorMsg .= "Guest phone number is required.";
    $success = false;
} else {
    $guestPhone = sanitize_input($_POST["guest_phone"]);
}

if ($success) {
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
    // Fetch the booking details
    $stmt = $conn->prepare("UPDATE cart_items SET check_in_date = ?, check_out_date = ?, num_rooms = ?, num_guests = ?, guest_name = ?, guest_email = ?, guest_phone = ? WHERE item_id = ?");
    $stmt->bind_param("ssiisssi", $checkInDate, $checkOutDate, $numRooms, $numGuests, $guestName, $guestEmail, $guestPhone, $bookingId);

    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Booking updated successfully.";
    } else {
        $_SESSION['error_msg'] = "Error updating booking: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    unset($_SESSION['edit_booking_id']);
    header('Location: view_cart.php');
    exit;
}
unset($_SESSION['edit_booking_id']);
}

/*
* Helper function that checks input for malicious or unwanted content.
*/
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>