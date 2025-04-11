<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $booking_type = $_POST['booking_type'];
    $start_date = new DateTime($_POST['start_date']);
    $slot_time = $_POST['slot_time'];
    $weekdays = isset($_POST['weekdays']) ? $_POST['weekdays'] : [];

    // Calculate end date based on booking type
    $end_date = clone $start_date;
    if ($booking_type === 'week') {
        $end_date->modify('+7 days');
    } elseif ($booking_type === 'month') {
        $end_date->modify('+1 month');
    }

    // Insert booking with duration
    $stmt = $conn->prepare("INSERT INTO gym_slots (user_id, slot_time, booking_date, end_date, booking_type, weekdays) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    
    $booking_date = $start_date->format('Y-m-d');
    $end_date = $end_date->format('Y-m-d');
    $weekdays_str = implode(',', $weekdays);
    
    $stmt->bind_param("isssss", $user_id, $slot_time, $booking_date, $end_date, $booking_type, $weekdays_str);
    
    if ($stmt->execute()) {
        $_SESSION['slot_success'] = "Workout schedule booked successfully!";
    } else {
        $_SESSION['slot_error'] = "Error booking schedule. Please try again.";
    }
    
    header("Location: user_page.php#slot-booking");
    exit();
}
?>