<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['pending_payment'])) {
    header("Location: user_page.php");
    exit();
}

$payment_data = $_SESSION['pending_payment'];

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $membership_id = $payment_data['membership_id'];
    $payment_method = $_POST['payment_method'];
    
    // Insert payment record without payment_status
    $stmt = $conn->prepare("INSERT INTO payments (membership_id, amount, payment_method) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $membership_id, $payment_data['amount'], $payment_method);
    
    if ($stmt->execute()) {
        unset($_SESSION['pending_payment']);
        $_SESSION['membership_success'] = "Payment successful! Your membership is now active.";
        header("Location: user_page.php");
        exit();
    } else {
        $_SESSION['payment_error'] = "Payment failed. Please try again.";
        header("Location: payment.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - The Bombay Gym</title>
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

        .payment-container {
            max-width: 600px;
            margin: 120px auto;
            padding: 40px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h2 {
            color: #fffc00;
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .payment-summary {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .payment-summary h3 {
            color: #fffc00;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .payment-summary p {
            margin: 10px 0;
            font-size: 1.1rem;
        }

        .payment-summary strong {
            color: #fffc00;
            margin-right: 10px;
        }

        .payment-methods {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }

        .payment-method {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            border: 2px solid transparent;
        }

        .payment-method:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
            border-color: rgba(255, 252, 0, 0.3);
        }

        .payment-method input[type="radio"] {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            accent-color: #fffc00;
        }

        .payment-method i {
            margin-right: 15px;
            font-size: 1.5rem;
            color: #fffc00;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: #fffc00;
            color: #000000;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 20px;
        }

        .btn:hover {
            background: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 252, 0, 0.2);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .payment-container {
                margin: 80px 20px;
                padding: 30px;
            }

            h2 {
                font-size: 1.7rem;
            }

            .payment-method {
                padding: 15px;
            }

            .btn {
                font-size: 1rem;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Complete Payment</h2>
        
        <div class="payment-summary">
            <h3>Order Summary</h3>
            <p><strong>Plan:</strong> <?php echo htmlspecialchars($payment_data['plan_type']); ?></p>
            <p><strong>Amount:</strong> ₹<?php echo number_format($payment_data['amount'], 2); ?></p>
        </div>

        <form action="payment.php" method="POST">
            <div class="payment-methods">
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="UPI" required>
                    <i class="fas fa-mobile-alt"></i> UPI Payment
                </label>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="Card" required>
                    <i class="fas fa-credit-card"></i> Credit/Debit Card
                </label>
                <label class="payment-method">
                    <input type="radio" name="payment_method" value="NetBanking" required>
                    <i class="fas fa-university"></i> Net Banking
                </label>
            </div>

            <button type="submit" class="btn">
                Pay ₹<?php echo number_format($payment_data['amount'], 2); ?>
            </button>
        </form>
    </div>
</body>
</html>