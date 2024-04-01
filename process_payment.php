<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Generate a unique order ID before starting the booking transaction
        $orderId = time();

        // Start transaction
        $conn->begin_transaction();

        // Retrieve selected item IDs from the form submission
        $selectedItemIds = $_SESSION['selected_rooms'] ?? [];

        // Prepare to select cart items
        $stmt = $conn->prepare("SELECT * FROM cart_items WHERE cart_id = (SELECT cart_id FROM carts WHERE member_id = ?)");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $cartItemsResult = $stmt->get_result();

        $totalDemand = []; // To track total demand for each room type
        $allCartItems = []; // To keep track of all cart items for later processing

        // Calculate total demand for each room type
        while ($cartItem = $cartItemsResult->fetch_assoc()) {
            if (in_array($cartItem['item_id'], $selectedItemIds)) { // Keep only selected items
                $roomTypeId = $cartItem['room_type_id'];
                $numRoomsNeeded = $cartItem['num_rooms'];
                if (!isset($totalDemand[$roomTypeId])) {
                    $totalDemand[$roomTypeId] = 0;
                }
                $totalDemand[$roomTypeId] += $numRoomsNeeded;
                $allCartItems[] = $cartItem; // Save selected cart items for later processing
            }
        }

        // Check if there is enough availability for each room type to meet total demand
        foreach ($totalDemand as $roomTypeId => $numRoomsNeeded) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS availableRooms FROM rooms WHERE room_type_id = ? AND availability = 1");
            $stmt->bind_param("i", $roomTypeId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result['availableRooms'] < $numRoomsNeeded) {
                throw new Exception("Transaction cancelled. There are not enough rooms available for booking.");
            }
        }

        $allocatedRooms = [];

        foreach ($allCartItems as $cartItem){
            $stmt = $conn->prepare("SELECT room_id FROM rooms WHERE room_type_id = ? AND availability = 1 LIMIT ?");
            $stmt->bind_param("ii", $cartItem['room_type_id'], $cartItem['num_rooms']);
            $stmt->execute();
            $availableRoomsResult = $stmt->get_result();

            // Temporary array to hold room IDs for the current cart item
            $tempRooms = [];
            while ($room = $availableRoomsResult->fetch_assoc()) {
                $tempRooms[] = $room['room_id'];
            }

            // Only if we have enough rooms, assign them to the cart item
            if (count($tempRooms) == $cartItem['num_rooms']) {
                // Store the entire cart item and its allocated rooms together
                $allocatedRooms[] = ['cartItem' => $cartItem, 'rooms' => $tempRooms];
            } else {
                // If not enough rooms are available
                throw new Exception("Unexpected error: Not enough rooms available when attempting to allocate.");
            }
        }

        foreach ($allocatedRooms as $allocation) {
            $cartItem = $allocation['cartItem'];
            foreach ($allocation['rooms'] as $roomId) {
                // Update room availability
                $stmt = $conn->prepare("UPDATE rooms SET availability = 0 WHERE room_id = ?");
                $stmt->bind_param("i", $roomId);
                $stmt->execute();

                // Insert booking record
                $stmt = $conn->prepare("INSERT INTO bookings (room_type_id, order_id, member_id, room_id, check_in_date, check_out_date, total_price, num_rooms, num_guests, guest_name, guest_email, guest_phone, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiiissiiisss", $cartItem['room_type_id'], $orderId, $memberId, $roomId, $cartItem['check_in_date'], $cartItem['check_out_date'], $amount, $cartItem['num_rooms'], $cartItem['num_guests'], $cartItem['guest_name'], $cartItem['guest_email'], $cartItem['guest_phone']);
                $stmt->execute();
            }
            
            // Delete the processed cart item
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE item_id = ?");
            $stmt->bind_param("i", $cartItem['item_id']);
            $stmt->execute();
        }

        $charge = \Stripe\Charge::create([
            "amount" => $amount, // Amount in cents
            "currency" => "sgd",
            "description" => "Hotel Booking Charge",
            "source" => $token,
        ]);

        // Check if there are no more items in the cart, then delete the cart
        $stmt = $conn->prepare("DELETE FROM carts WHERE cart_id NOT IN (SELECT cart_id FROM cart_items)");
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        $stmt->close();
        $conn->close();

        header('Location: view_my_bookings.php');
        exit;
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle Stripe-specific errors
    header("Location: payment.php?error=" . urlencode("Stripe API Error: " . $e->getMessage()));
    exit;
} catch (Exception $e) {
    // Rollback the transaction if any exception occurs
    if (isset($conn)) {
        $conn->rollback();
    }
    header("Location: payment.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>
