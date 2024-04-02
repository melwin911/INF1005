<!-- start of head section -->
<?php
// Start the session
session_start();

// Check if session variables are set
if (isset($_SESSION['fname']) && isset($_SESSION['lname'])) {
    $fname = $_SESSION['fname'];
    $lname = $_SESSION['lname'];
}

$loggedin = isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : false;
?>


<main aria-label="login_headsection">
<section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
    <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
            <div class="col-md-10 text-center" data-aos="fade">
                <?php if ($loggedin == true): ?>
                    <h1 class="heading mb-3"><?php echo "Welcome, " . htmlspecialchars($fname) . " " . htmlspecialchars($lname) . "!"; ?></h1>
                    <button class="btn btn-primary"><a href="member_page.php" style="text-decoration: none; color: white;">Return to Home</a></button>
                <?php elseif ($loggedin == false): ?>
                    <h1 class="heading mb-3"><?php echo "Goodbye and have a great day ahead, " . htmlspecialchars($fname) . " " . htmlspecialchars($lname) . "!"; ?></h1>
                    <button class="btn btn-primary"><a href="index.php" style="text-decoration: none; color: white;">Return to Home</a></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
                </main>