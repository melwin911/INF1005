<?php
$email = $gender = $firstname = $lastname = $password = $confirmPassword = $errorMsg = "";
$success = true;

if (empty($_POST["email"]))
{
    $errorMsg .= "Email is required.<br>";
    $success = false;
}
else
{
    $email = sanitize_input($_POST["email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
    elseif(emailExists($email))
    {
        $errorMsg .= "Email already exists.<br>";
        $success = false;
    }
}

if (empty($_POST["gender"]))
{
    $errorMsg .= "Gender is required.<br>";
    $success = false;
}
else
{
    $gender = sanitize_input($_POST["gender"]);
}

if (empty($_POST["lname"]))
{
    $errorMsg .= "Last Name is required.<br>";
    $success = false;
}
else
{
    $lastName = sanitize_input($_POST["lname"]);
}

if (empty($_POST["fname"]))
{
    $firstName = sanitize_input($_POST["fname"]);
}
else
{
    $firstName = sanitize_input($_POST["fname"]);
}

if (empty($_POST["pwd"]))
{
    $errorMsg .= "Password is required.<br>";
    $success = false;
}
else
{
    $password = $_POST["pwd"]; // No need to sanitize password because it typically contain special characters
}

if (empty($_POST["pwd_confirm"]))
{
    $errorMsg .= "Confirm Password is required.<br>";
    $success = false;
}
else
{
    $confirmPassword = $_POST["pwd_confirm"];
    if ($confirmPassword !== $password)
    {
        $errorMsg .= "Passwords do not match.<br>";
        $success = false;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $success)
{
    include "inc/head.inc.php";
    include "inc/nav.inc.php";
    echo '<div class="container registration-container">';
    echo "<h1> Your registration is successful!</h1>";
    echo "<h3 class='mb-4'>Thank you for signing up, " .$firstName." ". $lastName . ".</h3>"; // concatenate using (.)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    echo '<a href="login.php" class="login-btn mt-3">Log-in</a>';
    echo '</div>';
    include "inc/footer.inc.php";
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST")
{
    include "inc/head.inc.php";
    include "inc/nav.inc.php";
    echo '<div class="container registration-container">';
    echo "<h1> Oops!</h1>";
    echo "<h3>The following input errors were detected:</h3>";
    echo "<p>" . $errorMsg . "</p>";
    echo '<a href="register.php" class="return-to-sign-up-btn mt-3">Return to Sign Up</a>';
    echo '</div>';
    include "inc/footer.inc.php";
}
/*
* Helper function that checks input for malicious or unwanted content.
*/
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function emailExists($email) {
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config) {
        return false; // Return false if unable to read database config file.
    }

    $conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
    );

    // Check connection
    if ($conn->connect_error) {
        return false; // Return false if unable to connect to the database.
    }

    // Prepare the statement:
    $stmt = $conn->prepare("SELECT email FROM world_of_pets_members WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;

    $stmt->close();
    $conn->close();

    return $exists;
}

function saveMemberToDB()
{
    global $firstName, $lastName, $email, $hashedPassword, $gender, $errorMsg, $success;
    // Create database connection.
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config)
    {
        $errorMsg = "Failed to read database config file.";
        $success = false;
    }
    else
    {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection
        if ($conn->connect_error)
        {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        }
        else
        {
            // Prepare the statement:
            $stmt = $conn->prepare("INSERT INTO world_of_pets_members
            (fname, lname, gender, email, password) VALUES (?, ?, ?, ?, ?)");
            // Bind & execute the query statement:
            $stmt->bind_param("sssss", $firstName, $lastName, $gender, $email, $hashedPassword); // 's' means string type
            if (!$stmt->execute())
            {
                $errorMsg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                $success = false;
             }
            $stmt->close();
        }
        $conn->close();
    }
}

saveMemberToDB()
?>