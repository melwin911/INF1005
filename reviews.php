<?php
session_start();

// Check if room_type_id is set in the query string
if (!isset($_GET['room_type_id'])) {
    header('Location: rooms.php');
    exit;
}

$headSection = "nonmember_head.inc.php"; // Default to non-member head
$navBar = "navbar.inc.php"; // Default to non-member navbar

// Check if the authentication cookie exists
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Include the member head section if the auth cookie exists
    $headSection = "member_head.inc.php";
    $navBar = "member_navbar.inc.php";
}

$roomTypeId = intval($_GET['room_type_id']); // Get the room type ID and ensure it's an integer

// Database connection
$config = parse_ini_file('/var/www/private/db-config.ini');
$conn = new mysqli(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch reviews for the given room type ID
$sql = "SELECT reviews.*, hotel_members.fname AS member_first_name, hotel_members.lname AS member_last_name FROM reviews 
JOIN hotel_members ON reviews.member_id = hotel_members.member_id 
WHERE room_type_id = ? 
ORDER BY reviews.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $roomTypeId);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
} else {
    $noReviewsMessage = "There are no reviews for this room yet.";
}

$roomDetails = null;
$roomName = "Room";

// Fetch the room details
$roomSql = "SELECT room_type FROM room_types WHERE room_type_id = ?";
$roomStmt = $conn->prepare($roomSql);
$roomStmt->bind_param("i", $roomTypeId);
$roomStmt->execute();
$roomResult = $roomStmt->get_result();

if ($roomResult->num_rows > 0) {
    $roomDetails = $roomResult->fetch_assoc();
    $roomName = $roomDetails['room_type'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE HTML>
<html lang="en">

<?php
include "head.inc.php";
?>

<body>

    <?php
    include "header.inc.php";
    include $navBar;
    include $headSection;
    renderNavbar('Rooms');
    ?>

    <div class="container mt-5">
        <div class="back-button">
            <button onclick="goBack()" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>
        <h1 class="mb-4 text-center">Room Reviews for <b><?php echo htmlspecialchars($roomName); ?></b></h1>

        <!-- Dropdown filter for sorting reviews -->
        <div class="text-center mb-4">
            <label for="reviewFilter" class="mr-2">Sort by:</label>
            <select id="reviewFilter" class="form-control w-25 mx-auto">
                <option value="" selected disabled>Select sort option</option>
                <option value="highestRating">Highest Rating</option>
                <option value="mostRecent">Most Recent</option>
            </select>
        </div>

        <div id="reviewsContainer" class="row">
            <?php if (empty($reviews)) : ?>
                <div class="alert alert-info" role="alert">
                    <?php echo $noReviewsMessage; ?>
                </div>
            <?php else : ?>
                <div class="row">
                    <?php foreach ($reviews as $review) : ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Rating: <?php echo str_repeat('★', intval($review['rating'])); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">Posted on: <?php echo date('F j, Y', strtotime(htmlspecialchars($review['created_at']))); ?></small>
                                    <br>
                                    <small class="text-muted">By: <?php echo htmlspecialchars($review['member_first_name'] . " " . $review['member_last_name']); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Start of footer -->
    <?php
    include "footer.inc.php";
    ?>
    <!-- End of footer -->
    <script src="https://cdn.botpress.cloud/webchat/v1/inject.js"></script>
    <script src="https://mediafiles.botpress.cloud/5839c45b-a068-4754-9a6c-6e58dee3de97/webchat/config.js" defer></script>

    <script src="js/bootstrap-datepicker.js"></script>
    <script src="js/jquery.timepicker.min.js"></script>
    <script src="js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reviewsContainer = document.getElementById('reviewsContainer');
            const reviewFilter = document.getElementById('reviewFilter');

            function sortReviews(value) {
                let reviews = Array.from(reviewsContainer.getElementsByClassName('col-md-4'));
                let sortedReviews;

                if (value === 'highestRating') {
                    sortedReviews = reviews.sort(function(a, b) {
                        let ratingA = a.querySelector('.card-title').textContent.trim().length; // count the stars (★)
                        let ratingB = b.querySelector('.card-title').textContent.trim().length; // count the stars (★)
                        return ratingB - ratingA;
                    });
                } else { // Defaults to 'mostRecent'
                    sortedReviews = reviews.sort(function(a, b) {
                        let dateA = new Date(a.querySelector('.text-muted').textContent.trim().substring(10));
                        let dateB = new Date(b.querySelector('.text-muted').textContent.trim().substring(10));
                        return dateB - dateA;
                    });
                }

                // Clear the current reviews and append sorted ones
                reviewsContainer.innerHTML = '';
                sortedReviews.forEach(function(review) {
                    reviewsContainer.appendChild(review);
                });
            }

            // Initially sort reviews by 'mostRecent' when the page loads
            sortReviews('mostRecent');

            reviewFilter.addEventListener('change', function() {
                sortReviews(this.value);
            });
        });
    </script>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>

</html>