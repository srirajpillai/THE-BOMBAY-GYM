<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: login page modified.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainer_id = $_SESSION['user_id'];
    
    // Update basic info in trainers table
    $stmt = $conn->prepare("UPDATE trainers SET username = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("sssi", $_POST['name'], $_POST['email'], $_POST['phone'], $trainer_id);
    $stmt->execute();

    // Handle profile image upload
    $profile_image_path = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_dir = 'uploads/trainer_profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $file_name = $trainer_id . '_' . time() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            $profile_image_path = $target_path;
        }
    }

    // Check if trainer details exist
    $check_stmt = $conn->prepare("SELECT id FROM trainer_details WHERE trainer_id = ?");
    $check_stmt->bind_param("i", $trainer_id);
    $check_stmt->execute();
    $exists = $check_stmt->get_result()->num_rows > 0;

    if ($exists) {
        // Update existing details
        $update_stmt = $conn->prepare("UPDATE trainer_details SET 
            specialization = ?, 
            experience = ?, 
            certifications = ?, 
            bio = ?" . 
            ($profile_image_path ? ", profile_image = ?" : "") . 
            " WHERE trainer_id = ?");
        
        if ($profile_image_path) {
            $update_stmt->bind_param("sissssi", 
                $_POST['specialization'],
                $_POST['experience'],
                $_POST['certifications'],
                $_POST['bio'],
                $profile_image_path,
                $trainer_id
            );
        } else {
            $update_stmt->bind_param("sissi", 
                $_POST['specialization'],
                $_POST['experience'],
                $_POST['certifications'],
                $_POST['bio'],
                $trainer_id
            );
        }
        $update_stmt->execute();
    } else {
        // Insert new details
        $insert_stmt = $conn->prepare("INSERT INTO trainer_details 
            (trainer_id, specialization, experience, certifications, bio, profile_image) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("isisss", 
            $trainer_id,
            $_POST['specialization'],
            $_POST['experience'],
            $_POST['certifications'],
            $_POST['bio'],
            $profile_image_path
        );
        $insert_stmt->execute();
    }

    $_SESSION['profile_success'] = "Profile updated successfully!";
    header("Location: trainer_page.php#profile");
    exit();
}
?>