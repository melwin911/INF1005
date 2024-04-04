<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to index.php if the user is not logged in
    header('Location: rooms.php');
    exit;
}

if (!isset($_GET['room_type_id'])) {
    // Redirect to index.php if the user is not logged in
    header('Location: rooms.php');
    exit;
}

$headSection = "member_head.inc.php";
$navBar = "member_navbar.inc.php";

$roomTypeID = isset($_GET['room_type_id']) ? (int) $_GET['room_type_id'] : 0; // Cast to int to ensure it's a valid integer

// Initialize variables
$errorMsg = "";
$roomDetails = [];
$success = true;

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
        $stmt = $conn->prepare("SELECT room_type_id, room_type, description, price_per_night, image_path FROM room_types WHERE room_type_id = ?");
        $stmt->bind_param("i", $roomTypeID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $roomDetails = $result->fetch_assoc();
        } else {
            $errorMsg = "No rooms found.";
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
    }
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
    renderNavbar('Rooms');
    ?>

    <br>
    <main>
    <div class="container">
        <?php if (!empty($roomDetails)) : ?>
            <div class="back-button">
                    <button onclick="goBack()" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
            </div>
            <h2>Booking: <?php echo htmlspecialchars($roomDetails['room_type']); ?></h2>
            <img src="images/rooms/<?php echo htmlspecialchars($roomDetails['image_path']); ?>" alt="Room image" class="img-fluid">
            <p><?php echo htmlspecialchars($roomDetails['description']); ?></p>
            <p>Price per night: $<?php echo htmlspecialchars($roomDetails['price_per_night']); ?></p>

            <!-- Booking form -->
            <form action="add_to_cart.php" method="post" onsubmit="return validateBooking()">
                <input type="hidden" name="room_type_id" value="<?php echo htmlspecialchars($roomTypeID); ?>">

                <!-- Check-in Date -->
                <div class="form-group">
                    <label for="check_in_date">Check-in Date:</label>
                    <input type="date" id="check_in_date" name="check_in_date" class="form-control" required>
                </div>

                <!-- Check-out Date -->
                <div class="form-group">
                    <label for="check_out_date">Check-out Date:</label>
                    <input type="date" id="check_out_date" name="check_out_date" class="form-control" required>
                </div>

                <!-- Number of Rooms Dropdown -->
                <div class="form-group">
                    <label for="num_rooms">Number of Rooms:</label>
                    <?php if ($availableRooms > 0) : ?>
                        <select required name="num_rooms" id="num_rooms" class="form-control">
                            <?php for ($i = 1; $i <= min(10, $availableRooms); $i++) : ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    <?php else : ?>
                        <p class="text-danger">Fully Booked</p>
                    <?php endif; ?>
                </div>

                <!-- Number of Guests (Pax) Dropdown -->
                <div class="form-group">
                    <label for="num_guests">Number of Guests:</label>
                    <select required name="num_guests" id="num_guests" class="form-control">
                        <?php for ($i = 1; $i <= 20; $i++) : ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Guest Information -->
                <div class="form-group">
                    <label for="guest_name">Guest Name:</label>
                    <input type="text" id="guest_name" name="guest_name" class="form-control" placeholder="Full Name" required>
                </div>

                <div class="form-group">
                    <label for="guest_email">Guest Email:</label>
                    <input type="email" id="guest_email" name="guest_email" class="form-control" placeholder="email@example.com" required>
                </div>

                <div class="form-group">
                    <label for="guest_phone">Guest Phone Number:</label>
                    <input type="tel" id="guest_phone" name="guest_phone" class="form-control" placeholder="+65 91234567" required>
                </div>

                <!-- Total Price Display -->
                <div class="form-group">
                    <label>Total Price:</label>
                    <p id="totalPrice" class="font-weight-bold"></p>
                </div>

                <button type="submit" class="btn btn-primary">Add to Booking Cart</button>
            </form>
            <br>
        <?php else : ?>
            <p>Room details not found.</p>
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
    <script>
        function validateBooking() {
            const checkInDateInput = document.getElementById('check_in_date');
            const checkOutDateInput = document.getElementById('check_out_date');

            // 1. Check if check-in date is not a historical date
            const currentDate = new Date();
            const checkInDate = new Date(checkInDateInput.value);
            if (checkInDate < currentDate) {
                alert("Please select a future date for check-in.");
                return false;
            }

            // 2. Check if check-in date is before check-out date
            const checkOutDate = new Date(checkOutDateInput.value);
            if (checkInDate >= checkOutDate) {
                alert("Check-out date must be after check-in date.");
                return false;
            }

            // 3. Check if check-in date is not the same day as check-out date
            const diffInDays = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
            if (diffInDays < 1) {
                alert("Check-in and check-out dates cannot be the same day.");
                return false;
            }

            return true; // Form is valid
        }
    </script>
            <script>
            function goBack() {
                window.history.back();
            }
        </script>
</body>

</html>