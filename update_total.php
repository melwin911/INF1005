<?php
session_start();

if(isset($_POST['totalAmount'])) {
    $_SESSION['totalAmount'] = $_POST['totalAmount'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Total amount not provided.']);
}
