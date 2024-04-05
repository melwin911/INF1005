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
    renderNavbar('Registration');
    ?>
    <main class="container">
        <h1>Membership Registration</h1>
        <p>
            For existing members, please proceed to the
            <a href="login.php" style="color: #333; text-decoration: underline;">Sign In Page</a>
        </p>
        <form action="process_registration.php" method="post">
            <div class="mb-3">
                <label for="fname" class="form-label">First Name:</label>
                <input maxlength="45" type="text" id="fname" name="fname" class="form-control" placeholder="Enter first name (NOT COMPULSORY)">
            </div>

            <div class="mb-3">
                <label for="lname" class="form-label">Last Name:</label>
                <input required maxlength="45" type="text" id="lname" name="lname" class="form-control" placeholder="Enter last name (COMPULSORY)">
            </div>

            <div class="mb-3">
                <ul style="list-style: none; padding: 0;">
                    <li><label>Gender: (COMPULSORY)</label></li>
                    <li>
                        <input type="radio" id="male" name="gender" value="male" required>
                        <label for="male">Male</label>
                    </li>
                    <li>
                        <input type="radio" id="female" name="gender" value="female" required>
                        <label for="female">Female</label>
                    </li>
                    <li>
                        <input type="radio" id="other" name="gender" value="other" required>
                        <label for="other">Other</label>
                    </li>
                </ul>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input required maxlength="45" type="email" id="email" name="email" class="form-control" placeholder="Enter email (COMPULSORY)">
            </div>

            <div class="mb-3">
                <label for="pwd" class="form-label">Password:</label>
                <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password (COMPULSORY)">
            </div>

            <div class="mb-3">
                <label for="pwd_confirm" class="form-label">Confirm Password:</label>
                <input required type="password" id="pwd_confirm" name="pwd_confirm" class="form-control" placeholder="Confirm password (COMPULSORY)">
            </div>

            <div class="mb-3 form-check">
                <input required type="checkbox" name="agree" id="agree" class="form-check-input">
                <label class="form-check-label" for="agree">
                    Agree to terms and conditions.
                </label>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </main>
    <?php
    include "footer.inc.php";
    ?>
    <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
    <script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
    <script src="js/main.js"></script>
</body>
</html>