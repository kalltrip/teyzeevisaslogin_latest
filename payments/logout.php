<?php
session_start();

// If user is logged in, logout from database
if (isset($_SESSION['session_token'])) {
    try {
        require_once 'database.php';
        require_once 'user.php';
        
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        
        // Logout from database (deactivate session)
        $user->logout($_SESSION['session_token']);
    } catch (Exception $e) {
        // Log error but continue with session cleanup
        error_log("Logout error: " . $e->getMessage());
    }
}

// Clear all session data
session_destroy();

// Redirect to login page
header("Location: login.php?logout=success");
exit;
?>