<?php
// Start the session
session_start();

$_SESSION['loggedin'] = false;
$pass = true;

// Assuming $_SESSION['email'] holds the email of the logged-in user
if (isset($_SESSION['email'])) {
    $result = invalidateToken($_SESSION['email']); // Invalidate the token for the logged-in user
    $pass = $result['pass']; // Set $pass to true or false
    if ($pass == true) {
        if (isset($_COOKIE['rememberme'])) {
            unset($_COOKIE['rememberme']); // Unset the cookie variable
            setcookie('rememberme', null, -1, '/'); // Clear the JWT cookie
        }
        include "head.inc.php";
        include "header.inc.php";
        include "member_headsection.inc.php";
        include "footer.inc.php";
        $_SESSION = []; // Unset all session variables
    } else { // If there are any errors in the token invalidation, e.g. connection failure etc..
        $_SESSION['error'] = $result['message'];
        include "head.inc.php";
        include "header.inc.php";
        include "member_headsection.inc.php";
        include "footer.inc.php";
    }
}

// Destroy the PHP session
session_destroy();

exit;

// Helper function to invalidate or delete the token
function invalidateToken($email) {
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
?>