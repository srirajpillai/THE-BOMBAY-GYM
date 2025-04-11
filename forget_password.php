<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $new_password = password_hash("Temp1234", PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password='$new_password' WHERE email='$email'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Password reset successful! Use 'Temp1234' to login.";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>