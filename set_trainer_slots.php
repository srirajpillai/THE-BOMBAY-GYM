<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: login page modified.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainer_id = $_SESSION['user_id'];
    $slot_type = $_POST['slot_type'];
    $available_date = $_POST['available_date'];
    $slot_time = $_POST['slot_time'];

    // Validate inputs
    if (empty($slot_type) || empty($available_date) || empty($slot_time)) {
        $_SESSION['slot_error'] = "All fields are required";
        header("Location: trainer_page.php#availability");
        exit();
    }

    // Calculate end date based on slot type
    $end_date = $available_date;
    if ($slot_type === 'week') {
        $end_date = date('Y-m-d', strtotime($available_date . ' +6 days'));
    } elseif ($slot_type === 'month') {
        $end_date = date('Y-m-d', strtotime($available_date . ' +1 month -1 day'));
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Check if slot already exists
        $check_stmt = $conn->prepare("
            SELECT id FROM trainer_slots 
            WHERE trainer_id = ? 
            AND available_date = ? 
            AND slot_time = ?
            AND status != 'Cancelled'
        ");
        $check_stmt->bind_param("iss", $trainer_id, $available_date, $slot_time);
        $check_stmt->execute();
        $exists = $check_stmt->get_result()->num_rows > 0;

        if (!$exists) {
            // Insert new slot
            $insert_stmt = $conn->prepare("
                INSERT INTO trainer_slots 
                (trainer_id, slot_time, available_date, end_date, slot_type, status) 
                VALUES (?, ?, ?, ?, ?, 'Available')
            ");
            $insert_stmt->bind_param("issss", 
                $trainer_id, 
                $slot_time, 
                $available_date, 
                $end_date, 
                $slot_type
            );

            if ($insert_stmt->execute()) {
                $conn->commit();
                $_SESSION['slot_success'] = "Availability slot set successfully!";
            } else {
                throw new Exception("Error inserting slot");
            }
        } else {
            throw new Exception("This time slot is already set for this date");
        }

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['slot_error'] = $e->getMessage();
    }

    header("Location: trainer_page.php#availability");
    exit();
}
?>