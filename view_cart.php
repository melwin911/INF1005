<?php
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

    $cartItems = [];
    $stmt = $conn->prepare("SELECT ci.*, rt.room_type, rt.image_path, rt.price_per_night FROM cart_items ci JOIN carts c ON ci.cart_id = c.cart_id JOIN room_types rt ON ci.room_type_id = rt.room_type_id WHERE c.member_id = ?");
    $stmt->bind_param("i", $memberId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cartItems[] = $row;
        }
    } else {
        echo "Your cart is empty.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <?php include "head.inc.php";?>
</head>
<body>
    <?php
    include "header.inc.php";
    include $navBar;
    include $headSection;
    renderNavbar('Booking Cart');
    ?>
    <main>
    <div class="container mt-4">
    <h2 class="heading" data-aos="fade-up">Booking Cart</h2>
    <?php if (count($cartItems) > 0): ?>
    <form action="cart_actions.php" method="post">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Select</th>
                <th>Room Image</th>
                <th>Room Type</th>
                <th>Total Price</th>
                <th>Check-in Date</th>
                <th>Check-out Date</th>
                <th>Number of Rooms</th>
                <th>Number of Guests</th>
                <th>Guest Name</th>
                <th>Guest Email</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalPriceForAllItems = 0;
            foreach ($cartItems as $item): 
                $checkInDate = new DateTime($item['check_in_date']);
                $checkOutDate = new DateTime($item['check_out_date']);
                $numNights = $checkOutDate->diff($checkInDate)->days;
                $totalPrice = $item['price_per_night'] * $numNights * $item['num_rooms'];
                $totalPriceForAllItems += $totalPrice; // Add the item's total price to the cumulative total
                ?>
            <tr>
                <td><input type="checkbox" name="selected_rooms[]" value="<?= htmlspecialchars($item['item_id']) ?>" data-price="<?= $totalPrice ?>" onchange="updateTotal()"></td>
                <td><img src="images/<?php echo htmlspecialchars($item['image_path'])?>" alt="Room image" class="img-fluid mb-3"></td>
                <td><?= htmlspecialchars($item['room_type']) ?></td>
                <td>$<?=number_format($totalPrice, 2)?></td>
                <td><?= htmlspecialchars($item['check_in_date']) ?></td>
                <td><?= htmlspecialchars($item['check_out_date']) ?></td>
                <td><?= htmlspecialchars($item['num_rooms']) ?></td>
                <td><?= htmlspecialchars($item['num_guests']) ?></td>
                <td><?= htmlspecialchars($item['guest_name']) ?></td>
                <td><?= htmlspecialchars($item['guest_email']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
        <div class="row justify-content-end">
        <div class="col-auto">
            <h4>
        <div id="dynamicTotalPrice" class="text-right mt-3">Total: $0.00</div>
        </h4>
        </div>
    </div>
    <br>
    <button type="submit" name="action" value="checkout" class="btn btn-primary">Checkout</button>
    <button type="submit" name="action" value="delete" class="btn btn-danger">Delete</button>
    <button type="submit" name="action" value="edit" class="btn btn-secondary">Edit</button>
    </form>
    <br>
    <?php else: ?>
    <p>Your cart is empty.</p>
    <?php endif; ?>
    </div>
    <?php include "footer.inc.php"; ?>
    <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
<script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>

<script src="js/bootstrap-datepicker.js"></script> 
    <script src="js/jquery.timepicker.min.js"></script> 
    <script src="js/main.js"></script>
    <script>
function updateTotal() {
    let total = 0;
    document.querySelectorAll('input[name="selected_rooms[]"]:checked').forEach((item) => {
        total += parseFloat(item.getAttribute('data-price'));
    });
    document.getElementById('dynamicTotalPrice').textContent = 'Total: $' + total.toFixed(2);
    // Send the total amount to update_total.php
    fetch('update_total.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}, 
        body: 'totalAmount=' + total.toFixed(2)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            console.log('Total amount updated successfully');
        } else {
            console.error('Failed to update total amount');
        }
    })
    .catch(error => console.error('Error:', error));
    document.addEventListener('DOMContentLoaded', updateTotal);
}
</script>
<script>
function checkButtonsState() {
    const checkedBoxes = document.querySelectorAll('input[name="selected_rooms[]"]:checked');
    const editButton = document.querySelector('button[value="edit"]');
    const checkoutButton = document.querySelector('button[value="checkout"]');
    const deleteButton = document.querySelector('button[value="delete"]');

    // Enable the "Edit" button only if exactly one checkbox is selected
    editButton.disabled = !(checkedBoxes.length === 1);

    // Determine if at least one checkbox is checked
    const isAtLeastOneChecked = checkedBoxes.length > 0;

    // Enable or disable the "Checkout" and "Delete" buttons based on if at least one checkbox is checked
    checkoutButton.disabled = !isAtLeastOneChecked;
    deleteButton.disabled = !isAtLeastOneChecked;
}

// Attach the change event listener to each checkbox
document.querySelectorAll('input[name="selected_rooms[]"]').forEach((checkbox) => {
    checkbox.addEventListener('change', checkButtonsState);
});

// Check the initial state of buttons on page load
document.addEventListener('DOMContentLoaded', checkButtonsState);
</script>
    </main>
</body>
</html>