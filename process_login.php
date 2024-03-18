<head>
    <title>Test Hotel</title>
    <?php
    include "head.inc.php";
    ?>
</head>

<body>
    <?php
    include "navbar.inc.php";
    include "headsection.inc.php";
    ?>


<?php
// Initialize variables
$email = $pwd = "";
$errorMsg = "";
$success = true;

// Check if form fields are set
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize and validate email
    if (empty($_POST["email"])) {
        $errorMsg .= "Email is required.<br>";
        $success = false;
    } else {
        $email = sanitize_input($_POST["email"]);
        // Additional check to make sure email address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg .= "Invalid email format.<br>";
            $success = false;
        }
    }

    // Validate password
    if (empty($_POST["pwd"])) {
        $errorMsg .= "Password is required.<br>";
        $success = false;
    } else {
        $pwd = $_POST["pwd"];
    }
}

if ($success) {
    authenticateUser();
}

if ($success) {
    echo '<br><div style="text-align: left; margin: 0 auto; width: 50%;">';
    echo "<h3>Login successful!</h3>";
    echo "<h4><p>Welcome back, " . $fname . " " . $lname . ".</p></h4>";
    echo '<button class="btn btn-primary"><a href="index.php" style="text-decoration: none; color: white;">Return to Home</a></button>';
    echo '</div><br>';
} else {
    echo '<br><div style="text-align: left; margin: 0 auto; width: 50%;">';
    echo "<h3>Oops! </h3> <h4>The following errors were detected:</h4>";
    echo "<p>" . $errorMsg . "</p>";
    echo '<button class="btn btn-primary"><a href="login.php" style="text-decoration: none; color: white;">Return to Login</a></button>';
    echo '</div><br>';
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
    if (!$config) {
        $errorMsg = "Failed to read database config file.";
        $success = false;
    } else {
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("SELECT * FROM hotel_members WHERE email=?");
            // Bind & execute the query statement:
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Note that email field is unique, so should only have
                // one row in the result set.
                $row = $result->fetch_assoc();
                $fname = $row["fname"];
                $lname = $row["lname"];
                $pwd_hashed = $row["password"];
                // Check if the password matches:
                if (!password_verify($_POST["pwd"], $pwd_hashed)) {
                    // Don't be too specific with the error message - hackers don't
                    // need to know which one they got right or wrong. :)
                    $errorMsg = "Email not found or password doesn't match...";
                    $success = false;
                }
            } else {
                $errorMsg = "Email not found or password doesn't match...";
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// function GenerateRandomToken()
// {
//     return bin2hex(random_bytes(16)); // Generates a 128-bit token
// }

// function storeTokenForUser($email, $token)
// {
//     global $errorMsg, $success;
//     $config = parse_ini_file('/var/www/private/db-config.ini');
//     if (!$config)
//     {
//         $errorMsg = "Failed to read database config file.";
//         $success = false;
//         return; // Exit function
//     }
//     $conn = new mysqli(
//         $config['servername'],
//         $config['username'],
//         $config['password'],
//         $config['dbname']
//     );
//     // Check connection
//     if ($conn->connect_error)
//     {
//         $errorMsg = "Connection failed: " . $conn->connect_error;
//         $success = false;
//         return; // Exit function
//     }
//     // Prepare the statement
//     $stmt = $conn->prepare("UPDATE hotel_members SET token = ? WHERE email = ?");
//     if (!$stmt)
//     {
//         $errorMsg = "Failed to prepare SQL statement: " . $conn->error;
//         $success = false;
//         $conn->close();
//         return; // Exit function
//     }
//     // Bind parameters and execute the statement
//     $stmt->bind_param("ss", $token, $email);
//     if (!$stmt->execute())
//     {
//         $errorMsg = "Failed to execute SQL statement: " . $stmt->error;
//         $success = false;
//     }
//     // Close the statement and connection
//     $stmt->close();
//     $conn->close();
// }


// function onLogin($email, $rememberme)
// {
//     // Perform authentication logic here
//     $_SESSION['loggedin'] = true; // Set session variable indicating the user is logged in
//     // Handle remember me functionality
//     if ($rememberme) {
//         $token = GenerateRandomToken(); // generate a token, should be 128-bit or more
//         storeTokenForUser($email, $token);
//         $cookie = $email . ':' . $token;
//         $mac = hash_hmac('sha256', $cookie, SECRET_KEY);
//         $cookie .= ':' . $mac;
//         setcookie('rememberme', $cookie, time() + (86400 * 30), "/"); // Cookie valid for 30 days
//     }
// }

// function fetchTokenByEmail($email)
// {
//     $token = null;
//     $config = parse_ini_file('/var/www/private/db-config.ini');
//     if (!$config)
//     {
//         $errorMsg = "Failed to read database config file.";
//     }
//     else
//     {
//         $conn = new mysqli(
//             $config['servername'],
//             $config['username'],
//             $config['password'],
//             $config['dbname']
//         );
//         // Check connection
//         if ($conn->connect_error)
//         {
//             $errorMsg = "Connection failed: " . $conn->connect_error;
//         }
//         else
//         {
//             $stmt = $conn->prepare("SELECT token FROM hotel_members WHERE email = ?");
//             $stmt->bind_param("s", $email);
//             $stmt->execute();
//             $stmt->bind_result($token);
//             $stmt->fetch();
//         }
//         $stmt->close();
//     }
//     $conn->close();
//     return $token;
// }

// function rememberMe()
// {
//     if (isset($_COOKIE['rememberme']))
//     {
//         list($email, $token, $mac) = explode(':', $_COOKIE['rememberme']);
//         if (hash_equals(hash_hmac('sha256', $email . ':' . $token, SECRET_KEY), $mac))
//         {
//             $storedToken = fetchTokenByEmail($email);
//             if ($storedToken !== null && hash_equals($storedToken, $token))
//             {
//                 $_SESSION['loggedin'] = true;
//                 $_SESSION['email'] = $email;
//             }
//         }
//     }
// }
?>

<?php
    include "footer.inc.php";
    ?>

</body>
