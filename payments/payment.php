<?php
// Start the session
session_start();

// ✅ Correct login check
if (empty($_SESSION['email'])) {
    header("Location: ../php/login.php");
    exit;
}


// Check if user is logged in
/*if (!isset($_SESSION['user']) || !isset($_SESSION['session_token'])) {
    error_log("payment.php: User not logged in, redirecting to login");
    // Use relative path and check if login.php exists
    if (file_exists('login.php')) {
        header('Location: login.php');
    } else {
        // Try different paths
        if (file_exists('../login.php')) {
            header('Location: ../login.php');
        } else {
            die('Login page not found. Please check your file structure.');
        }
    }
    exit;
}
*/

// If we reach here, user should be logged in
error_log("payment.php: User is logged in as: " . $_SESSION['user']);

// Check if required files exist before requiring them
$requiredFiles = ['database.php', 'User.php', '../php/logger.php'];
$missingFiles = [];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = $file;
    }
}

if (!empty($missingFiles)) {
    die("Missing required files: " . implode(', ', $missingFiles) . ". Please check your file structure.");
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/User.php';
include __DIR__ . '/../php/logger.php';
require_once __DIR__ . '/../php/header.php';



$email = $_SESSION['user'];
$sessionToken = $_SESSION['session_token'];
$sessionValidationError = false;

// Validate session with database
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if the connection is successful
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    $user = new User($db);
    
    // Validate session
    $sessionData = $user->validateSession($sessionToken);
    
    if (!$sessionData) {
        error_log("Session validation failed: No session data returned");
        $sessionValidationError = "Session not found in database";
    } elseif ($sessionData['email'] !== $email) {
        error_log("Session validation failed: Email mismatch - Session: " . $sessionData['email'] . " vs Current: " . $email);
        $sessionValidationError = "Session email mismatch";
    } else {
        error_log("Session validation successful for: " . $email);
    }
    
} catch (Exception $e) {
    error_log("Payment session validation error: " . $e->getMessage());
    $sessionValidationError = "Database error: " . $e->getMessage();
}

// If session validation failed, destroy session and redirect
if ($sessionValidationError) {
    error_log("Destroying session due to validation error: " . $sessionValidationError);
    session_destroy();
    
    // Use relative path for redirect
    if (file_exists('login.php')) {
        header("Location: login.php?error=invalid_session");
    } else {
        header("Location: ../login.php?error=invalid_session");
    }
    exit;
}

// Cashfree configuration
$appId = "TEST1025202424afd2dc8021f396d1a142025201";
$secretKey = "cfsk_ma_test_9e2009d996703608210713144d330cbb_72985f22";

// Generate unique order ID
$clean_email = preg_replace('/[^a-zA-Z0-9]/', '', $email);
$order_id = "ORD_" . substr($clean_email, 0, 15) . "_" . time();

// Generate webhook code for this user
$webhook_code = bin2hex(random_bytes(16));

// Store the webhook code and order ID in session storage
$_SESSION['webhook_code'] = $webhook_code;
$_SESSION['pending_order_id'] = $order_id;

