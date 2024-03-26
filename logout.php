<?php
// Start the session
session_start();

$_SESSION['loggedin'] = false;

// Assuming $_SESSION['email'] holds the email of the logged-in user
if (isset($_SESSION['email'])) {
    invalidateToken($_SESSION['email']);
}

// If you're using cookies to store JWTs:
if (isset($_COOKIE['rememberme'])) {
    unset($_COOKIE['rememberme']); // Unset the cookie variable
    setcookie('rememberme', null, -1, '/'); // Clear the JWT cookie
}

// Include your header files after the session and cookie are cleared
include "head.inc.php";
include "header.inc.php";
include "member_headsection.inc.php";
include "footer.inc.php";

// Clear PHP session variables
$_SESSION = [];

// Destroy the PHP session
session_destroy();

exit;

function invalidateToken($email) {
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
        return; // Exit function
    }
    // Prepare the statement to invalidate or delete the token
    $stmt = $conn->prepare("UPDATE hotel_members SET token = NULL WHERE email = ?");
    if (!$stmt)
    {
        $errorMsg = "Failed to prepare SQL statement: " . $conn->error;
        $conn->close();
        return; // Exit function
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute())
    {
        $errorMsg = "Failed to execute SQL statement: " . $stmt->error;
        $stmt->close();
        return;
    }

    // Execute and close
    $stmt->execute();
    $stmt->close();
    $conn->close();
}
?>