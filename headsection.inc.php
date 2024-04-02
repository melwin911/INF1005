<?php
session_start();

// Define variables
$fname = isset($_SESSION['fname']) ? $_SESSION['fname'] : '';
$lname = isset($_SESSION['lname']) ? $_SESSION['lname'] : '';
$signup_success = isset($_SESSION['signup_success']) ? $_SESSION['signup_success'] : false;
$errorMsg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';

?>

<div aria-label="headsection">
<section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
    <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
            <div class="col-md-10 text-center" data-aos="fade">
                <?php if ($signup_success): ?>
                    <h1 class="heading mb-3">Thank you for signing up, <?php echo htmlspecialchars($fname) . " " . htmlspecialchars($lname) . "!"; ?></h1>
                    <button class="btn btn-primary"><a href="login.php" style="text-decoration: none; color: white;">Login</a></button>
                    <?php unset($_SESSION['signup_success'], $_SESSION['fname'], $_SESSION['lname']); ?>

                <?php else: ?>
                    <h1 class="heading mb-3">Oops!</h1>
                    <?php if (!empty($errorMsg)): ?>
                        <?php foreach ($errorMsg as $message): ?>
                            <h1 class="heading mb-3"><?php echo htmlspecialchars($message); ?></h1><br>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <button class="btn btn-primary"><a href="registration.php" style="text-decoration: none; color: white;">Return to Registration</a></button>
                    <?php unset($_SESSION['error_msg']); // unset the session error message ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
                        </div>