<?php
session_start();

if (!isset($_SESSION['user'])) {
    echo "Unauthorized access.";
    exit;
}

$email = $_SESSION['user'];

// MySQL DB credentials
$host = 'localhost';
$dbname = 'visas_db';
$username = 'devuser';
$password = 'webcoder01@2905';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch user info from customers table
$stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

// Example output
echo "<h3>Welcome, " . htmlspecialchars($user['full_name'] ?? $email) . "</h3>";
echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
?>
