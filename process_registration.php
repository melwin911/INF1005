<?php
require 'functions.php';

// Initialize variables
$email = $firstName = $lastName = $gender = $password = $confirmPassword = "";
$errorMsg = [];
$success = true;

// Check if form fields are set
if (empty($_POST["email"])) {
    $errorMsg[] = "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg[] = "Invalid email format.";
        $success = false;
    } elseif (emailExists($email)) {// Check if email already exists in the database
        $errorMsg[] = "Email already exists.";
        $success = false;
    }
}

if (empty($_POST["gender"])) {
    $errorMsg[] = "gender is required.";
    $success = false;
} else {
    $gender = sanitize_input($_POST["gender"]);
}

if (empty($_POST["lname"])) {
    $errorMsg[] = "Last Name is required.";
    $success = false;
} else {
    $lastName = sanitize_input($_POST["lname"]);
}

if (!empty($_POST["fname"])) {
    $firstName = sanitize_input($_POST["fname"]);
}

if (empty($_POST["pwd"])) {
    $errorMsg[] = "Password is required.";
    $success = false;
} else {
    $password = $_POST["pwd"]; // No need to sanitize password because it typically contain special characters
}

if (empty($_POST["pwd_confirm"])) {
    $errorMsg[] = "Confirm Password is required.";
    $success = false;
} else {
    $confirmPassword = $_POST["pwd_confirm"];
    if ($confirmPassword !== $password) {
        $errorMsg[] = "Passwords do not match.";
        $success = false;
    }
}

session_start();

if ($success) {
    // If signup was successful, set user's name
    $_SESSION['fname'] = $firstName; // Assuming $firstName is set during signup
    $_SESSION['lname'] = $lastName;   // Assuming $lastName is set during signup
    // Proceed to hash the password and save the member to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    saveMemberToDB($firstName, $lastName, $gender, $email, $hashedPassword);
} else {
    // If signup failed, set a session variable for error message
    $_SESSION['signup_success'] = false;
    $_SESSION['error'] = $errorMsg; // Assuming $errorMsg contains the error messages
}
// Include the sections after setting the session variables
include "head.inc.php";
include "header.inc.php";
include "registration_headsection.inc.php";
include "footer.inc.php";
?>
