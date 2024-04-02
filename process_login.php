<?php
session_start();

// Initialize and load environment variables from .env file using Dotenv library
require __DIR__ . '/vendor/autoload.php'; // Autoload all composer libraries
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // Create a Dotenv instance for the project directory
$dotenv->load(); // Load the environment variables from the .env file

use \Firebase\JWT\JWT; // Import the JWT class to encode and decode JSON Web Tokens
use \Firebase\JWT\ExpiredException; // Import the ExpiredException class to handle exceptions related to expired tokens
use \Firebase\JWT\SignatureInvalidException; // Import the SignatureInvalidException class to handle exceptions when a token's signature validation fails
use \Firebase\JWT\BeforeValidException; // Import the BeforeValidException class to handle exceptions when a token is used before it is valid
use \UnexpectedValueException; // Import the UnexpectedValueException class to handle exceptions for unexpected values, such as incorrect encoding or invalid claims in a token

// Retrieve the 'SECRET_KEY' value from environment variables and assign it to $secretKey
$secretKey = $_ENV['SECRET_KEY'];

// Initialize variables
$email = $password = $errorMsg = "";
$success = true;

// Check if user is already logged in using rememberMe function
rememberMe($secretKey);

// Form validation and sanitization
if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
}

if (empty($_POST["pwd"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
} else {
    $password = $_POST["pwd"];
}

// Attempt authentication only if the request is POST and $success is still true.
if ($_SERVER["REQUEST_METHOD"] == "POST" && $success) {
    $result = authenticateUser($email, $password, $secretKey);
    $success = $result['success'];
    
    // Check if the user is an admin
    if ($success && $email == 'admin@admin.com' && $password == 'admin') {
        // Successful admin login, include admin pages
        //include "admin_head.inc.php";
        //include "header.inc.php";
        //include "view_bookings.php";
        //include "footer.inc.php";
        // Redirect to View Bookings Page
        header('Location: view_bookings.php');
    } else {
        // Process the result, which includes handling errors or no errors
        $_SESSION['error'] = $result['message'];
        include "head.inc.php";
        include "header.inc.php";
        include "member_headsection.inc.php";
        include "footer.inc.php";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !$success) {
    // Display error message if vaidation or sanitisation failed
    $_SESSION['error'] = $errorMsg;
    include "head.inc.php";
    include "header.inc.php";
    include "member_headsection.inc.php";
    include "footer.inc.php";
}

// Helper function that checks input for malicious or unwanted content.
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function to authenticate the login.
function authenticateUser($email, $password, $secretKey)
{
    global $success, $errorMsg;

    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
    } else {
        $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
            // Prepare the statement
            $stmt = $conn->prepare("SELECT member_id, fname, lname, email, password FROM hotel_members WHERE email=?");
            // Bind & execute the query statement
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $pwd_hashed = $row["password"];
                // Check if the password matches
                if (password_verify($password, $pwd_hashed)) {
                    // Successful login
                    $_SESSION['fname'] = $row["fname"];
                    $_SESSION['lname'] = $row["lname"];
                    $_SESSION['email'] = $email;
                    $_SESSION['member_id'] = $row["member_id"];
                    onLogin($email, isset($_POST['rememberme']), $secretKey);
                } else {
                    // Password does not match
                    $errorMsg = "Email not found or password doesn't match.";
                    $success = false;
                }
            } else {
                // email not found
                $errorMsg = "Email not found or password doesn't match.";
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }

    // Return both the success status and the message
    return ['success' => $success, 'message' => $errorMsg];
}

// Helper function to store token for users who click on remember me option
function storeTokenForUser($email, $token)
{
    global $errorMsg, $success;
    // Create database connection
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
            // Prepare the statement
            $stmt = $conn->prepare("UPDATE hotel_members SET token = ? WHERE email = ?");
            if (!$stmt) {
                $errorMsg = "Failed to prepare SQL statement: " . $conn->error;
                $success = false;
                $conn->close();
            } else {
                // Bind parameters and execute the statement
                $stmt->bind_param("ss", $token, $email);
                if (!$stmt->execute()) {
                    $errorMsg = "Failed to execute SQL statement: " . $stmt->error;
                    $success = false;
                }
            }
            // Close the statement
            $stmt->close();
        }
        $conn->close();
    }
    // Return both the success status and the message
    return ['success' => $success, 'message' => $errorMsg];
}

