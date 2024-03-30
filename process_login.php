<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$secretKey = $_ENV['SECRET_KEY'];

// Initialize variables
$email = $password = $errorMsg = "";
$success = true;
rememberMe($secretKey);

// Check if form fields are set
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
    
    if ($success == true && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        // Successful login, include member pages
        include "head.inc.php";
        include "header.inc.php";
        include "member_headsection.inc.php";
        include "footer.inc.php";
    } elseif ($success == false) {
        $_SESSION['error'] = $result['message'];
        // Failed login, include non-member pages and error message
        include "head.inc.php";
        include "header.inc.php";
        include "member_headsection.inc.php";
        include "footer.inc.php";
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

/*
* Helper function to authenticate the login.
*/
function authenticateUser($email, $password, $secretKey)
{
    $errorMsg = ""; // Initialize error message variable
    $success = true; // Default success unless an error occurs

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
            $stmt = $conn->prepare("SELECT fname, lname, email, password FROM hotel_members WHERE email=?");
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


function storeTokenForUser($email, $token)
{
    global $errorMsg, $success;
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config)
    {
        $errorMsg = "Failed to read database config file.";
        $success = false;
        return; // Exit function
    }
    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );
    // Check connection
    if ($conn->connect_error)
    {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        $success = false;
        return; // Exit function
    }
    // Prepare the statement
    $stmt = $conn->prepare("UPDATE hotel_members SET token = ? WHERE email = ?");
    if (!$stmt)
    {
        $errorMsg = "Failed to prepare SQL statement: " . $conn->error;
        $success = false;
        $conn->close();
        return; // Exit function
    }
    // Bind parameters and execute the statement
    $stmt->bind_param("ss", $token, $email);
    if (!$stmt->execute())
    {
        $errorMsg = "Failed to execute SQL statement: " . $stmt->error;
        $success = false;
    }
    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

use \Firebase\JWT\JWT; // Import the JWT namespace
use \Firebase\JWT\ExpiredException;
use \Firebase\JWT\SignatureInvalidException;
use \Firebase\JWT\BeforeValidException;
use \UnexpectedValueException;
function onLogin($email, $rememberme, $secretKey) {
    if ($email){
    $_SESSION['loggedin'] = true; // User is logged in
    $_SESSION['user_email'] = $email;
    if ($rememberme) {
        $payload = [
            'email' => $email,
            'exp' => time() + (86400 * 30), // Token expires in 30 days
        ];

        $jwt = JWT::encode($payload, $secretKey, 'HS256');
        storeTokenForUser($email, $jwt); // Store JWT in your database against the user

        $cookie = $email . ':' . $jwt;
        setcookie('rememberme', $cookie, time() + (86400 * 30), "/"); // Set cookie for 30 days
    }
    }
    else {
        unset($_SESSION['loggedin']);
    }
}

function fetchTokenByEmail($email)
{
    $token = null;
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config)
    {
        $errorMsg = "Failed to read database config file.";
    }
    else
    {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection
        if ($conn->connect_error)
        {
            $errorMsg = "Connection failed: " . $conn->connect_error;
        }
        else
        {
            $stmt = $conn->prepare("SELECT token FROM hotel_members WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($token);
            $stmt->fetch();
        }
        $stmt->close();
    }
    $conn->close();
    return $token;
}

function rememberMe($secretKey) {
    if (isset($_COOKIE['rememberme'])) {
        list($email, $jwt) = explode(':', $_COOKIE['rememberme']);
        $jwtFromDatabase = fetchTokenByEmail($email); // Fetch the token from the database
        
        if ($jwt === $jwtFromDatabase) {
            try {
                // JWT::decode($jwt, $secretKey, 'HS256'); // Adjusted for firebase/php-jwt v6+
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $email;
                // Consider redirecting the user to their intended destination or member page
            } catch (ExpiredException $e) {
                // Handle expired token
                // Maybe prompt user to login again or automatically extend their session if applicable
            } catch (SignatureInvalidException $e) {
                // Handle invalid signature - potential tampering
            } catch (BeforeValidException $e) {
                // Handle token being used before it's valid
            } catch (UnexpectedValueException $e) {
                // Handle other errors such as wrong algorithm or malformed token
            } catch (Exception $e) {
                // Handle any other exceptions
            } finally {
                if (!$_SESSION['loggedin']) {
                    // This ensures actions are taken if any of the catches set loggedin to false or if an exception was caught
                    setcookie('rememberme', '', time() - 3600, "/"); // Clear the 'rememberme' cookie
                    session_unset(); // Clear the session
                    session_destroy(); // Destroy the session
                    header('Location: login.php'); // Redirect to login page
                    exit; // Ensure no further execution of script
                }
            }
        }
    }
}
?>
