<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all incoming data
error_log("Payment return accessed with GET: " . json_encode($_GET));
error_log("Payment return accessed with POST: " . json_encode($_POST));

// Fetch user and order_id from GET
$email = $_GET['user'] ?? '';
$order_id = $_GET['order_id'] ?? '';
$signature = $_GET['code'] ?? '';

// Validate required parameters
if (empty($email) || empty($order_id) || empty($signature)) {
    error_log("Missing required parameters - Email: $email, Order ID: $order_id, Signature: $signature");
    die("Missing required parameters.");
}

// MySQL DB credentials
$host = 'localhost';
$dbname = 'visas_db';
$username = 'devuser';
$password = 'webcoder01@2905';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Check if the transaction already exists
$check = $pdo->prepare("SELECT * FROM payment_transactions WHERE order_id = ? AND email = ?");
$check->execute([$order_id, $email]);
$txn = $check->fetch(PDO::FETCH_ASSOC);

$status = 'PENDING'; // Default status
$message = '';

if ($txn) {
    $status = $txn['status'];
    $message = "Payment record already exists";
    error_log("Existing transaction found: " . json_encode($txn));
} else {
    // Insert new payment transaction record
    try {
        $stmt = $pdo->prepare("INSERT INTO payment_transactions (email, order_id, status, received_at, signature) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$email, $order_id, 'PENDING', $signature]);
        $message = "Payment record created successfully";
        error_log("New payment transaction inserted for order: $order_id");
    } catch (PDOException $e) {
        error_log("Failed to insert payment transaction: " . $e->getMessage());
        $message = "Failed to record payment transaction";
    }
}

// Optional: Add logging to file if needed
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'event' => 'payment_return',
    'email' => $email,
    'order_id' => $order_id,
    'signature' => $signature,
    'status' => $status,
    'message' => $message,
    'get_params' => $_GET,
    'post_params' => $_POST
];

file_put_contents("payment_logs.txt", json_encode($log_data) . PHP_EOL, FILE_APPEND | LOCK_EX);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status - Teyzee Visas</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
        }
        .logo {
            font-size: 1.8em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .status-icon {
            font-size: 4em;
            margin: 20px 0;
        }
        .status-pending {
            color: #ffc107;
        }
        .status-success {
            color: #28a745;
        }
        .status-failed {
            color: #dc3545;
        }
        .status-title {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .action-buttons {
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .note {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
            font-size: 14px;
            text-align: left;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Teyzee Visas</div>
            <div>Payment Status</div>
        </div>

        <?php if ($status === 'PENDING'): ?>
            <div class="status-icon status-pending">⏳</div>
            <div class="status-title status-pending">Payment Processing</div>
            <p>Your payment is being processed. Please wait while we verify the transaction.</p>
        <?php elseif ($status === 'SUCCESS' || $status === 'PAID'): ?>
            <div class="status-icon status-success">✅</div>
            <div class="status-title status-success">Payment Successful!</div>
            <p>Thank you for your payment. Your premium service has been activated.</p>
        <?php else: ?>
            <div class="status-icon status-failed">❌</div>
            <div class="status-title status-failed">Payment Status Unknown</div>
            <p>We're verifying your payment status. Please contact support if you have concerns.</p>
        <?php endif; ?>

        <div class="details">
            <div class="detail-row">
                <strong>Order ID:</strong>
                <span><?php echo htmlspecialchars($order_id); ?></span>
            </div>
            <div class="detail-row">
                <strong>Email:</strong>
                <span><?php echo htmlspecialchars($email); ?></span>
            </div>
            <div class="detail-row">
                <strong>Status:</strong>
                <span><?php echo htmlspecialchars($status); ?></span>
            </div>
            <div class="detail-row">
                <strong>Timestamp:</strong>
                <span><?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
        </div>

        <div class="note">
            <strong>📋 What happens next?</strong><br>
            • Your payment confirmation will be sent to your email<br>
            • Premium services will be activated within 5-10 minutes<br>
            • You can check your account status in the dashboard<br>
            • Contact support if you need immediate assistance
        </div>

        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>

        <div style="margin-top: 30px; font-size: 12px; color: #666;">
            <p>If you encounter any issues, please contact support at support@teyzeevisas.com</p>
            <p>Reference ID: <?php echo htmlspecialchars($order_id); ?></p>
        </div>
    </div>

    <script>
        // Auto-refresh page every 30 seconds if status is pending
        <?php if ($status === 'PENDING'): ?>
        setTimeout(function() {
            location.reload();
        }, 30000);
        <?php endif; ?>
        
        // Log page access for debugging
        console.log('Payment return page loaded', {
            email: '<?php echo htmlspecialchars($email); ?>',
            orderId: '<?php echo htmlspecialchars($order_id); ?>',
            status: '<?php echo htmlspecialchars($status); ?>',
            timestamp: '<?php echo date('Y-m-d H:i:s'); ?>'
        });
    </script>
</body>
</html>