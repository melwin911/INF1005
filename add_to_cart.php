<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['member_id'])) {
    // Redirect to index.php if the user is not logged in
    header('Location: rooms.php');
    exit;
}

// Initialize variables
$memberId = $_SESSION['member_id'];
$roomTypeId = $checkInDate = $checkOutDate = $numRooms = $numGuests = $guestName = $guestEmail = $guestPhone = "";
$success = true;
$errorMsg = "";

// Check if form fields are set
if (empty($_POST['room_type_id'])) {
    $errorMsg .= "Room Type is required.<br>";
    $success = false;
} else {
    $roomTypeId = sanitize_input($_POST["room_type_id"]);
}

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

        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
                if ($success) {
                    $cartId = "";
                    // Check for an existing cart
                    $stmt = $conn->prepare("SELECT cart_id FROM carts WHERE member_id = ?");
                    $stmt->bind_param("i", $memberId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $cart = $result->fetch_assoc();
                        $cartId = $cart['cart_id'];
                    } else {
                        // Create new cart
                        $stmt = $conn->prepare("INSERT INTO carts (member_id, created_at, updated_at) VALUES (?, NOW(), NOW())");
                        $stmt->bind_param("i", $memberId);
                        $stmt->execute();
                        $cartId = $stmt->insert_id;
                    }

                    // Insert booking into cart_items
                    $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, room_type_id, check_in_date, check_out_date, num_rooms, num_guests, guest_name, guest_email, guest_phone, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->bind_param("iissiisss", $cartId, $roomTypeId, $checkInDate, $checkOutDate, $numRooms, $numGuests, $guestName, $guestEmail, $guestPhone);
                    $stmt->execute();
                    $stmt->close();
                }
                else {
                    $_SESSION['error_msg'] = $errorMsg; // Storing error message in session
                    header('Location: booking.php'); // Redirecting back to the booking form or to an error page
                    exit;
                }
            }
        $conn->close();
        header('Location: view_cart.php'); // Redirect to cart view
        exit;
    }
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
