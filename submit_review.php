<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['member_id'])) {
    // Redirect if not logged in
    header('Location: login.php');
    exit;
}

// Initialize variables
$memberId = $_SESSION['member_id'];
$roomTypeId = $bookingId = $rating = $reviewText = "";
$success = true;
$errorMsg = "";

// Check if form fields are set
if (empty($_POST['room_type_id'])) {
    $errorMsg .= "Room Type is required.<br>";
    $success = false;
} else {
    $roomTypeId = sanitize_input($_POST["room_type_id"]);
}

if (empty($_POST["booking_id"])) {
    $errorMsg .= "Booking ID is required.";
    $success = false;
} else {
    $bookingId = sanitize_input($_POST["booking_id"]);
}

if (empty($_POST["rating"])) {
    $errorMsg .= "Rating is required.";
    $success = false;
} else {
    $rating = sanitize_input($_POST["rating"]);
}

if (empty($_POST["review_text"])) {
    $errorMsg .= "Review Text is required.";
    $success = false;
} else {
    $reviewText = sanitize_input($_POST["review_text"]);
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
            // Insert review into the database
            $stmt = $conn->prepare("INSERT INTO reviews (member_id, room_type_id, booking_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiids", $memberId, $roomTypeId, $bookingId, $rating, $reviewText);
            $stmt->execute();

            // Check if the review was successfully inserted
            if ($stmt->affected_rows > 0) {
                // Review submitted successfully
                // Redirect or display a success message
                header('Location: reviews.php?room_type_id='  . $roomTypeId);
                exit;
            } else {
                // Review submission failed
                // Redirect or display an error message
                $_SESSION['error_msg'] = $errorMsg; // Storing error message in session
                header('Location: reviews.php?room_type_id='  . $roomTypeId);
                exit;
            }

            $stmt->close();
            $conn->close();
        }
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
