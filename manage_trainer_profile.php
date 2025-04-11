<?php
session_start();
require_once 'db.php';

// Check if trainer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: login page modified.html");
    exit();
}

// Fetch trainer details
$trainer_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM trainers WHERE id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$trainer_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Set trainer details
$name = $trainer_data['username'];
$email = $trainer_data['email'];
$phone = $trainer_data['phone'];
$specialization = $trainer_data['specialization'];
$experience = $trainer_data['experience'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile - The Bombay Gym</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier', sans-serif;
        }

        body {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.7)),
                        url('samuel-girven-2e4lbLTqPIo-unsplash.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #ffffff;
            line-height: 1.6;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.9);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        main {
            padding-top: 100px;
        }

        .profile-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h1, h2 {
            color: #fffc00;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #fffc00;
            font-size: 1.1rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #fffc00;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #fffc00;
            color: #000;
        }

        .btn-secondary {
            background: transparent;
            color: #fff;
            border: 2px solid #fff;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .alert.success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
        }

        .nav-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #fffc00;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-links">
                <a href="trainer_page.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="profile-card">
                <h1>Manage Your Profile</h1>

                <?php if (isset($_SESSION['profile_success'])): ?>
                    <div class="alert success">
                        <?php 
                            echo $_SESSION['profile_success'];
                            unset($_SESSION['profile_success']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="update_trainer_profile.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" 
                               value="<?php echo htmlspecialchars($specialization); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="experience">Years of Experience</label>
                        <input type="number" id="experience" name="experience" 
                               value="<?php echo htmlspecialchars($experience); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="bio">About Me</label>
                        <textarea id="bio" name="bio" rows="4" 
                                  placeholder="Tell us about your training philosophy and experience..."><?php echo htmlspecialchars($trainer_data['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="btn-container">
                        <a href="trainer_page.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>