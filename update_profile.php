<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $errors = [];

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone)) {
        $errors[] = "All fields are required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (!preg_match("/^\d{10}$/", $phone)) {
        $errors[] = "Phone number must be 10 digits";
    }

    if (empty($errors)) {
        // Check if email already exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        } else {
            // Update profile
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Profile updated successfully!";
                header("Location: user_page.php");
                exit();
            } else {
                $errors[] = "Error updating profile";
            }
            $update_stmt->close();
        }
        $stmt->close();
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: user_page.php");
        exit();
    }
}
?>