function onLogin($email, $rememberme, $secretKey) {
    if ($email){
        // Set session variables to indicate user is logged in
        $_SESSION['loggedin'] = true;
        $_SESSION['user_email'] = $email;
        if ($rememberme) {
            // Prepare the payload for the JWT
            $payload = [
                'email' => $email,
                'exp' => time() + (86400 * 30), // Token expires in 30 days
            ];
            
            // Generate the JWT using the payload, secret key, and specifying the HS256 algorithm
            $jwt = JWT::encode($payload, $secretKey, 'HS256');
            storeTokenForUser($email, $jwt); // Store JWT in your database against the user for verification next time they log in
            
            // Prepare the cookie value
            $cookie = $email . ':' . $jwt;
            // Set a cookie named 'rememberme' with the JWT
            setcookie('rememberme', $cookie, time() + (86400 * 30), "/"); // Set cookie for 30 days
        }
    }
}

// Helper function to fetch token from database
function fetchTokenByEmail($email)
{
    $token = null; // Initialize the token variable to hold the JWT
    // Create database connection
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
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
        } else {
            $stmt = $conn->prepare("SELECT token FROM hotel_members WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($token); // Bind the result of the query (token) to the $token variable
            $stmt->fetch(); // Fetch the result, populating $token (done for security purposes)
        }
        $stmt->close();
    }
    $conn->close();
    // Return both the token and the message
    return ['token' => $token, 'message' => $errorMsg];
}

// Helper function to check if user is logged in
function rememberMe($secretKey) {
    // Check if a 'rememberme' cookie exists
    if (isset($_COOKIE['rememberme'])) {
        list($email, $jwt) = explode(':', $_COOKIE['rememberme']); // Extract the email and JWT from the 'rememberme' cookie
        $result = fetchTokenByEmail($email); // Fetch the token from the database
        $jwtFromDatabase = $result['token'];
        $errorMsg = $result['message'];
        
        if ($jwtFromDatabase == null || $errorMsg != null) { // If there is no token in the database or an error occurred
            return;
        } else {
            if ($jwt == $jwtFromDatabase) { // Compare the JWT from the cookie with the one from the database
                try {
                    JWT::decode($jwt, $secretKey, 'HS256'); // Decode the JWT
                    // If decoding is successful
                    $_SESSION['loggedin'] = true;
                    $_SESSION['email'] = $email;
                    
                } catch (ExpiredException $e) { // Handle expired token
                } catch (SignatureInvalidException $e) { // Handle invalid signature - potential tampering
                } catch (BeforeValidException $e) { // Handle token being used before it's valid
                } catch (UnexpectedValueException $e) { // Handle exceptions for other JWT-related errors, such as incorrect encoding or claims
                } catch (Exception $e) { //Handle any other exceptions
                } finally {
                    // Ensure the user is redirected to the login page if they're not logged in after processing
                    if (!$_SESSION['loggedin']) { // Used for when the user logout previously, and this will remove the JWT from the 'rememberme' cookie if not yet removed
                        // This ensures actions are taken if any of the catches set loggedin to false or if an exception was caught
                        setcookie('rememberme', '', time() - 3600, "/"); // Clear the 'rememberme' cookie
                        session_unset(); // Clear the session
                        session_destroy(); // Destroy the session
                        header('Location: login.php'); // Redirect to login page
                        exit; // Ensure no further execution of script
                    }
                }
            } else {
                return;
            }
        }
    }
}
?>
