<?php
session_start();
?>

<section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
    <div class="container">
        <div class="row site-hero-inner justify-content-center align-items-center">
            <div class="col-md-10 text-center" data-aos="fade">
                <?php if (isset($_SESSION['signup_success']) && $_SESSION['signup_success']): ?>
                    <h1 class="heading mb-3">Thank you for signing up, <?php echo ($_SESSION['fname']) . " " . ($_SESSION['lname']) . "!"; ?></h1>
                    <button class="btn btn-primary"><a href="login.php" style="text-decoration: none; color: white;">Login</a></button>

                <?php else: ?>
                    <h1 class="heading mb-3">Oops!</h1>
                    <h1 class="heading mb-3"><?php echo isset($_SESSION['error_msg']) ? ($_SESSION['error_msg']) : "An unknown error occurred."; ?></h1>
                    <button class="btn btn-primary"><a href="registration.php" style="text-decoration: none; color: white;">Return to Sign Up</a></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
unset($_SESSION['signup_success'], $_SESSION['fname'], $_SESSION['lname'], $_SESSION['error_msg']);
?>
