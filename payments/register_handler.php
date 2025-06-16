<?php
// register_handler.php
session_start();

// Include required files
require_once 'database.php';
require_once 'user.php';

// Set response header
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON input is not available, try $_POST
    if (!$input) {
        $input = $_POST;
    }

    // Validate required fields
    $required_fields = ['email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            exit;
        }
    }

    $email = trim($input['email']);
    $password = $input['password'];
    $full_name = trim($input['full_name'] ?? '');
    $phone = trim($input['phone'] ?? ''); // Phone is optional, default empty
    $address = trim($input['address'] ?? ''); // Address/City is optional, default empty

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    // Validate password length
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit;
    }

    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Create user instance and register
    $user = new User($db);
    // Pass all 5 parameters: email, password, full_name, phone, address
    $result = $user->register($email, $password, $full_name, $phone, $address);

    // Return result
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>