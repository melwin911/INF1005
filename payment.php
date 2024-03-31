<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_51I3z2RGZozntPACPaBmQYQLQaNn5C8EgP92kHeSH3oBsEYaVV1hUxDOZ8YZAHSevP1mooArI45a9DVo9XindVKwt00kf84rT4r');

session_start();

// Check if the user is not logged in
if (!isset($_SESSION['loggedin'])) {
    // Redirect to index.php if the user is not logged in
    header('Location: rooms.php');
    exit;
}

$headSection = "member_head.inc.php";
$navBar = "member_navbar.inc.php";

$totalPrice = $_SESSION['totalAmount'] ?? 0; // Default to 0 if not set
$totalPrice = (float)$totalPrice; // Cast to float to ensure correct data type for number_format()

?>
<!DOCTYPE HTML>
<html lang="en">

<head>
    <?php include "head.inc.php"; ?>
    <title>Payment - Hotel Booking</title>
</head>

<body>
    <?php
    include "header.inc.php";
    include $navBar;
    include $headSection;
    renderNavbar('Booking Cart');
    ?>

<section class="section">
        <div class="container">
            <h2>Payment Details</h2>
            <p>Total amount to be charged: $<?php echo number_format($totalPrice, 2); ?></p>
            <form id="payment-form" action="process_payment.php" method="post">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($totalPrice); ?>">
                <!-- Billing Details -->
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Jane Doe" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="jane.doe@example.com" required>
                </div>

                <div class="form-group">
                    <label for="address_line1">Address Line 1:</label>
                    <input type="text" id="address_line1" name="address_line1" class="form-control" placeholder="123 Main St" required>
                </div>

                <div class="form-group">
                    <label for="address_line2">Address Line 2:</label>
                    <input type="text" id="address_line2" name="address_line2" class="form-control" placeholder="Apt 4B">
                </div>

                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" class="form-control" placeholder="Anytown" required>
                </div>

                <div class="form-group">
                    <label for="state">State/Province:</label>
                    <input type="text" id="state" name="state" class="form-control" placeholder="State" required>
                </div>

                <div class="form-group">
                    <label for="zip">Postal Code:</label>
                    <input type="text" id="zip" name="zip" class="form-control" placeholder="12345" required>
                </div>

                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" id="country" name="country" class="form-control" placeholder="Country" required>
                </div>

                <!-- Credit or debit card element -->
                <div class="form-group">
                    <label for="card-element">Credit or debit card</label>
                    <div id="card-element">
                        <!-- A Stripe Element will be inserted here. -->
                    </div>
                </div>

                <!-- Error Element -->
                <div id="card-errors" role="alert"></div>

                <button type="submit" class="btn btn-primary">Submit Payment</button>
            </form>
        </div>
    </section>

    <?php include "footer.inc.php"; ?>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        var stripe = Stripe('pk_test_51I3z2RGZozntPACPhMi6lw9NJv0FkRglcMc7zOsSinytCCsHeDKBQ0zjzmtDrwheZlyJQPdKObnAXWnqtT3Z076t00mHvFFIhz');
        var elements = stripe.elements();

        var card = elements.create('card');
        card.mount('#card-element');

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // Inform the user if there was an error.
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // Send the token to your server.
                    stripeTokenHandler(result.token);
                }
            });
        });

        function stripeTokenHandler(token) {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('payment-form');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);

            // Submit the form
            form.submit();
        }
    </script>
</body>

</html>