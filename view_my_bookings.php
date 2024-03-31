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
        order_id, 
        MIN(check_in_date) AS check_in_date, 
        MAX(check_out_date) AS check_out_date, 
        SUM(num_rooms) as total_rooms,  
        SUM(num_guests) as total_guests
        FROM (
            SELECT 
                order_id, 
                check_in_date, 
                check_out_date, 
                room_type_id, 
                MAX(num_rooms) AS num_rooms, 
                MAX(num_guests) AS num_guests
            FROM bookings
            WHERE member_id = ?
            GROUP BY order_id, room_type_id, check_in_date, check_out_date
        ) AS unique_bookings
        GROUP BY order_id;
    ");
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $ordersResult = $stmt->get_result();

    $orders = [];
    if ($ordersResult->num_rows > 0) {
        while ($order = $ordersResult->fetch_assoc()) {
            $orders[] = $order;
        }
    }

    $stmt = $conn->prepare("
        SELECT b.*, rt.*
        FROM bookings b
        JOIN room_types rt ON b.room_type_id = rt.room_type_id
        WHERE b.member_id = ?
    ");
    $stmt->bind_param("i", $memberId);
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
            <h2 class="heading" data-aos="fade-up">My Bookings</h2>
            <?php if (count($orders) > 0) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Booking Number</th>
                                <th>Check-in Date</th>
                                <th>Check-out Date</th>
                                <th>Number of Rooms</th>
                                <th>Number of Guests</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                                    <td><?= htmlspecialchars($order['check_in_date']) ?></td>
                                    <td><?= htmlspecialchars($order['check_out_date']) ?></td>
                                    <td><?= htmlspecialchars($order['total_rooms']) ?> rooms</td>
                                    <td><?= htmlspecialchars($order['total_guests']) ?> guests</td>
                                    <td><button type="button" class="btn btn-link" data-toggle="modal" data-target="#bookingDetailsModal<?= $order['order_id'] ?>" style="color: black; text-decoration: underline; padding: 0; margin: 0;">
                                            View Details
                                        </button></td>
                                </tr>
                                <div class="modal fade" id="bookingDetailsModal<?= $order['order_id'] ?>" tabindex="-1" role="dialog" aria-labelledby="bookingDetailsLabel<?= $order['order_id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="bookingDetailsLabel<?= $order['order_id'] ?>">Booking Details for Order <?= htmlspecialchars($order['order_id']) ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Loop through $bookings to display detailed information -->
                                                <?php foreach ($bookings as $booking) : ?>
                                                    <?php if ($booking['order_id'] == $order['order_id']) : ?>
                                                        <p>Room Type: <?= htmlspecialchars($booking['room_type']) ?></p>
                                                        <p>Check-in Date: <?= htmlspecialchars($booking['check_in_date']) ?></p>
                                                        <p>Check-out Date: <?= htmlspecialchars($booking['check_out_date']) ?></p>
                                                        <p>Total Price: $<?= htmlspecialchars($booking['total_price']) ?></p>
                                                        <p>Guest Name: <?= htmlspecialchars($booking['guest_name']) ?></p>
                                                        <p>Guest Email: <?= htmlspecialchars($booking['guest_email']) ?></p>
                                                        <p>Guest Phone: <?= htmlspecialchars($booking['guest_phone']) ?></p>
                                                        <p>Booking Time: <?= htmlspecialchars($booking['created_at']) ?></p>
                                                        <!-- Add more booking details as necessary -->
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p>
                <h3>You have no bookings.</h3>
                </p>
            <?php endif; ?>
        </div>
        <?php include "footer.inc.php"; ?>
        <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
        <script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>
        <script src="js/main.js"></script>
    </main>
</body>

</html>