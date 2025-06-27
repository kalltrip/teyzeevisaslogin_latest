<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional debug log
error_log("User logging out: " . ($_SESSION['user_id'] ?? 'unknown'));

// Corrected path to database.php (in payments/)
require_once(__DIR__ . '/../payments/database.php');

// Function to cleanup session from database
function cleanupDatabaseSession() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("UPDATE login_sessions SET status = 'terminated', last_activity = NOW() WHERE user_id = ? AND session_token = ?");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
        } catch (Exception $e) {
            error_log("Logout session cleanup error: " . $e->getMessage());
        }
    }
}

// Cleanup DB session
cleanupDatabaseSession();

// Destroy session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect to home page
header("Location: /index.php");
exit;
