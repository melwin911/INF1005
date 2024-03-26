<?php

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$secretKey = $_SERVER['SECRET_KEY'] ?? $_ENV['SECRET_KEY'] ?? null;

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
    $password = $_POST["pwd"]; // No need to sanitize password because it typically contains special characters
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $success) {
    authenticateUser();
    session_start();
    onLogin($email, isset($_POST['rememberme']), $secretKey);
    $_SESSION['fname'] = $fname;
    $_SESSION['lname'] = $lname;
    $_SESSION['email'] = $email;
}

if ($success) {
    include "head.inc.php";
    include "header.inc.php";
    include "member_headsection.inc.php";
    include "footer.inc.php";
} else {
    include "header.inc.php";
    include "headsection.inc.php";
    echo '<br><div style="text-align: left; margin: 0 auto; width: 50%;">';
    echo "<h3>Oops! </h3> <h4>The following errors were detected:</h4>";
    echo "<p>" . $errorMsg . "</p>";
    echo '<button class="btn btn-primary"><a href="login.php" style="text-decoration: none; color: white;">Return to Login</a></button>';
    echo '</div><br>';
    include "footer.inc.php";
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
function authenticateUser()
{
    global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;
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
        else
        {
            // Prepare the statement:
            $stmt = $conn->prepare("SELECT * FROM hotel_members WHERE email=?");
            // Bind & execute the query statement:
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Note that email field is unique, so should only have
                // one row in the result set.
                $row = $result->fetch_assoc();
                $fname = $row["fname"];
                $lname = $row["lname"];
                $pwd_hashed = $row["password"];
                // Check if the password matches:
                if (!password_verify($_POST["pwd"], $pwd_hashed))
                {
                    // Don't be too specific with the error message - hackers don't
                    // need to know which one they got right or wrong. :)
                    $errorMsg = "Email not found or password doesn't match...";
                    $success = false;
                }
            }
            else
            {
                $errorMsg = "Email not found or password doesn't match...";
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
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
    $_SESSION['loggedin'] = true; // User is logged in
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
        
        if ($jwtFromCookie === $jwtFromDatabase) {
            try {
                JWT::decode($jwt, $secretKey, 'HS256'); // Adjusted for firebase/php-jwt v6+
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
