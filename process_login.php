<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$email = $password = $errorMsg = "";
$success = true;
$strongKey = bin2hex(random_bytes(32));
define('SECRET_KEY', $strongKey);
rememberMe();

if (empty($_POST["email"])) {
    $errorMsg .= "Email is required.<br>";
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg .= "Invalid email format.<br>";
        $success = false;
    }
}

if (empty($_POST["pwd"])) {
    $errorMsg .= "Password is required.<br>";
    $success = false;
} else {
    $password = $_POST["pwd"]; // No need to sanitize password because it typically contains special characters
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $success) {
    authenticateUser();
    session_start();
    onLogin($email, isset($_POST['rememberme']));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $success) {
    include "inc/head.inc.php";
    include "inc/userNav.inc.php";
    echo '<div class="container registration-container">';
    echo "<h1> Login successful!</h1>";
    echo "<h3 class='mb-4'>Welcome back, " . $fname . " " . $lname . ".</h3>";
    echo '<a href="userIndex.php" class="login-btn mt-3">Return to Home</a>';
    echo '</div>';
    include "inc/footer.inc.php";
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "inc/head.inc.php";
    include "inc/nav.inc.php";
    echo '<div class="container registration-container">';
    echo "<h1> Oops!</h1>";
    echo "<h3>The following input errors were detected:</h3>";
    echo "<p>" . $errorMsg . "</p>";
    echo '<a href="login.php" class="return-to-log-in-btn mt-3">Return to Login</a>';
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

/*
* Helper function to authenticate the login.
*/
function authenticateUser()
{
    global $fname, $lname, $email, $pwd_hashed, $errorMsg, $success;
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
            $stmt = $conn->prepare("SELECT * FROM world_of_pets_members WHERE email=?");
            // Bind & execute the query statement:
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0)
            {
                // Note that email field is unique, so should only have
                // one row in the result set.
                $row = $result->fetch_assoc();
                $fname = $row["fname"];
                $lname = $row["lname"];
                $pwd_hashed = $row["password"];
                // Check if the password matches:
                if (!password_verify($_POST["pwd"], $pwd_hashed))
                {
                    // Don't be too specific with the error message - hackers don't
                    // need to know which one they got right or wrong. :)
                    $errorMsg = "Email not found or password doesn't match...";
                    $success = false;
                }
            }
            else
            {
                $errorMsg = "Email not found or password doesn't match...";
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

function GenerateRandomToken()
{
    return bin2hex(random_bytes(16)); // Generates a 128-bit token
}

function storeTokenForUser($email, $token)
{
    global $errorMsg, $success;
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config)
    {
        $errorMsg = "Failed to read database config file.";
        $success = false;
        return; // Exit function
    }
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
        return; // Exit function
    }
    // Prepare the statement
    $stmt = $conn->prepare("UPDATE world_of_pets_members SET token = ? WHERE email = ?");
    if (!$stmt)
    {
        $errorMsg = "Failed to prepare SQL statement: " . $conn->error;
        $success = false;
        $conn->close();
        return; // Exit function
    }
    // Bind parameters and execute the statement
    $stmt->bind_param("ss", $token, $email);
    if (!$stmt->execute())
    {
        $errorMsg = "Failed to execute SQL statement: " . $stmt->error;
        $success = false;
    }
    // Close the statement and connection
    $stmt->close();
    $conn->close();
}


function onLogin($email, $rememberme)
{
    // Perform authentication logic here
    $_SESSION['loggedin'] = true; // Set session variable indicating the user is logged in
    // Handle remember me functionality
    if ($rememberme) {
        $token = GenerateRandomToken(); // generate a token, should be 128-bit or more
        storeTokenForUser($email, $token);
        $cookie = $email . ':' . $token;
        $mac = hash_hmac('sha256', $cookie, SECRET_KEY);
        $cookie .= ':' . $mac;
        setcookie('rememberme', $cookie, time() + (86400 * 30), "/"); // Cookie valid for 30 days
    }
}

function fetchTokenByEmail($email)
{
    $token = null;
    $config = parse_ini_file('/var/www/private/db-config.ini');
    if (!$config)
    {
        $errorMsg = "Failed to read database config file.";
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
        }
        else
        {
            $stmt = $conn->prepare("SELECT token FROM world_of_pets_members WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($token);
            $stmt->fetch();
        }
        $stmt->close();
    }
    $conn->close();
    return $token;
}

function rememberMe()
{
    if (isset($_COOKIE['rememberme']))
    {
        list($email, $token, $mac) = explode(':', $_COOKIE['rememberme']);
        if (hash_equals(hash_hmac('sha256', $email . ':' . $token, SECRET_KEY), $mac))
        {
            $storedToken = fetchTokenByEmail($email);
            if ($storedToken !== null && hash_equals($storedToken, $token))
            {
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $email;
            }
        }
    }
}
?>
