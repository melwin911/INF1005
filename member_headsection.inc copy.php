<?php
// Start the session
session_start();

// Define variables
$fname = isset($_SESSION['fname']) ? $_SESSION['fname'] : '';
$lname = isset($_SESSION['lname']) ? $_SESSION['lname'] : '';
$loggedin = isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : false;
$errorMsg = isset($_SESSION['errorMsg']) ? $_SESSION['errorMsg'] : '';

// The section below will be displayed only if there's an error
if (!empty($errorMsg)) {
?>

<section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
    <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
            <div class="col-md-10 text-center" data-aos="fade">
                <!-- Display error messages -->
                <h1 class="heading mb-3">Oops! The following errors were detected:</h1>
                <p><?php echo $errorMsg; ?></p>
                <button class="btn btn-primary"><a href="login.php" style="text-decoration: none; color: white;">Return to Login</a></button>
            </div>
        </div>
    </div>
</section>

<?php
} else if ($loggedin) {
    // Logic for logged-in users
?>

<section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
    <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
            <div class="col-md-10 text-center" data-aos="fade">
                <h1 class="heading mb-3"><?php echo "Welcome, " . ($fname) . " " . ($lname) . "!"; ?></h1>
                <button class="btn btn-primary"><a href="member_page.php" style="text-decoration: none; color: white;">Return to Home</a></button>
            </div>
        </div>
    </div>
</section>

<?php
} else {
    // Logic for guests or when no specific condition is met
?>

<?php
}
?>
