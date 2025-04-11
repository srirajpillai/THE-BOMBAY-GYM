<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $slot_time = $_POST['slot_time'];
    $booking_date = $_POST['booking_date'];

    $stmt = $conn->prepare("UPDATE gym_slots SET status = 'Cancelled' 
                           WHERE user_id = ? AND booking_date = ? AND slot_time = ?");
    $stmt->bind_param("iss", $user_id, $booking_date, $slot_time);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Booking cancelled successfully!";
    } else {
        $_SESSION['error'] = "Error cancelling booking. Please try again.";
    }
    
    header("Location: user_page.php#slot-booking");
    exit();
}