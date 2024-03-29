<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$currentEmail = $_SESSION['user_email']; // User's current email from the session
$currentPasswordProvided = $_POST["current_pwd"];
$firstName = $lastName = $newEmail = $gender = $password = $confirmPassword = $errorMsg = "";
$success = true;

// Validate and assign input
if (!empty($_POST["gender"])) {
    $gender = sanitize_input($_POST["gender"]);
}

if (!empty($_POST["lname"])) {
    $lastName = sanitize_input($_POST["lname"]);
}

if (!empty($_POST["fname"])) {
    $firstName = sanitize_input($_POST["fname"]);
}

if (!empty($_POST["email"])) {
    $newEmail = sanitize_input($_POST["email"]);
    // Additional check to make sure e-mail address is well-formed.
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL))
    {
        $errorMsg .= "Invalid email format.";
        $success = false;
    }
}

if (!empty($_POST["pwd"]) && !empty($_POST["pwd_confirm"])) {
    if ($_POST["pwd"] !== $_POST["pwd_confirm"]) {
        $errorMsg .= "Passwords do not match.<br>";
        $success = false;
    } else {
        $password = $_POST["pwd"];
    }
}

// Proceed with the update if no errors
if ($success) {
    updateProfile($currentEmail, $newEmail, $firstName, $lastName, $gender, $password, $currentPasswordProvided);
} else {
    $_SESSION['update_success'] = false;
    $_SESSION['error_msg'] = $errorMsg;
    header("Location: user_profile.php"); // Redirect back to the profile page with error message
    exit;
}

/*
 * Function to update user profile in the database.
 */
function updateProfile($currentEmail, $newEmail, $firstName, $lastName, $gender, $password, $currentPasswordProvided) {
    global $errorMsg, $success;
    $config = parse_ini_file('/var/www/private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
    
    if ($conn->connect_error) {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        $success = false;
        return;
    }
    
    // Check if new email is different from current email
    if ($newEmail && $newEmail !== $currentEmail) {
        // Ensure the new email does not exist in the database
        if (emailExists($newEmail, $conn)) {
            $errorMsg = "New email is already in use by another account.";
            $success = false;
            return;
        }
    }

    if (!empty($password)) {
        // Fetch the current hashed password from the database
        $stmt = $conn->prepare("SELECT password FROM hotel_members WHERE email = ?");
        $stmt->bind_param("s", $currentEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentHashedPassword = $row['password'];
            // Verify the provided current password with the hashed password from the database
            if (!password_verify($currentPasswordProvided, $currentHashedPassword)) {
                $errorMsg = "Current password is incorrect.";
                $success = false;
                return;
            }
        } else {
            $errorMsg = "User not found.";
            $success = false;
            return;
        }
    }
    
    // Prepare SQL statement for updating the profile
    $sql = "UPDATE hotel_members SET fname=?, lname=?, gender=?, email=? ".(!empty($password) ? ", password=?" : "")." WHERE email=?";
    $stmt = $conn->prepare($sql);
    
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ssssss", $firstName, $lastName, $gender, $newEmail, $hashedPassword, $currentEmail);
    } else {
        $stmt->bind_param("sssss", $firstName, $lastName, $gender, $newEmail, $currentEmail);
    }

    if (!$stmt->execute()) {
        $errorMsg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        $success = false;
    } else {
        $_SESSION['update_success'] = true; // Use this to show a success message on the profile page
        $_SESSION['user_email'] = $newEmail; // Update the email in the session
        $_SESSION['fname'] = $firstName;
        $_SESSION['lname'] = $lastName;
    }

    $stmt->close();
    $conn->close();
    
    if ($success) {
        header("Location: user_profile.php"); // Redirect back to the profile page on success
    } else {
        $_SESSION['update_success'] = false;
        $_SESSION['error_msg'] = $errorMsg;
        header("Location: user_profile.php"); // Redirect back to the profile page with error message
    }
}

/*
 * Helper function to check if an email already exists in the database.
 */
function emailExists($email, $conn) {
    $stmt = $conn->prepare("SELECT email FROM hotel_members WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
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
?>
