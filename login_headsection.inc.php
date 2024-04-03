<!-- start of head section -->
<?php
// Start the session
session_start();

// Define variables
$fname = isset($_SESSION['fname']) ? $_SESSION['fname'] : '';
$lname = isset($_SESSION['lname']) ? $_SESSION['lname'] : '';
$loggedin = isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : false;
$errorMsg = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$password = isset($_SESSION['password']) ? $_SESSION['password'] : '';

?>

<main aria-label="login_headsection">
    <section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
        <div class="container">
            <div class="row site-hero-inner justify-content-center align-items-center">
                <div class="col-md-10 text-center" data-aos="fade">
                    <?php if ($loggedin == true): // check if login was successful ?>
                        <?php if ($email == 'admin@admin.com' && $password == 'admin'): ?>
                            <h1 class="heading mb-3">Welcome, <?php echo htmlspecialchars($fname) . " " . htmlspecialchars($lname) . "!"; ?></h1>
                            <button class="btn btn-primary"><a href="view_bookings.php" style="text-decoration: none; color: black;">View Bookings</a></button>
                        <?php else: ?>
                            <h1 class="heading mb-3">Welcome, <?php echo htmlspecialchars($fname) . " " . htmlspecialchars($lname) . "!"; ?></h1>
                            <button class="btn btn-primary"><a href="member_page.php" style="text-decoration: none; color: black;">Return to Home</a></button>
                        <?php endif; ?>
                    <?php elseif (!empty($errorMsg)): ?>
                        <h1 class="heading mb-3">Oops!</h1>
                        <?php foreach ($errorMsg as $message): ?>
                            <h1 class="heading mb-3"><?php echo htmlspecialchars($message); ?></h1><br>
                        <?php endforeach; ?>
                        <button class="btn btn-primary"><a href="login.php" style="text-decoration: none; color: black;">Try Logging In Again</a></button>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>