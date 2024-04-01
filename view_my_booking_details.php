<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['member_id'])) {
    // Redirect if not logged in
    header('Location: login.php');
    exit;
}

$headSection = "member_head.inc.php";
$navBar = "member_navbar.inc.php";

$memberId = $_SESSION['member_id'];
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

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

    $stmt = $conn->prepare("
        SELECT 
        MIN(b.booking_id) AS booking_id,
        b.member_id,
        b.order_id,
        b.room_type_id,
        b.total_price,
        b.guest_name,
        b.guest_email,
        b.guest_phone,
        b.created_at,
        rt.room_type,
        MIN(b.check_in_date) AS check_in_date, 
        MAX(b.check_out_date) AS check_out_date,
        MAX(b.num_rooms) AS total_rooms, 
        MAX(b.num_guests) AS total_guests
        FROM bookings b
        JOIN room_types rt ON b.room_type_id = rt.room_type_id
        WHERE b.member_id = ? AND b.order_id = ?
        GROUP BY b.room_type_id, b.member_id, b.order_id, b.total_price, b.guest_name, b.guest_email, b.guest_phone, b.created_at, rt.room_type
    ");
    $stmt->bind_param("ii", $memberId, $order_id);
    $stmt->execute();
    $bookingsResult = $stmt->get_result();

    $bookings = [];
    if ($bookingsResult->num_rows > 0) {
        while ($booking = $bookingsResult->fetch_assoc()) {
            $bookings[] = $booking;
        }
    }

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
    renderNavbar('View My Bookings');
    ?>
    <main>
        <div class="container mt-4">
            <?php if (count($bookings) > 0) : ?>
                <div class="back-button">
                    <button onclick="goBack()" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
                <h2 class="heading" data-aos="fade-up">Order <?= htmlspecialchars($order_id) ?></h2>
                <?php foreach ($bookings as $booking) : ?>
                    <div class="booking-details-container">
                        <h1>Booking Confirmation for <?= $booking['room_type'] ?>: #<?= $booking['booking_id'] ?></h1><br>
                        <h1>Booking Confirmation Date: <?= $booking['created_at'] ?></h1><br>
                        <section class="guest-info">
                            <h2>Guest Information</h2>
                            <p><strong><b>Name:</strong></b> <?= $booking['guest_name'] ?></p>
                            <p><strong><b>Email:</strong></b> <?= $booking['guest_email'] ?></p>
                            <p><strong><b>Phone:</strong></b> <?= $booking['guest_phone'] ?></p>
                        </section>
                        <section class="booking-dates">
                            <h2>Dates</h2>
                            <p><strong><b>Check-in:</strong></b> <?= $booking['check_in_date'] ?></p>
                            <p><strong><b>Check-out:</strong></b> <?= $booking['check_out_date'] ?></p>
                        </section>
                        <section class="room-details">
                            <h2>Room Details</h2>
                            <p><strong><b>Type:</strong></b> <?= $booking['room_type'] ?></p>
                            <p><strong><b>Total Number of Guests:</strong></b> <?= $booking['total_guests'] ?></p>
                            <p><strong><b>Total Number of Rooms:</strong></b> <?= $booking['total_rooms'] ?></p>
                        </section>
                        <section class="price">
                            <h2>Price</h2>
                            <p><strong><b>Booking Total: </strong></b>$<?= $booking['total_price'] ?></p>
                        </section>
                        <section class="cancellation-policy">
                            <h2>Cancellation Policy</h2>
                            <p><strong><b>Please email booking@fivestarhotel.com for any booking cancellations.</strong></b></p>
                        </section>
                    </div><br>
                <?php endforeach; ?>
            <?php else : ?>
                <p>
                <h3>Unable to load booking details. Please try again.</h3>
                </p>
            <?php endif; ?>
        </div>
        <?php include "footer.inc.php"; ?>
        <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
        <script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
        <script src="js/main.js"></script>
        <script>
            function goBack() {
                window.history.back();
            }
        </script>
    </main>
</body>

</html>