// Create payment session with Cashfree
function createPaymentSession($order_id, $email, $appId, $secretKey, $webhook_code) {
    $customer_id = preg_replace('/[^a-zA-Z0-9_-]/', '_', $email);
    $customer_id = preg_replace('/_+/', '_', $customer_id);
    $customer_id = 'CUST_' . ltrim($customer_id, '_');
    
    // Get the current domain
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    $orderData = [
        "order_id" => $order_id,
        "order_amount" => 99.00,
        "order_currency" => "INR",
        "customer_details" => [
            "customer_id" => $customer_id,
            "customer_name" => explode('@', $email)[0],
            "customer_email" => $email,
            "customer_phone" => "9999999999"
        ],
        "order_meta" => [
            // Return URL - where user is redirected after payment
            "return_url" => $domain . "/payment_return.php?user=" . urlencode($email) . "&order_id=" . $order_id . "&code=" . $webhook_code,
            // Webhook URL - where Cashfree sends payment notifications
            "notify_url" => $domain . "/webhook_pay.php"
        ]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://sandbox.cashfree.com/pg/orders",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($orderData),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-api-version: 2023-08-01",
            "x-client-id: $appId",
            "x-client-secret: $secretKey"
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    
    // Handle cURL errors
    if ($response === false) {
        error_log("cURL Error: " . $curlError);
        return ['error' => 'Network error: ' . $curlError];
    }
    
    $responseData = json_decode($response, true);
    
    // Log the transaction
    if (function_exists('write_log')) {
        write_log("transaction_log.txt", [
            'event' => 'create_order',
            'user' => $email,
            'order_id' => $order_id,
            'request' => $orderData,
            'response' => $responseData,
            'http_code' => $httpCode,
            'curl_error' => $curlError,
            'timestamp' => date("Y-m-d H:i:s")
        ]);
    }
    
    // Check for API errors
    if ($httpCode !== 200) {
        error_log("Cashfree API Error: HTTP $httpCode - " . $response);
        return ['error' => 'Payment gateway error', 'details' => $responseData];
    }
    
    return $responseData;
}

// Initialize payment session
$paymentSession = null;
$paymentSessionId = null;
$paymentError = null;

$paymentSession = createPaymentSession($order_id, $email, $appId, $secretKey, $webhook_code);

if (isset($paymentSession['error'])) {
    $paymentError = $paymentSession['error'];
    if (isset($paymentSession['details'])) {
        $paymentError .= ': ' . json_encode($paymentSession['details']);
    }
} else {
    $paymentSessionId = $paymentSession['payment_session_id'] ?? null;
    if (!$paymentSessionId) {
        $paymentError = "Payment session ID not received from Cashfree";
        error_log("Cashfree response missing payment_session_id: " . json_encode($paymentSession));
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teyzee Visas - Premium Payment</title>
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .payment-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
        }
        .logo {
            font-size: 1.8em;
            font-weight: bold;
            color: #667eea;
        }
        .user-info {
            font-size: 14px;
            color: #666;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: background 0.2s;
            margin-top: 5px;
            display: inline-block;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .payment-details {
            background: #f8f9ff;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .service-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .feature-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            border: 1px solid #e9ecef;
        }
        .feature-item strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        .pay-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .pay-button:hover {
            transform: translateY(-2px);
        }
        .pay-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        .security-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
            font-size: 14px;
        }
        .price-breakdown {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .price-row.total {
            border-top: 2px solid #ffc107;
            padding-top: 10px;
            font-weight: bold;
            font-size: 1.1em;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .debug-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #bee5eb;
            font-size: 12px;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (max-width: 768px) {
            .payment-container {
                padding: 20px;
                margin: 10px;
            }
            .header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            .service-features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="header">
            <div class="logo">Teyzee Visas</div>
            <div>
                <div class="user-info">Logged in as: <?php echo htmlspecialchars($email); ?></div>
                <a href="../php/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if ($sessionValidationError): ?>
            <div class="error-message">
                <strong>Session Error:</strong> <?php echo htmlspecialchars($sessionValidationError); ?>
                <br><a href="../php/logout.php">Click here to logout and login again</a>
            </div>
            <div class="debug-info">
                <strong>Debug Information:</strong><br>
                Session ID: <?php echo session_id(); ?><br>
                User Email: <?php echo htmlspecialchars($email ?? 'Not set'); ?><br>
                Session Token: <?php echo isset($sessionToken) ? substr($sessionToken, 0, 20) . '...' : 'Not set'; ?><br>
                Error: <?php echo htmlspecialchars($sessionValidationError); ?>
            </div>
        <?php elseif ($paymentError): ?>
            <div class="error-message">
                <strong>Payment Error:</strong> <?php echo htmlspecialchars($paymentError); ?>
                <br><small>Please refresh the page and try again, or contact support if the problem persists.</small>
            </div>
            <div class="debug-info">
                <strong>Debug Information:</strong><br>
                Order ID: <?php echo htmlspecialchars($order_id); ?><br>
                Timestamp: <?php echo date('Y-m-d H:i:s'); ?><br>
                <?php if (isset($paymentSession['message'])): ?>
                API Message: <?php echo htmlspecialchars($paymentSession['message']); ?><br>
                <?php endif; ?>
            </div>
        <?php elseif (!$paymentSessionId): ?>
            <div class="error-message">
                <strong>Payment Error:</strong> Unable to initialize payment session. Please try again later.
            </div>
        <?php else: ?>
            <h2 style="color: #333; margin-bottom: 20px;">Premium Service Payment</h2>
            
            <div class="payment-details">
                <h3 style="margin-top: 0; color: #667eea;">Service Details</h3>
                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Service:</strong> Premium Visa Processing</p>
            </div>

            <div class="price-breakdown">
                <h3 style="margin-top: 0; color: #856404;">Price Breakdown</h3>
                <div class="price-row">
                    <span>Premium Service Fee</span>
                    <span>₹99.00</span>
                </div>
                <div class="price-row">
                    <span>Processing Fee</span>
                    <span>₹0.00</span>
                </div>
                <div class="price-row total">
                    <span>Total Amount</span>
                    <span>₹99.00</span>
                </div>
            </div>

            <div class="service-features">
                <div class="feature-item">
                    <strong>✓ Priority Processing</strong>
                    <div>Faster visa application processing</div>
                </div>
                <div class="feature-item">
                    <strong>✓ Expert Support</strong>
                    <div>Dedicated customer service</div>
                </div>
                <div class="feature-item">
                    <strong>✓ Document Review</strong>
                    <div>Professional document verification</div>
                </div>
                <div class="feature-item">
                    <strong>✓ Status Updates</strong>
                    <div>Real-time application tracking</div>
                </div>
            </div>

            <div class="security-info">
                <strong>🔒 Secure Payment</strong><br>
                Your payment information is encrypted and secure. We use industry-standard security measures to protect your data.
            </div>

            <button id="payButton" class="pay-button">
                Pay ₹99.00 Securely
            </button>

            <div id="loadingDiv" class="loading" style="display: none;">
                <div class="spinner"></div>
                <p>Processing your payment...</p>
            </div>

            <div style="text-align: center; font-size: 12px; color: #666; margin-top: 20px;">
                <p>By proceeding with payment, you agree to our Terms of Service and Privacy Policy.</p>
                <p>Need help? Contact support at info@teyzeevisas.com</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($paymentSessionId): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cashfree = Cashfree({
                mode: "sandbox" // Change to "production" for live environment
            });

            document.getElementById('payButton').addEventListener('click', async function() {
                const button = this;
                const loadingDiv = document.getElementById('loadingDiv');
                
                // Disable button and show loading
                button.disabled = true;
                button.textContent = 'Processing...';
                loadingDiv.style.display = 'block';

                try {
                    const checkoutOptions = {
                        paymentSessionId: "<?php echo $paymentSessionId; ?>"
                    };

                    console.log('Initiating payment with session ID:', checkoutOptions.paymentSessionId);

                    const result = await cashfree.checkout(checkoutOptions);
                    
                    console.log('Payment result:', result);
                    
                    if (result.error) {
                        console.error('Payment failed:', result.error);
                        alert('Payment failed: ' + (result.error.message || 'Unknown error'));
                        
                        // Re-enable button
                        button.disabled = false;
                        button.textContent = 'Pay ₹99.00 Securely';
                        loadingDiv.style.display = 'none';
                    }
                    // Success case is handled by redirect to return URL
                } catch (error) {
                    console.error('Error during payment:', error);
                    alert('An error occurred during payment. Please try again.');
                    
                    // Re-enable button
                    button.disabled = false;
                    button.textContent = 'Pay ₹99.00 Securely';
                    loadingDiv.style.display = 'none';
                }
            });

            // Auto-hide loading on page unload
            window.addEventListener('beforeunload', function() {
                document.getElementById('loadingDiv').style.display = 'none';
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>