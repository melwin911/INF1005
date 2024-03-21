<?php
// Initialize variables
$email = $firstName = $lastName = $gender = $password = $confirmPassword = $errorMsg = "";
$success = true;

// Check if form fields are set
if (empty($_POST["email"]))
{
    $errorMsg .= "Email is required.<br>";
    $success = false;
}
else
{
    $email = sanitize_input($_POST["email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $errorMsg .= "Invalid email format.";
        $success = false;
    }
    elseif(emailExists($email))
    {
        $errorMsg .= "Email already exists.";
        $success = false;
    }
}

if (empty($_POST["gender"]))
{
    $errorMsg .= "gender is required.";
    $success = false;
}
else
{
    $gender = sanitize_input($_POST["gender"]);
}

if (empty($_POST["lname"]))
{
    $errorMsg .= "Last Name is required.";
    $success = false;
}
else
{
    $lastName = sanitize_input($_POST["lname"]);
}

if (empty($_POST["fname"]))
{
    $firstName = sanitize_input($_POST["fname"]);
}
else
{
    $firstName = sanitize_input($_POST["fname"]);
}

if (empty($_POST["pwd"]))
{
    $errorMsg .= "Password is required.";
    $success = false;
}
else
{
    $password = $_POST["pwd"]; // No need to sanitize password because it typically contain special characters
}

if (empty($_POST["pwd_confirm"]))
{
    $errorMsg .= "Confirm Password is required.";
    $success = false;
}
else
{
    $confirmPassword = $_POST["pwd_confirm"];
    if ($confirmPassword !== $password)
    {
        $errorMsg .= "Passwords do not match.";
        $success = false;
    }
}

session_start();

if ($success) {
    // If signup was successful, set session variables for success and user's name
    $_SESSION['signup_success'] = true;
    $_SESSION['fname'] = $firstName; // Assuming $firstName is set during signup
    $_SESSION['lname'] = $lastName;   // Assuming $lastName is set during signup
    // Proceed to hash the password and save the member to the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    saveMemberToDB();
} else {
    // If signup failed, set a session variable for error message
    $_SESSION['signup_success'] = false;
    $_SESSION['error_msg'] = $errorMsg; // Assuming $errorMsg contains the error messages
}
// Include the sections after setting the session variables
include "head.inc.php";
include "header.inc.php";
include "headsection.inc.php";
include "footer.inc.php";

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

/*
* Helper function to write the member data to the database.
*/
function saveMemberToDB()
{
    global $firstName, $lastName, $gender, $email, $hashedPassword, $errorMsg, $success;
    // Create database connection.
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
        }
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT email FROM hotel_members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Email already exists
            $errorMsg .= "This email is already registered.<br>";
            $success = false;
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("INSERT INTO hotel_members
            (fname, lname, gender, email, password) VALUES (?, ?, ?, ?, ?)");
            // Bind & execute the query statement:
            $stmt->bind_param("sssss", $firstName, $lastName, $gender, $email, $hashedPassword);
            if (!$stmt->execute()) {
                $errorMsg = "Execute failed: (" . $stmt->errno . ") " .
                    $stmt->error;
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

function emailExists($email) {
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        return false; // Return false if unable to read database config file.
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    // Check connection
    if ($conn->connect_error) {
        return false; // Return false if unable to connect to the database.
    }

    // Prepare the statement:
    $stmt = $conn->prepare("SELECT email FROM hotel_members WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;

    $stmt->close();
    $conn->close();

    return $exists;
}
?>
