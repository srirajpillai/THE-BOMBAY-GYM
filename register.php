<?php
session_start();
include "db.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role']; // User or Trainer

    // Validate input fields
    if (empty($name) || empty($email) || empty($phone) || empty($username) || empty($password)) {
        $errors[] = "All fields are required.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate phone number (10 digits)
    if (!preg_match("/^\d{10}$/", $phone)) {
        $errors[] = "Invalid phone number. Must be 10 digits.";
    }

    // Validate password strength
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        echo "<script>alert('Username already exists!'); window.location.href='Register Page.html';</script>";
        exit();
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $table = ($role == 'Trainer') ? 'trainers' : 'users';

        $query = "INSERT INTO $table (name, email, phone, username, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $name, $email, $phone, $username, $hashedPassword);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Please login.'); window.location.href='login page modified.html';</script>";
        } else {
            echo "<script>alert('Registration failed! Please try again.'); window.location.href='Register Page.html';</script>";
        }
    }
    $stmt->close();
}
?>
