<?php
// Include logging functionality
include 'logger.php';

// Parse JSON from php://input (webhook data)
$input = file_get_contents('php://input');
$webhook_data = json_decode($input, true);

// Fallback for testing with GET parameters (remove in production)
if (!$webhook_data && isset($_GET['user'])) {
    $webhook_data = [
        'user' => $_GET['user'],
        'code' => $_GET['code'],
        'order_id' => $_GET['order_id'] ?? 'TEST_ORDER',
        'payment_status' => 'SUCCESS'
    ];
}

// Validate required fields
if (!$webhook_data || !isset($webhook_data['user'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing user parameter']);
    exit;
}

$username = $webhook_data['user'];
$code = $webhook_data['code'] ?? null;
$order_id = $webhook_data['order_id'] ?? null;
$payment_status = $webhook_data['payment_status'] ?? 'FAILED';

// Load database
$dbPath = 'tvprefs_db.json';
if (!file_exists($dbPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database not found']);
    exit;
}

$db = json_decode(file_get_contents($dbPath), true);
if (!$db) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database corrupted']);
    exit;
}

$user = $db[$username] ?? null;

// Validate user exists and webhook code matches
if (!$user || !isset($user['webhook_code']) || $user['webhook_code'] !== $code) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Validate payment status
if ($payment_status !== 'SUCCESS') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payment not successful']);
    exit;
}

// Generate token and set expiry after webhook success
$token = bin2hex(random_bytes(16));
$expires = date("Y-m-d H:i:s", strtotime("+120 days"));

// Update user record
$db[$username]['token'] = $token;
$db[$username]['expires'] = $expires;
$db[$username]['paid'] = true;
$db[$username]['flag'] = 1; // Maintain active session flag
$db[$username]['last_activity'] = date("Y-m-d H:i:s");
$db[$username]['payment_order_id'] = $order_id;
$db[$username]['payment_date'] = date("Y-m-d H:i:s");

// Save to database
if (file_put_contents($dbPath, json_encode($db, JSON_PRETTY_PRINT))) {
    // Log successful payment
    write_log("transaction_log.txt", [
        'event' => 'webhook_success',
        'user' => $username,
        'order_id' => $order_id,
        'token' => $token,
        'expires' => $expires,
        'timestamp' => date("Y-m-d H:i:s")
    ]);
    
    http_response_code(200);
    
    // If this is a direct browser request (testing), redirect to special page
    if (isset($_GET['user'])) {
        header("Location: special.php?user=" . urlencode($username));
        exit;
    }
    
    // Otherwise, return JSON response for webhook
    echo json_encode([
        'status' => 'success',
        'message' => 'Payment processed successfully',
        'token' => $token,
        'expires' => $expires
    ]);
    
    // Log to error log as well
    error_log("Payment success for user: $username, Order: $order_id, Token: $token");
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update database']);
}
?>