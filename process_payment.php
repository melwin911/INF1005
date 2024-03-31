<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: rooms.php');
    exit;
}

$amount = $_POST['amount'];
$memberId = $_SESSION['member_id']; // Assuming member_id is stored in session

require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51I3z2RGZozntPACPaBmQYQLQaNn5C8EgP92kHeSH3oBsEYaVV1hUxDOZ8YZAHSevP1mooArI45a9DVo9XindVKwt00kf84rT4r');

// Retrieve the request's body and parse it as JSON
$token = $_POST['stripeToken'];

// Charge the user's card:
try {
    $charge = \Stripe\Charge::create([
        "amount" => $amount, // Amount in cents
        "currency" => "sgd",
        "description" => "Hotel Booking Charge",
        "source" => $token,
    ]);

    // Proceed with booking process only if the charge is successful

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

        // Prepare to select cart items
        $stmt = $conn->prepare("SELECT * FROM cart_items WHERE cart_id = (SELECT cart_id FROM carts WHERE member_id = ?)");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $cartItemsResult = $stmt->get_result();

        while ($cartItem = $cartItemsResult->fetch_assoc()) {
            // Check availability of rooms
            $stmt = $conn->prepare("SELECT room_id FROM rooms WHERE room_type_id = ? AND availability = 1 LIMIT ?");
            $stmt->bind_param("ii", $cartItem['room_type_id'], $cartItem['num_rooms']);
            $stmt->execute();
            $availableRoomsResult = $stmt->get_result();

            $availableRooms = [];
            while ($room = $availableRoomsResult->fetch_assoc()) {
                $availableRooms[] = $room['room_id'];
            }

            // Verify if enough rooms are available
            if (count($availableRooms) < $cartItem['num_rooms']) {
                throw new Exception("Not enough rooms available for booking.");
            }

            // Book available rooms
            foreach ($availableRooms as $roomId) {
                // Update room availability
                $stmt = $conn->prepare("UPDATE rooms SET availability = 0 WHERE room_id = ?");
                $stmt->bind_param("i", $roomId);
                $stmt->execute();

                // Insert booking record
                $stmt = $conn->prepare("INSERT INTO bookings (member_id, room_id, check_in_date, check_out_date, total_price, num_rooms, num_guests, guest_name, guest_email, guest_phone, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iissiiisss", $memberId, $roomId, $cartItem['check_in_date'], $cartItem['check_out_date'], $amount, $cartItem['num_rooms'], $cartItem['num_guests'], $cartItem['guest_name'], $cartItem['guest_email'], $cartItem['guest_phone']);
                $stmt->execute();
            }

            // Delete the processed cart item
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE item_id = ?");
            $stmt->bind_param("i", $cartItem['item_id']);
            $stmt->execute();
        }

        // Check if there are no more items in the cart, then delete the cart
        $stmt = $conn->prepare("DELETE FROM carts WHERE cart_id NOT IN (SELECT cart_id FROM cart_items)");
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        $stmt->close();
        $conn->close();

        header('Location: view_cart.php');
        exit;
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle Stripe API errors
    echo "Error: " . $e->getMessage();
} catch (Exception $e) {
    // Handle general errors
    if (isset($conn) && $conn->connect_error == false) {
        $conn->rollback(); // Rollback the transaction on error
        $conn->close();
    }
    echo "Error: " . $e->getMessage();
}
