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
$fname = $lname = $gender = $email = $pwd = $pwd_confirm = $hashed_pwd = "";
$errorMsg = "";
$success = true;

// Check if form fields are set
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fname = sanitize_input($_POST["fname"]);

    // Sanitize and validate last name
    if (empty($_POST["lname"])) {
        $errorMsg .= "Last name is required.<br>";
        $success = false;
    } else {
        $lname = sanitize_input($_POST["lname"]);
    }

    // Sanitize and validate gender
    if (empty($_POST["gender"])) {
        $errorMsg .= "Gender is required.<br>";
        $success = false;
    } else {
        $gender = sanitize_input($_POST["gender"]);
    }

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

    // Validate password confirmation
    if (empty($_POST["pwd_confirm"])) {
        $errorMsg .= "Password confirmation is required.<br>";
        $success = false;
    } else {
        $pwd_confirm = $_POST["pwd_confirm"];
        if ($pwd !== $pwd_confirm) {
            $errorMsg .= "Passwords do not match.<br>";
            $success = false;
        }
        else 
        {
            // Hash the password
            $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
        }
    }
}

if ($success)
{
    saveMemberToDB();
    if ($success) {
        echo '<br><div style="text-align: left; margin: 0 auto; width: 50%;">';
        echo "<h3>Your registration is successful!</h3>";
        echo "<h4><p>Thank you for signing up, " . $fname . " " .$lname . ".</p></h4>";
        echo '<button class="btn btn-primary"><a href="login.php" style="text-decoration: none; color: white;">Log-in</a></button>';
        echo '</div><br>';
    }
}
if (!$success)
{
    echo '<br><div style="text-align: left; margin: 0 auto; width: 50%;">';
    echo "<h3>Oops! </h3> <h4>The following input errors were detected:</h4>";
    echo "<p>" . $errorMsg . "</p>";
    echo '<button class="btn btn-primary"><a href="registration.php" style="text-decoration: none; color: white;">Return to Sign Up</a></button>';
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
* Helper function to write the member data to the database.
*/
function saveMemberToDB()
{
    global $fname, $lname, $gender, $email, $hashed_pwd, $errorMsg, $success;
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
        }
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT email FROM hotel_members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Email already exists
            $errorMsg .= "This email is already registered.<br>";
            $success = false;
        } else {
            // Prepare the statement:
            $stmt = $conn->prepare("INSERT INTO hotel_members
            (fname, lname, gender, email, password) VALUES (?, ?, ?, ?, ?)");
            // Bind & execute the query statement:
            $stmt->bind_param("sssss", $fname, $lname, $gender, $email, $hashed_pwd);
            if (!$stmt->execute()) {
                $errorMsg = "Execute failed: (" . $stmt->errno . ") " .
                    $stmt->error;
                $success = false;
            }
            $stmt->close();
        }
        $conn->close();
    }
}

?>

<?php
    include "footer.inc.php";
    ?>

</body>