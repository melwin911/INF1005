<?php

// Initialize and load environment variables from .env file using Dotenv library
require __DIR__ . '/vendor/autoload.php'; // Autoload all composer libraries
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // Create a Dotenv instance for the project directory
$dotenv->load(); // Load the environment variables from the .env file
use Firebase\JWT\Key;
use \Firebase\JWT\JWT; // Import the JWT class to encode and decode JSON Web Tokens
use \Firebase\JWT\ExpiredException; // Import the ExpiredException class to handle exceptions related to expired tokens
use \Firebase\JWT\SignatureInvalidException; // Import the SignatureInvalidException class to handle exceptions when a token's signature validation fails
use \Firebase\JWT\BeforeValidException; // Import the BeforeValidException class to handle exceptions when a token is used before it is valid
use \UnexpectedValueException; // Import the UnexpectedValueException class to handle exceptions for unexpected values, such as incorrect encoding or invalid claims in a token

// Helper function to authenticate the login.
function authenticateUser($email, $password, $rememberme, $secretKey)
{
    $success = true;
    $errorMsg = [];

    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg[] = "Failed to read database config file.";
        $success = false;
    } else {
        $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

        // Check connection
        if ($conn->connect_error) {
            $errorMsg[] = "Connection failed: " . $conn->connect_error;
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
                    $result = onLogin($email, $rememberme, $secretKey);
                    if ($result == false) {
                        $success = false;
                    }
                } else {
                    // Password does not match
                    $errorMsg[] = "Email not found or password doesn't match.";
                    $success = false;
                }
            } else {
                // email not found
                $errorMsg[] = "Email not found or password doesn't match.";
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }

    // Return the success status
    return ['success' => $success];
}

// Helper function to store token for users who click on remember me option
function storeTokenForUser($email, $token)
{
    $success = true;
    $errorMsg = [];
    // Create database connection
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $errorMsg[] = "Failed to read database config file.";
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
            $errorMsg[] = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
            // Prepare the statement
            $stmt = $conn->prepare("UPDATE hotel_members SET token = ? WHERE email = ?");
            if (!$stmt) {
                $errorMsg[] = "Failed to prepare SQL statement: " . $conn->error;
                $success = false;
                $conn->close();
            } else {
                // Bind parameters and execute the statement
                $stmt->bind_param("ss", $token, $email);
                if (!$stmt->execute()) {
                    $errorMsg[] = "Failed to execute SQL statement: " . $stmt->error;
                    $success = false;
                }
            }
            // Close the statement
            $stmt->close();
        }
        $conn->close();
    }
    // Return the success status
    return ['success' => $success];
}

function onLogin($email, $rememberme, $secretKey) {
    if ($email){
        // Set session variables to indicate user is logged in
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $email;
        if ($rememberme) {
            // Prepare the payload for the JWT
            $payload = [
                'email' => $email,
                'exp' => time() + (86400 * 30), // Token expires in 30 days
            ];
            
            // Generate the JWT using the payload, secret key, and specifying the HS256 algorithm
            $jwt = JWT::encode($payload, $secretKey, 'HS256');
            $result = storeTokenForUser($email, $jwt); // Store JWT in your database against the user for verification next time they log in
            $success = $result['success'];
            if (!$success) {
                return false;
            } else {
                // Prepare the cookie value
                $cookie = $email . ':' . $jwt;
                // Set a cookie named 'rememberme' with the JWT
                setcookie('rememberme', $cookie, [
                    'expires' => time() + (86400 * 30), // Token expires in 30 days
                    'path' => '/',
                ]);
            }
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
        return;
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection
        if ($conn->connect_error) {
            return;
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
    // Return the token
    return ['token' => $token];
}

// Helper function to check if user is logged in
function rememberMe($secretKey) {
    // Check if a 'rememberme' cookie exists
    if (isset($_COOKIE['rememberme'])) {
        list($email, $jwt) = explode(':', $_COOKIE['rememberme'], 2); // Extract the email and JWT from the 'rememberme' cookie
        $result = fetchTokenByEmail($email); // Fetch the token from the database
        $jwtFromDatabase = $result['token'];
        
        if ($jwtFromDatabase == null) { // If there is no token in the database or an error occurred
            return false;
        } else {
            if ($jwt == $jwtFromDatabase) { // Compare the JWT from the cookie with the one from the database
                try {
                    JWT::decode($jwt, new Key($secretKey, 'HS256')); // Decode the JWT
                    // If decoding is successful
                    $_SESSION['loggedin'] = true;
                    $_SESSION['email'] = $email;
                    return true;
                    
                } catch (ExpiredException $e) { // Handle expired token
                } catch (SignatureInvalidException $e) { // Handle invalid signature - potential tampering
                } catch (BeforeValidException $e) { // Handle token being used before it's valid
                } catch (UnexpectedValueException $e) { // Handle exceptions for other JWT-related errors, such as incorrect encoding or claims
                } catch (Exception $e) { //Handle any other exceptions
                }
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
}

// Helper function to invalidate or delete the token
function invalidateToken($email) {
    $pass = true;
    $errorMsg = "";
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config)
    {
        $errorMsg = "Failed to read database config file.";
        $pass = false;
    } else {
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
            $pass = false;
        } else {
            // Prepare the statement to invalidate or delete the token
            $stmt = $conn->prepare("UPDATE hotel_members SET token = NULL WHERE email = ?"); // set the token to NULL with reference to the email
            if (!$stmt) {
                $errorMsg = "Failed to prepare SQL statement: " . $conn->error;
                $pass = false;
            } else {
                $stmt->bind_param("s", $email);

                if (!$stmt->execute()) {
                    $errorMsg = "Failed to execute SQL statement: " . $stmt->error;
                    $pass = false;
                }
            }
            $stmt->close();
        }
        $conn->close();
    }
    return ['pass' => $pass, 'message' => $errorMsg]; // Return an array with pass as true or false and message if there are any errors
}

/*
  Helper function that checks input for malicious or unwanted content.
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
function saveMemberToDB($firstName, $lastName, $gender, $email, $hashedPassword)
{
    $errorMsg = [];
    $success = true;
    $_SESSION['signup_success'] = true;
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        $_SESSION['signup_success'] = false;
        $errorMsg[] = "Failed to read database config file.";
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection
        if ($conn->connect_error) {
            $_SESSION['signup_success'] = false;
            $errorMsg[] = "Connection failed: " . $conn->connect_error;
        }
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT email FROM hotel_members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['signup_success'] = false;
            // Email already exists
            $errorMsg[] = "This email is already registered.<br>";
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("INSERT INTO hotel_members
            (fname, lname, gender, email, password) VALUES (?, ?, ?, ?, ?)");
            // Bind & execute the query statement:
            $stmt->bind_param("sssss", $firstName, $lastName, $gender, $email, $hashedPassword);
            if (!$stmt->execute()) {
                $errorMsg[] = "Execute failed: (" . $stmt->errno . ") " .$stmt->error;
                $_SESSION['signup_success'] = false;
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
    } else {
        $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
        );

        // Check connection
        if ($conn->connect_error) {
            return false; // Return false if unable to connect to the database.
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("SELECT email FROM hotel_members WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
        }
        $stmt->close();
        $conn->close();
    }
    return $exists;
}

?>