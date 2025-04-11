<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user and membership details
$user_id = $_SESSION['user_id'];

// First fetch basic user details
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Check if user exists
if (!$user_data) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Set basic user details
$name = $user_data['name'];
$email = $user_data['email'];
$phone = $user_data['phone'];

// Update the membership query to properly fetch active memberships
$stmt = $conn->prepare("SELECT m.plan_type, m.start_date, m.end_date, m.status 
                       FROM memberships m 
                       WHERE m.user_id = ? 
                       AND m.status = 'Active'
                       ORDER BY m.start_date DESC 
                       LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$membership_data = $result->fetch_assoc();
$stmt->close();

// Set membership details with proper error checking
$has_active_plan = false;
if ($membership_data) {
    $plan_type = $membership_data['plan_type'];
    $start_date = $membership_data['start_date'];
    $end_date = $membership_data['end_date'];
    $has_active_plan = true;
} else {
    $plan_type = 'No Active Plan';
    $start_date = '';
    $end_date = '';
}

// Add this before closing the PHP tag
error_log("Debug - User ID: $user_id, Plan Type: $plan_type, Has Active Plan: " . ($has_active_plan ? 'Yes' : 'No'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

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

/* Navbar Styling */
.navbar {
    background: rgba(0, 0, 0, 0.9);
    padding: 1rem 0;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
}

.navbar .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 2rem;
    margin-top: 0;
}

.navbar .logo h1 {
    color: #fffc00;
    font-size: 1.5rem;
    margin: 0;
    padding: 0;
}

.navbar .logo h1::after {
    display: none;
}

.nav-links {
    display: flex;
    gap: 2rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-links a {
    color: #fff;
    text-decoration: none;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-links a:hover {
    color: #fffc00;
    background: rgba(255, 252, 0, 0.1);
}

.nav-links .logout-btn {
    background: #fffc00;
    color: #000;
}

.nav-links .logout-btn:hover {
    background: #fff;
    color: #000;
}

/* Container Styling */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px;
    margin-top: 100px;
}

/* Section Styling */
section {
    background: linear-gradient(145deg, 
        rgba(255, 255, 255, 0.1) 0%,
        rgba(255, 255, 255, 0.05) 100%);
    border-radius: 20px;
    padding: 40px;
    margin: 30px 0;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

h1, h2 {
    color: #fffc00;
    margin-bottom: 30px;
    text-align: center;
    font-size: 2.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    padding-bottom: 15px;
}

h1::after, h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 4px;
    background: #fffc00;
    border-radius: 2px;
}

/* Card Styling */
.card {
    background: linear-gradient(145deg,
        rgba(255, 255, 255, 0.1) 0%,
        rgba(255, 255, 255, 0.05) 100%);
    border-radius: 15px;
    padding: 30px;
    margin: 20px 0;
    transition: transform 0.4s ease;
    border: 1px solid rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: #fffc00;
    transition: all 0.4s ease;
    z-index: 1;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.card h3 {
    color: #fffc00;
    font-size: 1.8rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.card p {
    color: #ffffff;
    font-size: 1.1rem;
    line-height: 1.6;
}

/* Form Styling */
form {
    display: grid;
    gap: 25px;
    max-width: 600px;
    margin: 0 auto;
}

label {
    color: #fffc00;
    font-size: 1.1rem;
    font-weight: 500;
}

input {
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.1);
    padding: 15px 20px;
    border-radius: 10px;
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s ease;
}

input:focus {
    outline: none;
    border-color: #fffc00;
    background: rgba(255, 255, 255, 0.15);
}

button {
    background: #fffc00;
    color: #000000;
    padding: 15px 30px;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(255, 252, 0, 0.2);
    background: #ffffff;
}

/* Add this CSS in the <style> section after the existing button styling */
.btn {
    display: inline-block;
    background: #fffc00;
    color: #000000;
    padding: 15px 30px;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-decoration: none;
    margin-top: 20px;
    position: relative;
    z-index: 2;
}

.btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(255, 252, 0, 0.2);
    background: #ffffff;
}

/* Footer Styling */
footer {
    background: linear-gradient(to right, #000000, #1a1a1a);
    padding: 30px 0;
    text-align: center;
    margin-top: 60px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar .container {
        flex-direction: column;
        padding: 1rem;
    }

    .nav-links {
        flex-direction: column;
        width: 100%;
        text-align: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .logo h1 {
        margin-bottom: 1rem;
    }

    .nav-links a {
        padding: 0.8rem;
    }

    .container {
        padding: 20px;
        margin-top: 180px;
    }

    h1, h2 {
        font-size: 2rem;
    }

    .card {
        padding: 20px;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out forwards;
}

/* Add this to your existing CSS */
.alert {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    animation: slideIn 0.3s ease-out;
}

.alert.success {
    background: rgba(40, 167, 69, 0.2);
    border: 1px solid #28a745;
    color: #28a745;
}

.alert.error {
    background: rgba(220, 53, 69, 0.2);
    border: 1px solid #dc3545;
    color: #dc3545;
}

.alert p {
    margin: 5px 0;
    color: inherit;
}

@keyframes slideIn {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Add inside your existing <style> tag */
.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.benefits-list li {
    margin: 15px 0;
    display: flex;
    align-items: center;
    color: #ffffff;
}

.benefits-list li i {
    color: #fffc00;
    margin-right: 10px;
    font-size: 1.2rem;
}

/* Add inside your existing <style> tag */
.slot-selection {
    display: grid;
    gap: 20px;
    margin-bottom: 30px;
}

.slot-selection select {
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.1);
    padding: 15px 20px;
    border-radius: 10px;
    color: #ffffff;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.slot-selection select:focus {
    outline: none;
    border-color: #fffc00;
}

.slot-selection option {
    background: #1a1a1a;
    color: #ffffff;
}

.bookings-list {
    display: grid;
    gap: 20px;
}

.booking-item {
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-cancel {
    background: #dc3545;
    color: #ffffff;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 10px;
}

.btn-cancel:hover {
    background: #c82333;
    transform: translateY(-2px);
}

.booking-type {
    margin-bottom: 20px;
}

.duration-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 10px;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.radio-label input[type="radio"] {
    accent-color: #fffc00;
}

.weekday-selection {
    margin: 20px 0;
}

.weekday-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    accent-color: #fffc00;
}
</style>
<script src="slot-booking.js" defer></script>
</head>
<body>
<header>
<nav class="navbar">
    <div class="container">
        <div class="logo">
            <h1>The Bombay Gym</h1>
        </div>
        <ul class="nav-links">
            <!-- <li><a href="Home Page.html"><i class="fas fa-home"></i> Home</a></li> -->
            <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>
</header>

<main>
<section id="welcome">
    <div class="container">
        <?php if (isset($_SESSION['membership_success'])): ?>
            <div class="alert success" style="
                background: rgba(0, 255, 0, 0.1);
                border: 2px solid #fffc00;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                color: #fffc00;
                font-size: 1.1rem;">
                <?php 
                    echo $_SESSION['membership_success'];
                    unset($_SESSION['membership_success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['membership_error'])): ?>
            <div class="alert error" style="
                background: rgba(255, 0, 0, 0.1);
                border: 2px solid #ff3333;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                color: #ff3333;
                font-size: 1.1rem;">
                <?php 
                    echo $_SESSION['membership_error'];
                    unset($_SESSION['membership_error']);
                ?>
            </div>
        <?php endif; ?>

        <h2>Hello, <?php echo htmlspecialchars($name); ?>!</h2>
        <p>Welcome back! Here's an overview of your account and services.</p>
    </div>
</section>

<section id="services">
    <div class="container">
        <h2>Your Services</h2>
        <div class="card">
            <h3>Current Membership</h3>
            <?php if ($has_active_plan): ?>
                <p><strong>Plan Type:</strong> <?php echo htmlspecialchars($plan_type); ?></p>
                <p><strong>Valid From:</strong> <?php echo date('d M Y', strtotime($start_date)); ?></p>
                <p><strong>Valid Until:</strong> <?php echo date('d M Y', strtotime($end_date)); ?></p>
            <?php else: ?>
                <p>No active membership plan. Choose a plan below to get started!</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Your Plan Benefits</h3>
            <?php if ($has_active_plan): ?>
                <?php if ($plan_type === 'Monthly'): ?>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check"></i> Access to all gym facilities</li>
                        <li><i class="fas fa-check"></i> Basic fitness assessment</li>
                        <li><i class="fas fa-check"></i> Locker access</li>
                    </ul>
                <?php elseif ($plan_type === 'Yearly'): ?>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check"></i> All Monthly Plan benefits</li>
                        <li><i class="fas fa-check"></i> Two months free</li>
                        <li><i class="fas fa-check"></i> Quarterly fitness assessment</li>
                        <li><i class="fas fa-check"></i> Priority booking for classes</li>
                    </ul>
                <?php elseif ($plan_type === 'Premium'): ?>
                    <ul class="benefits-list">
                        <li><i class="fas fa-check"></i> All Yearly Plan benefits</li>
                        <li><i class="fas fa-check"></i> Personal training sessions</li>
                        <li><i class="fas fa-check"></i> Diet consultation</li>
                        <li><i class="fas fa-check"></i> 24/7 gym access</li>
                    </ul>
                <?php endif; ?>
            <?php else: ?>
                <p>Select a membership plan to view available benefits.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="plans">
<div class="container">
    <h2>Our Plans</h2>
    <div class="card">
        <h3>Monthly Plan</h3>
        <p>Access to all gym facilities for one month.</p>
        <p><strong>Price:</strong> ₹2,500/month</p>
        <a href="apply_membership.php" class="btn">Apply Now</a>
    </div>
    <div class="card">
        <h3>Yearly Plan</h3>
        <p>Access to all gym facilities for one year.</p>
        <p><strong>Price:</strong> ₹25,000/year</p>
        <a href="apply_membership.php" class="btn">Apply Now</a>
    </div>
    <div class="card">
        <h3>Premium Plan</h3>
        <p>Includes gym facilities, personal training, and diet consultation.</p>
        <p><strong>Price:</strong> ₹3,000/month</p>
        <a href="apply_membership.php" class="btn">Apply Now</a>
    </div>
</div>
</section>

<section id="profile">
<div class="container">
    <h2>Manage Your Profile</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert error">
            <?php 
                foreach($_SESSION['errors'] as $error) {
                    echo "<p>$error</p>";
                }
                unset($_SESSION['errors']);
            ?>
        </div>
    <?php endif; ?>

    <form action="update_profile.php" method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

        <button type="submit">Update Profile</button>
    </form>
</div>
</section>

<section id="slot-booking">
    <div class="container">
        <h2>Book Your Workout Slot</h2>
        
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

        <?php if ($has_active_plan): ?>
            <!-- Rest of your slot booking form code -->
            <div class="card">
                <h3>Select Your Workout Schedule</h3>
                <form action="book_slot.php" method="POST">
                    <div class="slot-selection">
                        <div class="booking-type">
                            <label>Booking Duration:</label>
                            <div class="duration-options">
                                <label class="radio-label">
                                    <input type="radio" name="booking_type" value="single" checked>
                                    Single Day
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="booking_type" value="week">
                                    Weekly
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="booking_type" value="month">
                                    Monthly
                                </label>
                            </div>
                        </div>

                        <div class="date-selection">
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" 
                                   min="<?php echo date('Y-m-d'); ?>" 
                                   required>
                        </div>
                        
                        <div class="weekday-selection" style="display: none;">
                            <label>Select Days:</label>
                            <div class="weekday-options">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="weekdays[]" value="Monday"> Monday
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="weekdays[]" value="Tuesday"> Tuesday
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="weekdays[]" value="Wednesday"> Wednesday
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="weekdays[]" value="Thursday"> Thursday
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="weekdays[]" value="Friday"> Friday
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="weekdays[]" value="Saturday"> Saturday
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="weekdays[]" value="Sunday"> Sunday
                                </label>
                            </div>
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
                    <button type="submit" class="btn">Book Slots</button>
                </form>
            </div>

            <!-- Replace the current bookings display section with this -->
            <div class="card">
                <h3>Your Workout Schedule</h3>
                <?php
                $booking_stmt = $conn->prepare("SELECT slot_time, booking_date, end_date, booking_type, weekdays, status 
                                              FROM gym_slots 
                                              WHERE user_id = ? 
                                              AND status = 'Booked'
                                              AND end_date >= CURDATE()
                                              ORDER BY booking_date ASC
                                              LIMIT 1");
                $booking_stmt->bind_param("i", $user_id);
                $booking_stmt->execute();
                $booking = $booking_stmt->get_result()->fetch_assoc();

                if ($booking): 
                    $weekdays = explode(',', $booking['weekdays']);
                ?>
                    <div class="booking-item">
                        <p><strong>Time Slot:</strong> <?php echo htmlspecialchars($booking['slot_time']); ?></p>
                        <p><strong>Duration:</strong> <?php echo ucfirst($booking['booking_type']); ?> Schedule</p>
                        <p><strong>Start Date:</strong> <?php echo date('d M Y', strtotime($booking['booking_date'])); ?></p>
                        <p><strong>End Date:</strong> <?php echo date('d M Y', strtotime($booking['end_date'])); ?></p>
                        <?php if (!empty($booking['weekdays'])): ?>
                        <p><strong>Selected Days:</strong> <?php echo implode(', ', $weekdays); ?></p>
                        <?php endif; ?>
                        <form action="cancel_slot.php" method="POST" style="display: inline;">
                            <input type="hidden" name="booking_date" value="<?php echo $booking['booking_date']; ?>">
                            <input type="hidden" name="slot_time" value="<?php echo $booking['slot_time']; ?>">
                            <button type="submit" class="btn-cancel">Cancel Schedule</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>No active workout schedule found.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p>Please activate a membership plan to book workout slots.</p>
                <a href="#plans" class="btn">View Plans</a>
            </div>
        <?php endif; ?>
    </div>
</section>

</main>

<footer>
<div class="container">
<p>&copy; 2025 Gym and Fitness Centre Management. All rights reserved.</p>
</div>
</footer>
</body>
</html>