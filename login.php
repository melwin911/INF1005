<?php
session_start();

$headSection = "nonmember_head.inc.php"; // Default to non-member head
$navBar = "navbar.inc.php"; // Default to non-member navbar

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>5 Hotel</title>
    <?php
    include "head.inc.php";
    ?>
</head>

<body>
    <?php
    include "header.inc.php";
    include $navBar;
    include $headSection;
    renderNavbar('Login');
    ?>

    <main class="container" id="registration"> 
        <h1>Member Login</h1> 
        <p> 
            Existing members log in here. For new members, please sign up at 
            <a href="registration.php" style="color: #333; text-decoration: underline;">Registration page</a>. 
        </p> 
        <form action="process_login.php" method="post"> 
            <div class="mb-3"> 
                <label for="email" class="form-label">Email:</label> 
                <input required maxlength="45" type="email" id="email" name="email" class="form-control" placeholder="Enter email (COMPULSORY)"> 
            </div> 
            <div class="mb-3"> 
                <label for="pwd" class="form-label">Password:</label> 
                <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password (COMPULSORY)"> 
            </div> 
            <div class="mb-3 form-check">
                <input type="checkbox" id="rememberme" name="rememberme" class="form-check-input">
                <label for="rememberme" class="form-check-label">Remember Me</label>
            </div>
            <div class="mb-3"> 
                <button type="submit" class="btn btn-primary">Submit</button> 
            </div> 
        </form>
    </main>
   <!-- Start of footer -->
    <?php
      include "footer.inc.php";
      ?>
<!-- End of footer -->
<script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
<script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
<script src="js/main.js"></script>
    
</body>
</html>