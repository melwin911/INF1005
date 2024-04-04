<?php
session_start();
// Check if the edit_booking_id is set in the session
if (!isset($_SESSION['edit_booking_id'])) {
    // Handle the error - redirect back or show an error message
    header('Location: view_cart.php');
    exit;
}

$bookingId = $_SESSION['edit_booking_id'];

$headSection = "member_head.inc.php";
$navBar = "member_navbar.inc.php";

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
    // Fetch the booking details
    $stmt = $conn->prepare("SELECT ci.*, rt.room_type, rt.description, rt.price_per_night, rt.image_path 
    FROM cart_items ci 
    JOIN room_types rt ON ci.room_type_id = rt.room_type_id 
    WHERE ci.item_id = ?
    ");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $roomDetails = $result->fetch_assoc();
    } else {
        // Handle the case where no booking details are found
        $errorMsg = "Booking details not found.";
        $success = false;
    }
    $stmt->close();

    $roomTypeID = null;

    $stmt = $conn->prepare("
        SELECT room_type_id
        FROM cart_items
        WHERE item_id = ?
    ");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $roomTypeID = $row['room_type_id'];
    } else {
        // No room type is found for the bookingId
        $errorMsg = "No room type found for the given booking ID.";
        $success = false;
    }

    $stmt->close();

    // Fetch the current number of available rooms for the selected room type
    $availableRooms = 0;
    $stmt = $conn->prepare("
                SELECT room_id
                FROM rooms
                WHERE room_type_id = ?
                AND (
                    availability = 1
                    OR (availability = 0 AND room_id NOT IN (
                    SELECT room_id
                    FROM bookings
                    WHERE room_type_id = ?
                        AND check_out_date >= CURDATE()
                    ))
                );
            ");
    $stmt->bind_param("ii", $roomTypeID, $roomTypeID);
    $stmt->execute();
    $result = $stmt->get_result();
    // Count the number of rows returned, which represents the number of available rooms
    $availableRooms = $result->num_rows;
    $stmt->close();

    $conn->close();
}
?>

<!DOCTYPE HTML>
<html lang="en">

<head>
    <?php include "head.inc.php"; ?>
</head>

<body>
    <?php
    include "header.inc.php";
    include $navBar;
    include $headSection;
    renderNavbar('Booking Cart');
    ?>

    <br>
    <main>
        <div class="container">
            <?php if (!empty($roomDetails)) : ?>
                <div class="back-button">
            <button onclick="window.location.href = 'view_cart.php'" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>
                <h2>Edit Booking: <?php echo htmlspecialchars($roomDetails['room_type']); ?></h2>
                <img src="images/rooms/<?php echo htmlspecialchars($roomDetails['image_path']); ?>" alt="Room image" class="img-fluid">
                <p><?php echo htmlspecialchars($roomDetails['description']); ?></p>
                <p>Price per night: $<?php echo htmlspecialchars($roomDetails['price_per_night']); ?></p>

                <!-- Booking form -->
                <form action="update_cart.php" method="post">
                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($bookingId) ?>">

                    <div class="form-group">
                        <label for="check_in_date">Check-in Date:</label>
                        <input type="date" id="check_in_date" name="check_in_date" class="form-control" value="<?= isset($roomDetails['check_in_date']) ? date('Y-m-d', strtotime($roomDetails['check_in_date'])) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="check_out_date">Check-out Date:</label>
                        <input type="date" id="check_out_date" name="check_out_date" class="form-control" value="<?= isset($roomDetails['check_out_date']) ? date('Y-m-d', strtotime($roomDetails['check_out_date'])) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="num_rooms">Number of Rooms:</label>
                        <?php if ($availableRooms > 0) : ?>
                        <select required name="num_rooms" id="num_rooms" class="form-control">
                            <?php for ($i = 1; $i <= min(10, $availableRooms); $i++) : ?>
                                <option value="<?= $i ?>" <?= isset($roomDetails['num_rooms']) && $i == $roomDetails['num_rooms'] ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <?php else : ?>
                        <p class="text-danger">Fully Booked</p>
                    <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="num_guests">Number of Guests:</label>
                        <select required name="num_guests" id="num_guests" class="form-control">
                            <?php for ($i = 1; $i <= 20; $i++) : ?>
                                <option value="<?= $i ?>" <?= isset($roomDetails['num_guests']) && $i == $roomDetails['num_guests'] ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="guest_name">Guest Name:</label>
                        <input type="text" id="guest_name" name="guest_name" class="form-control" placeholder="Full Name" value="<?= htmlspecialchars($roomDetails['guest_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="guest_email">Guest Email:</label>
                        <input type="email" id="guest_email" name="guest_email" class="form-control" placeholder="email@example.com" value="<?= htmlspecialchars($roomDetails['guest_email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="guest_phone">Guest Phone Number:</label>
                        <input type="tel" id="guest_phone" name="guest_phone" class="form-control" placeholder="+65 91234567" value="<?= htmlspecialchars($roomDetails['guest_phone'] ?? '') ?>" required>
                    </div>

                    <!-- Total Price Display -->
                    <div class="form-group">
                        <label>Total Price:</label>
                        <p id="totalPrice" class="font-weight-bold"></p>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Booking</button>
                </form>
                <br>
            <?php else : ?>
                <p>Booking details not found.</p>
            <?php endif; ?>
        </div>
        </main>

    <?php include "footer.inc.php"; ?>
    <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
    <script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>

    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/jquery.timepicker.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        function calculateTotal() {
            const pricePerNight = <?php echo json_encode($roomDetails['price_per_night']); ?>;
            const numNights = (new Date(document.getElementById('check_out_date').value) - new Date(document.getElementById('check_in_date').value)) / (1000 * 60 * 60 * 24);
            const numRooms = document.getElementById('num_rooms').value;

            if (numNights > 0 && numRooms > 0) {
                const totalPrice = pricePerNight * numNights * numRooms;
                document.getElementById('totalPrice').textContent = '$' + totalPrice.toFixed(2);
            } else {
                document.getElementById('totalPrice').textContent = '';
            }
        }

        // Add event listeners to form fields to recalculate total when they change
        document.getElementById('check_in_date').addEventListener('change', calculateTotal);
        document.getElementById('check_out_date').addEventListener('change', calculateTotal);
        document.getElementById('num_rooms').addEventListener('change', calculateTotal);

        // Calculate initial total in case the form is pre-filled or when revisiting the page
        calculateTotal();
    </script>
</body>

</html>