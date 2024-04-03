<!-- start of head section -->
<?php
// Start the session
session_start();

// Define variables
$loggedin = isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : false;
$errorMsg = isset($_SESSION['error']) ? $_SESSION['error'] : '';

?>

<main aria-label="logout_headsection">
    <section class="site-hero inner-page overlay" style="background-image: url(images/slider-6.jpg)">
        <div class="container">
            <div class="row site-hero-inner justify-content-center align-items-center">
                <div class="col-md-10 text-center" data-aos="fade">
                    <?php if ($loggedin == false): // check if login was successful ?>
                        <h1 class="heading mb-3">Goodbye and have a great day ahead!</h1>
                        <button class="btn btn-primary"><a href="index.php" style="text-decoration: none; color: black;">Return to Home</a></button>

                    <?php elseif (!empty($errorMsg)): ?>
                        <h1 class="heading mb-3">Oops!</h1>
                        <?php foreach ($errorMsg as $message): ?>
                            <h1 class="heading mb-3"><?php echo htmlspecialchars($message); ?></h1><br>
                        <?php endforeach; ?>
                        <button class="btn btn-primary"><a href="member_page.php" style="text-decoration: none; color: black;">Return Home</a></button>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>