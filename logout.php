<?php
// Start the session
session_start();
$_SESSION['loggedin'] = false;
// Unset specific session variables (if needed)
unset($_SESSION['loggedin']);
unset($_SESSION['email']);

include "head.inc.php";
include "header.inc.php";
include "member_headsection.inc.php";
include "footer.inc.php";

// Alternatively, you can destroy the entire session (uncomment the line below)
session_destroy();
exit;
?>