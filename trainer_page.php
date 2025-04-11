<?php
session_start();
require_once 'db.php';

// Check if trainer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: login page modified.html");
    exit();
}

// Fetch trainer details from existing trainers table
$trainer_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM trainers WHERE id = ?");
$stmt->bind_param("i", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer_data = $result->fetch_assoc();
$stmt->close();

// Check if trainer exists
if (!$trainer_data) {
    session_destroy();
    header("Location: login page modified.html");
    exit();
}

// Set trainer details
$name = $trainer_data['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - The Bombay Gym</title>
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
        }

        .navbar {
            background: rgba(0, 0, 0, 0.9);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-buttons {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-buttons .btn {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-buttons .btn:hover {
            background: #fffc00;
            color: #000;
        }

        main {
            margin-top: 80px;
            padding: 20px;
        }

        .card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h2 {
            color: #fffc00;
            margin-bottom: 20px;
        }

        .btn {
            background: #fffc00;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #fff;
            transform: translateY(-2px);
        }

        .logout-btn {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .logout-btn:hover {
            color: #fffc00;
        }

        .trainer-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-card h4 {
            color: #fffc00;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #ffffff;
        }

        .specialization-badge {
            display: inline-block;
            background: #fffc00;
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #fffc00;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            color: #fff;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #fffc00;
        }

        .current-profile-image {
            display: block;
            max-width: 200px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert.success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #fff;
        }

        .alert.error {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .bookings-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .booking-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .booking-header {
            background: #fffc00;
            color: #000;
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }

        .user-details {
            padding: 15px;
        }

        .user-details p {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-details i {
            color: #fffc00;
            width: 20px;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-badge.booked {
            background: #28a745;
            color: #fff;
        }

        .status-badge.cancelled {
            background: #dc3545;
            color: #fff;
        }

        .no-booking {
            padding: 15px;
            text-align: center;
            color: #999;
            font-style: italic;
        }

        .user-details strong {
            color: #fffc00;
            margin-right: 5px;
        }

        /* Availability Section Styles */
        #availability .card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        #availability h3 {
            color: #fffc00;
            font-size: 1.5rem;
            margin-bottom: 25px;
            border-bottom: 2px solid rgba(255, 252, 0, 0.2);
            padding-bottom: 10px;
        }

        .duration-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .radio-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .radio-label:hover {
            background: rgba(255, 252, 0, 0.1);
            border-color: rgba(255, 252, 0, 0.3);
        }

        .radio-label input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #fffc00;
        }

        .slot-selection {
            display: grid;
            gap: 25px;
            margin-bottom: 30px;
        }

        .date-selection input[type="date"],
        .time-selection select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .date-selection input[type="date"]:focus,
        .time-selection select:focus {
            border-color: #fffc00;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 252, 0, 0.2);
        }

        .time-selection select option {
            background: #1a1a1a;
            color: #fff;
        }

        .btn-cancel {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-cancel:hover {
            background: #dc3545;
            color: #fff;
        }

        /* Current Availability Styles */
        .booking-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .booking-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .booking-item p {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .booking-item strong {
            color: #fffc00;
            min-width: 100px;
        }

        .slot-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .slot-status.available {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .slot-status.booked {
            background: rgba(255, 252, 0, 0.2);
            color: #fffc00;
            border: 1px solid rgba(255, 252, 0, 0.3);
        }

        .slot-status.cancelled {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
    </style>
    <script src="trainer-slots.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1>Trainer Dashboard</h1>
            <div class="nav-buttons">
                <a href="manage_trainer_profile.php" class="btn">
                    <i class="fas fa-user-edit"></i> Manage Profile
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <section id="welcome">
                <div class="container">
                    <h2>Welcome, <?php echo htmlspecialchars($name); ?>!</h2>
                    <div class="trainer-stats">
                        <div class="stat-card">
                            <h4>Today's Booked Users</h4>
                            <div class="number">
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT COUNT(DISTINCT gs.id) as today_bookings
                                    FROM trainer_slots ts
                                    INNER JOIN gym_slots gs ON (
                                        ts.available_date = gs.booking_date 
                                        AND ts.slot_time = gs.slot_time
                                        AND gs.status = 'Booked'
                                    )
                                    WHERE ts.trainer_id = ? 
                                    AND ts.available_date = CURDATE()
                                    AND ts.status = 'Available'
                                ");
                                $stmt->bind_param("i", $trainer_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                echo $result->fetch_assoc()['today_bookings'];
                                ?>
                            </div>
                        </div>
                        <div class="stat-card">
                            <h4>Total Active Bookings</h4>
                            <div class="number">
                                <?php
                                $stmt = $conn->prepare("
                                    SELECT COUNT(DISTINCT gs.id) as total_bookings
                                    FROM trainer_slots ts
                                    INNER JOIN gym_slots gs ON (
                                        ts.available_date = gs.booking_date 
                                        AND ts.slot_time = gs.slot_time
                                        AND gs.status = 'Booked'
                                    )
                                    WHERE ts.trainer_id = ? 
                                    AND ts.available_date >= CURDATE()
                                    AND ts.status = 'Available'
                                ");
                                $stmt->bind_param("i", $trainer_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                echo $result->fetch_assoc()['total_bookings'];
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="current-bookings" class="card">
                <h2>Users Booked in Your Slots</h2>
                <?php
                // Modified query to show users who booked during trainer's available slots
                $booking_stmt = $conn->prepare("
                    SELECT 
                        ts.available_date,
                        ts.slot_time,
                        gs.status as booking_status,
                        u.username as user_name,
                        u.phone as user_phone,
                        u.email as user_email
                    FROM trainer_slots ts
                    LEFT JOIN gym_slots gs ON (
                        ts.available_date = gs.booking_date 
                        AND ts.slot_time = gs.slot_time
                        AND gs.status = 'Booked'
                    )
                    LEFT JOIN users u ON gs.user_id = u.id
                    WHERE ts.trainer_id = ? 
                    AND ts.available_date >= CURDATE()
                    AND ts.status = 'Available'
                    ORDER BY ts.available_date ASC, ts.slot_time ASC");
                
                $booking_stmt->bind_param("i", $trainer_id);
                $booking_stmt->execute();
                $bookings = $booking_stmt->get_result();

                if ($bookings->num_rows > 0):
                ?>
                    <div class="bookings-wrapper">
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <div class="booking-item">
                                <div class="booking-header">
                                    <span class="booking-date">
                                        <?php echo date('d M Y', strtotime($booking['available_date'])); ?>
                                    </span>
                                    <span class="booking-time">
                                        <?php echo htmlspecialchars($booking['slot_time']); ?>
                                    </span>
                                </div>
                                <?php if ($booking['user_name'] && $booking['booking_status'] === 'Booked'): ?>
                                    <div class="user-details">
                                        <p><i class="fas fa-user"></i> <strong>Member:</strong> 
                                            <?php echo htmlspecialchars($booking['user_name']); ?>
                                        </p>
                                        <p><i class="fas fa-phone"></i> <strong>Contact:</strong> 
                                            <?php echo htmlspecialchars($booking['user_phone']); ?>
                                        </p>
                                        <p><i class="fas fa-envelope"></i> <strong>Email:</strong> 
                                            <?php echo htmlspecialchars($booking['user_email']); ?>
                                        </p>
                                        <p><i class="fas fa-info-circle"></i> <strong>Status:</strong> 
                                            <span class="status-badge booked">Booked</span>
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <p class="no-booking">No booking for this slot</p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No available slots found.</p>
                <?php endif; ?>
            </section>

            <section id="availability">
                <div class="container">
                    <h2>Manage Your Availability</h2>
                    
                    <?php if (isset($_SESSION['slot_success'])): ?>
                        <div class="alert success">
                            <?php 
                                echo $_SESSION['slot_success'];
                                unset($_SESSION['slot_success']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['slot_error'])): ?>
                        <div class="alert error">
                            <?php 
                                echo $_SESSION['slot_error'];
                                unset($_SESSION['slot_error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <h3>Set Your Available Time Slots</h3>
                        <form action="set_trainer_slots.php" method="POST">
                            <div class="slot-selection">
                                <div class="booking-type">
                                    <label>Availability Duration:</label>
                                    <div class="duration-options">
                                        <label class="radio-label">
                                            <input type="radio" name="slot_type" value="single" checked>
                                            Single Day
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="slot_type" value="week">
                                            Weekly
                                        </label>
                                        <label class="radio-label">
                                            <input type="radio" name="slot_type" value="month">
                                            Monthly
                                        </label>
                                    </div>
                                </div>

                                <div class="date-selection">
                                    <label for="available_date">Start Date:</label>
                                    <input type="date" id="available_date" name="available_date" 
                                           min="<?php echo date('Y-m-d'); ?>" 
                                           required>
                                </div>

                                <div class="time-selection">
                                    <label for="slot_time">Select Time Slot:</label>
                                    <select id="slot_time" name="slot_time" required>
                                        <option value="">Choose a slot</option>
                                        <option value="06:00 AM - 08:00 AM">6:00 AM - 8:00 AM</option>
                                        <option value="08:00 AM - 10:00 AM">8:00 AM - 10:00 AM</option>
                                        <option value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</option>
                                        <option value="04:00 PM - 06:00 PM">4:00 PM - 6:00 PM</option>
                                        <option value="06:00 PM - 08:00 PM">6:00 PM - 8:00 PM</option>
                                        <option value="08:00 PM - 10:00 PM">8:00 PM - 10:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn">Set Availability</button>
                        </form>
                    </div>

                    <div class="card">
                        <h3>Your Current Availability</h3>
                        <?php
                        $slots_stmt = $conn->prepare("SELECT * FROM trainer_slots 
                                                    WHERE trainer_id = ? 
                                                    AND status != 'Cancelled'
                                                    AND end_date >= CURDATE()
                                                    ORDER BY available_date ASC");
                        $slots_stmt->bind_param("i", $trainer_id);
                        $slots_stmt->execute();
                        $slots = $slots_stmt->get_result();
                        
                        if ($slots->num_rows > 0):
                            while ($slot = $slots->fetch_assoc()):
                        ?>
                            <div class="booking-item">
                                <p><strong>Time Slot:</strong> <?php echo htmlspecialchars($slot['slot_time']); ?></p>
                                <p><strong>Start Date:</strong> <?php echo date('d M Y', strtotime($slot['available_date'])); ?></p>
                                <p><strong>End Date:</strong> <?php echo date('d M Y', strtotime($slot['end_date'])); ?></p>
                                <p><strong>Status:</strong> <?php echo $slot['status']; ?></p>
                                <?php if ($slot['status'] === 'Available'): ?>
                                    <form action="cancel_trainer_slot.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
                                        <button type="submit" class="btn-cancel">Cancel Slot</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <p>No active slots found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>