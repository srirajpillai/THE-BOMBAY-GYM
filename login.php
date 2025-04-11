<?php
ob_start();
session_start();
require_once "db.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
    $password = trim($_POST['password']);
    $role = filter_var(isset($_POST['role']) ? $_POST['role'] : '', FILTER_SANITIZE_STRING);

    // Input validation
    if (empty($username) || empty($password) || empty($role)) {
        $errors[] = "All fields are required.";
    }

    if (!in_array($role, ['User', 'Trainer'])) {
        $errors[] = "Invalid role selected.";
    }

    if (empty($errors)) {
        try {
            $table = ($role === "User") ? "users" : "trainers";
            
            $stmt = $conn->prepare("SELECT * FROM $table WHERE username = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $role;
                    
                    $redirect = ($role === "User") ? "user_page.php" : "trainer_page.php"; // Changed from trainer_login.php to trainer_page.php
                    header("Location: $redirect");
                    exit();
                } else {
                    echo "<script>alert('Wrong password!'); window.location.href='login page modified.html';</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Username not found!'); window.location.href='login page modified.html';</script>";
                exit();
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            $errors[] = "An error occurred. Please try again later.";
            error_log("Login error: " . $e->getMessage());
        }
    }

    if (!empty($errors)) {
        header("Location: login page modified.html?error=" . urlencode(json_encode($errors)));
        exit();
    }
}
ob_end_flush();
?>
