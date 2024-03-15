<!DOCTYPE html>
<html lang="en">
    <?php
    include "inc/head.inc.php";
    ?>

    <body>
        <?php
        include "inc/nav.inc.php";
        ?>

        <main class="container">
            <h1>Membership Registration</h1>
            <p>
                For existing members, please proceed to the
                <a href="login.php">Sign In Page</a>.
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
                    <label for="male">Male</label>
                    <input type="radio" id="male" name="gender" value="male">

                    <label for="female">Female</label>
                    <input type="radio" id="female" name="gender" value="female">

                    <label for="other">Other</label>
                    <input type="radio" id="other" name="gender" value="other">
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
                    <input required type="password" id="pwd_confirm" name="pwd_confirm" class="form-control" placeholder="Confirm password(COMPULSORY)">
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
        include "inc/footer.inc.php";
        ?>
    </body>
</html>