<?php
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['member_id'])) {
    // Redirect if not logged in
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selectedRooms = $_POST['selected_rooms'] ?? [];
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'checkout':
            $_SESSION['selected_rooms'] = $selectedRooms; // Store in session
            // Redirect to payment.php
            header('Location: payment.php');
            exit;
            break;
        case 'delete':
            // Delete selected rooms from cart
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
                if (!empty($selectedRooms)) {
                    // Prepare a placeholder string with the correct number of placeholders
                    $placeholders = implode(',', array_fill(0, count($selectedRooms), '?'));

                    // Prepare the SQL statement
                    $stmt = $conn->prepare("DELETE FROM cart_items WHERE item_id IN ($placeholders)");

                    // Dynamically bind the selected room IDs to the placeholders
                    $stmt->bind_param(str_repeat('i', count($selectedRooms)), ...$selectedRooms);

                    // Execute the query
                    if ($stmt->execute()) {
                        // Redirect or display success message
                        $_SESSION['message'] = "Selected rooms have been removed from your cart.";
                    } else {
                        // Handle error
                        $_SESSION['error'] = "There was a problem removing rooms from your cart.";
                    }
                    $stmt->close();
                    $conn->close();
                }
            }
            header('Location: view_cart.php');
            exit;
            break;
        case 'edit':
            // Redirect to page to edit selected room
            if (!empty($selectedRooms)) {
                // Store the first selected room's ID in the session for editing
                $_SESSION['edit_booking_id'] = $selectedRooms[0];
                
                // Redirect to the edit booking page
                header('Location: edit_booking.php');
                exit;
            } else {
                // Case where no rooms are selected for editing
                $_SESSION['error_msg'] = "Please select a booking to edit.";
                header('Location: view_cart.php');
                exit;
            }
            break;
        default:
            // Handle unknown action
            break;
    }
}
?>