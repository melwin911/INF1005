<?php
session_start();

$headSection = "nonmember_head.inc.php"; // Default to non-member head

// Check if the authentication cookie exists
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Include the member head section if the auth cookie exists
    $headSection = "member_head.inc.php";
} else {
    // Redirect to member_page.php if the user is already logged in
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
    include "head.inc.php";
    ?>

  <body>
  <?php
    include "header.inc.php";
    include "member_navbar.inc.php";
    include $headSection;
    renderNavbar('User Profile');
    ?>

<?php
    // Initialize variables
    $errorMsg = "";
    $user = [];
    $success = true;
    $email = $_SESSION['user_email'];

    // Create database connection using the existing config file
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
            // Use prepared statement to safely execute the query
            $stmt = $conn->prepare("SELECT fname, lname, gender, email FROM hotel_members WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result && $result->num_rows > 0) {
                // Fetch user data
                $user = $result->fetch_assoc(); // Fetch single row, as email is unique
            } else {
                $errorMsg = "No user details found.";
                $success = false;
            }

            $stmt->close(); // Close the prepared statement
        }
        $conn->close();
    }
    ?>

<main class="container">
    <h1>User Profile</h1>
    <p>View or update your profile information below.</p>
    <form id="userProfileForm" action="process_update_profile.php" method="post" onsubmit="return confirmProfileUpdate();">
        <div class="mb-3">
            <label for="fname" class="form-label">First Name:</label>
            <input maxlength="45" type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($user['fname']); ?>">
        </div>

        <div class="mb-3">
            <label for="lname" class="form-label">Last Name:</label>
            <input required maxlength="45" type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($user['lname']); ?>">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input required maxlength="45" type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        
        <div class="mb-3">
            <label>Gender:</label>
            <div>
                <input type="radio" id="male" name="gender" value="male" required <?php echo $user['gender'] == 'male' ? 'checked' : ''; ?>>
                <label for="male">Male</label>
            </div>
            <div>
                <input type="radio" id="female" name="gender" value="female" required <?php echo $user['gender'] == 'female' ? 'checked' : ''; ?>>
                <label for="female">Female</label>
            </div>
            <div>
                <input type="radio" id="other" name="gender" value="other" required <?php echo $user['gender'] == 'other' ? 'checked' : ''; ?>>
                <label for="other">Other</label>
            </div>
        </div>

        <div class="password-update-section">
            <h2>Update Password</h2>
            <p class="text-muted">Leave blank to keep your current password.</p>

            <div class="mb-3">
                <label for="current_pwd" class="form-label">Current Password:</label>
                <input type="password" id="current_pwd" name="current_pwd" class="form-control" placeholder="Enter current password">
            </div>

            <div class="mb-3">
                <label for="new_pwd" class="form-label">New Password:</label>
                <input type="password" id="new_pwd" name="new_pwd" class="form-control" placeholder="Enter new password">
            </div>

            <div class="mb-3">
                <label for="confirm_new_pwd" class="form-label">Confirm New Password:</label>
                <input type="password" id="confirm_new_pwd" name="confirm_new_pwd" class="form-control" placeholder="Confirm new password">
            </div>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary" id="updateProfileBtn" disabled>Update Profile</button>
        </div>
    </form>
</main>
        <?php
        include "footer.inc.php";
        ?>
        <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
<script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
<script src="js/main.js"></script>
<script>
function confirmProfileUpdate() {
    var confirmUpdate = confirm('Are you sure you want to update your profile with these details?');
    if (!confirmUpdate) {
        return false; // Stop form submission
    }
    return validatePasswords(); // Continue to validate passwords if confirmed
}
</script>
<script>
document.getElementById('userProfileForm').addEventListener('change', function() {
    document.getElementById('updateProfileBtn').disabled = false;
});
</script>
<script>
function validatePasswords() {
    let currentPwd = document.getElementById('current_pwd').value;
    let newPwd = document.getElementById('new_pwd').value;
    let confirmNewPwd = document.getElementById('confirm_new_pwd').value;

    if (newPwd || confirmNewPwd) {
        if (newPwd !== confirmNewPwd) {
            alert("New passwords do not match.");
            return false;
        }
        if (!currentPwd) {
            alert("Please enter your current password to update to a new password.");
            return false;
        }
    }
    return true;
}
</script>
    </body>
</html>