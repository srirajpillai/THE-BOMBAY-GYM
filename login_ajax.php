<?php
include "db.php";
session_start();
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['username']);
$password = trim($data['password']);
$role = trim($data['role']);

$response = [];

if (!$role || !$username || !$password) {
    $response['success'] = false;
    $response['message'] = "All fields are required.";
    echo json_encode($response); exit;
}

$table = ($role === "Trainer") ? "trainers" : "users";
$query = $conn->prepare("SELECT * FROM $table WHERE username = ?");
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION[$role === "Trainer" ? 'trainer_id' : 'user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $response['success'] = true;
        $response['redirect'] = $role === "Trainer" ? "trainer_page.php" : "user_page.php";
    } else {
        $response['success'] = false;
        $response['message'] = "Incorrect password.";
    }
} else {
    $response['success'] = false;
    $response['message'] = "User not found.";
}

echo json_encode($response);
?>
