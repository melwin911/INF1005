<?php
session_start();

// Retrieve the functions needed for login
require 'functions.php';

// Retrieve the 'SECRET_KEY' value from environment variables and assign it to $secretKey
$secretKey = $_ENV['SECRET_KEY'];

// Initialize variables
$email = $password = $rememberme = "";
$errorMsg = [];
$success = true;

// Form validation and sanitization
if (empty($_POST["email"])) {
    $errorMsg[] = "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg[] = "Invalid email format.<br>";
        $success = false;
    }
}

if (empty($_POST["pwd"])) {
    $errorMsg[] = "Password is required.<br>";
    $success = false;
} else {
    $password = $_POST["pwd"];
}

$rememberme = $_POST['rememberme'];

// Attempt authentication only if the request is POST and $success is still true.
if ($_SERVER["REQUEST_METHOD"] == "POST" && $success) {
    $result = authenticateUser($email, $password, $rememberme, $secretKey);
    $success = $result['success'];
    
    // Check if the user is an admin
    if ($success && $email == 'admin@admin.com' && $password == 'admin') {
        header('Location: view_bookings.php');
    } else {
        // Process the result, which includes handling errors or no errors
        $_SESSION['error'] = $errorMsg;
        include "head.inc.php";
        include "header.inc.php";
        include "login_headsection.inc.php";
        include "footer.inc.php";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !$success) { // For validation and sanitisation failure
    // Display error message if vaidation or sanitisation failed
    $_SESSION['error'] = $errorMsg;
    include "head.inc.php";
    include "header.inc.php";
    include "login_headsection.inc.php";
    include "footer.inc.php";
}
?>
