<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_type = $_POST['plan_type'];
    $start_date = $_POST['start_date'];
    
    // Calculate end date based on plan type
    $end_date = date('Y-m-d', strtotime($start_date . ' + ' . 
        ($plan_type === 'Yearly' ? '1 year' : '1 month')));
    
    // Set amount based on plan type
    $amounts = [
        'Monthly' => 2500,
        'Yearly' => 25000,
        'Premium' => 3000
    ];
    $amount = $amounts[$plan_type];

    try {
        $stmt = $conn->prepare("INSERT INTO memberships (user_id, plan_type, start_date, end_date, amount, status) 
                               VALUES (?, ?, ?, ?, ?, 'Active')");
        $stmt->bind_param("isssd", $user_id, $plan_type, $start_date, $end_date, $amount);
        
        // Add this after membership insertion
        if ($stmt->execute()) {
            $membership_id = $conn->insert_id;
            $_SESSION['pending_payment'] = [
                'membership_id' => $membership_id,
                'amount' => $amount,
                'plan_type' => $plan_type
            ];
            header("Location: payment.php");
            exit();
        } else {
            $_SESSION['membership_error'] = "Error submitting application. Please try again.";
            header("Location: apply_membership.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred. Please try again.";
        header("Location: apply_membership.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Membership - The Bombay Gym</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .navbar .logo h1 {
            color: #fffc00;
            font-size: 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            color: #fffc00;
            background: rgba(255, 252, 0, 0.1);
        }

        /* Main Content Styling */
        main {
            padding-top: 100px;
            min-height: calc(100vh - 100px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        h2 {
            color: #fffc00;
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 40px;
            text-transform: uppercase;
        }

        /* Plan Cards Styling */
        .plan-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .plan-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .plan-card.selected {
            border-color: #fffc00;
            transform: scale(1.05);
        }

        .plan-card h3 {
            color: #fffc00;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .price {
            font-size: 2rem;
            color: #fff;
            margin: 20px 0;
        }

        .plan-features {
            text-align: left;
            margin: 20px 0;
        }

        .plan-features li {
            margin: 10px 0;
            list-style: none;
            color: #fff;
        }

        .plan-features li i {
            color: #fffc00;
            margin-right: 10px;
        }

        /* Form Styling */
        #application-form {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: 15px;
            display: none;
        }

        .input-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #fffc00;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        input {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #fffc00;
        }

        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background: #fffc00;
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button[type="submit"]:hover {
            background: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 252, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .plan-cards {
                grid-template-columns: 1fr;
            }

            .navbar .container {
                flex-direction: column;
                padding: 1rem;
            }

            .nav-links {
                margin-top: 1rem;
            }

            h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h1>The Bombay Gym</h1>
            </div>
            <ul class="nav-links">
                <li><a href="user_page.php"><i class="fas fa-user"></i> Dashboard</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <main>
        <section>
            <div class="container">
                <h2>Choose Your Membership Plan</h2>
                
                <div class="plan-cards">
                    <!-- Monthly Plan -->
                    <div class="plan-card" onclick="selectPlan('Monthly')">
                        <h3>Monthly Plan</h3>
                        <div class="price">₹2,500/month</div>
                        <div class="plan-features">
                            <li><i class="fas fa-check"></i> Access to all gym facilities</li>
                            <li><i class="fas fa-check"></i> Basic fitness assessment</li>
                            <li><i class="fas fa-check"></i> Locker access</li>
                        </div>
                    </div>

                    <!-- Yearly Plan -->
                    <div class="plan-card" onclick="selectPlan('Yearly')">
                        <h3>Yearly Plan</h3>
                        <div class="price">₹25,000/year</div>
                        <div class="plan-features">
                            <li><i class="fas fa-check"></i> All Monthly Plan features</li>
                            <li><i class="fas fa-check"></i> Two months free</li>
                            <li><i class="fas fa-check"></i> Quarterly fitness assessment</li>
                        </div>
                    </div>

                    <!-- Premium Plan -->
                    <div class="plan-card" onclick="selectPlan('Premium')">
                        <h3>Premium Plan</h3>
                        <div class="price">₹3,000/month</div>
                        <div class="plan-features">
                            <li><i class="fas fa-check"></i> All Yearly Plan features</li>
                            <li><i class="fas fa-check"></i> Personal training sessions</li>
                            <li><i class="fas fa-check"></i> Diet consultation</li>
                        </div>
                    </div>
                </div>

                <form id="application-form" action="apply_membership.php" method="POST">
                    <input type="hidden" id="plan_type" name="plan_type">
                    
                    <div class="input-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <button type="submit">Submit Application</button>
                </form>
            </div>
        </section>
    </main>

    <script>
        function selectPlan(planType) {
            // Remove selected class from all cards
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
            
            // Show form and set plan type
            const form = document.getElementById('application-form');
            form.style.display = 'grid';
            document.getElementById('plan_type').value = planType;
        }
    </script>
</body>
</html>