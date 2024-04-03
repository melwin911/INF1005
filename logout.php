<?php
// Start the session
session_start();
require 'functions.php';

$_SESSION['loggedin'] = false;

// Assuming $_SESSION['email'] holds the email of the logged-in user
if (isset($_SESSION['email'])) {
    $result = invalidateToken($_SESSION['email']); // Invalidate the token for the logged-in user
    $pass = $result['pass']; // Set $pass to true or false
    if ($pass == true) {
        setcookie('rememberme', '', [
            'expires' => time() - 3600, // Set in the past to ensure deletion
            'path' => '/',
        ]); // Unset the cookie variable
        include "head.inc.php";
        include "header.inc.php";
        include "logout_headsection.inc.php";
        include "footer.inc.php";

        $_SESSION = []; // Unset all session variables
        // Destroy the PHP session
        session_destroy();

    } else { // If there are any errors in the token invalidation, e.g. connection failure etc..
        $_SESSION['error'] = $result['message'];
        include "head.inc.php";
        include "header.inc.php";
        include "logout_headsection.inc.php";
        include "footer.inc.php";
    }
}

?>