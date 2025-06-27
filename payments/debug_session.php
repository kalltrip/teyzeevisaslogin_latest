<?php
// debug_session.php - Use this to debug your session and database issues
// Place this file in the same directory as your login.php

// Session configuration - same as login.php
ini_set('session.cookie_domain', '.teyzeevisas.com');
ini_set('session.cookie_path', '/');
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Session and Database Debug Information</h2>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .section { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; } .error { background: #ffe6e6; } .success { background: #e6ffe6; } .info { background: #e6f3ff; }</style>";

// 1. Check current session
echo "<div class='section info'>";
echo "<h3>Current Session Information</h3>";
echo "<strong>Session ID:</strong> " . session_id() . "<br>";
echo "<strong>Session Status:</strong> " . (session_status() == PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . "<br>";
echo "<strong>Session Data:</strong><br>";
if (!empty($_SESSION)) {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "No session data found<br>";
}
echo "</div>";

// 2. Test database connection
echo "<div class='section'>";
echo "<h3>Database Connection Test</h3>";
try {
    require_once 'database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<div class='success'>✓ Database connection successful</div>";
        
        // Check if customers table exists
        $stmt = $db->query("SHOW TABLES LIKE 'customers'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✓ customers table exists</div>";
            
            // Count customers
            $stmt = $db->query("SELECT COUNT(*) as count FROM customers");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='info'>Total customers in database: " . $result['count'] . "</div>";
        } else {
            echo "<div class='error'>✗ customers table not found</div>";
        }
        
        // Check if login_sessions table exists
        $stmt = $db->query("SHOW TABLES LIKE 'login_sessions'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✓ login_sessions table exists</div>";
            
            // Count active sessions
            $stmt = $db->query("SELECT COUNT(*) as count FROM login_sessions WHERE expires_at > NOW()");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='info'>Active sessions in database: " . $result['count'] . "</div>";
        } else {
            echo "<div class='error'>✗ login_sessions table not found</div>";
        }
        
    } else {
        echo "<div class='error'>✗ Database connection failed</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Database error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 3. Test User class
echo "<div class='section'>";
echo "<h3>User Class Test</h3>";
try {
    require_once 'user.php';
    
    if (class_exists('User')) {
        echo "<div class='success'>✓ User class loaded successfully</div>";
        
        if (isset($db) && $db) {
            $user = new User($db);
            echo "<div class='success'>✓ User class instantiated successfully</div>";
        } else {
            echo "<div class='error'>✗ Cannot instantiate User class - no database connection</div>";
        }
    } else {
        echo "<div class='error'>✗ User class not found</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ User class error: " . $e->getMessage() . "</div>";
}
echo "</div>";

// 4. PHP Configuration
echo "<div class='section info'>";
echo "<h3>PHP Configuration</h3>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Session Save Path:</strong> " . session_save_path() . "<br>";
echo "<strong>Session Cookie Params:</strong><br>";
$params = session_get_cookie_params();
echo "<pre>" . print_r($params, true) . "</pre>";
echo "</div>";

// 5. Test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div class='section'>";
    echo "<h3>Form Submission Test</h3>";
    echo "<strong>POST Data:</strong><br>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    if (isset($_POST['test_email']) && isset($_POST['test_password'])) {
        $email = trim($_POST['test_email']);
        $password = $_POST['test_password'];
        
        try {
            if (isset($user)) {
                $result = $user->login($email, $password);
                echo "<div class='" . ($result['success'] ? 'success' : 'error') . "'>";
                echo "Login test result: " . ($result['success'] ? 'Success' : 'Failed') . "<br>";
                echo "Message: " . $result['message'] . "<br>";
                if ($result['success']) {
                    echo "Session token: " . $result['session_token'] . "<br>";
                    echo "User data: <pre>" . print_r($result['user'], true) . "</pre>";
                }
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>Login test error: " . $e->getMessage() . "</div>";
        }
    }
    echo "</div>";
}

// Test form
echo "<div class='section'>";
echo "<h3>Test Login Form</h3>";
echo "<form method='POST'>";
echo "<label>Email: <input type='email' name='test_email' required></label><br><br>";
echo "<label>Password: <input type='password' name='test_password' required></label><br><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";
echo "</div>";

echo "<div class='section info'>";
echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Run this debug script to identify issues</li>";
echo "<li>Check your database tables and data</li>";
echo "<li>Verify your user.php login method works correctly</li>";
echo "<li>Check server error logs for detailed errors</li>";
echo "<li>Test with a known good email/password combination</li>";
echo "</ol>";
echo "</div>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug - Teyzee Visas</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            margin: 10px 0;
        }
        .status {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        h1, h2 {
            color: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Session Debug Information</h1>
        
        <div class="section">
            <h2>Session Status</h2>
            <?php
            $session_status = session_status();
            $status_text = [
                PHP_SESSION_DISABLED => 'Sessions are disabled',
                PHP_SESSION_NONE => 'Sessions are enabled, but no session exists',
                PHP_SESSION_ACTIVE => 'Sessions are enabled and a session exists'
            ];
            
            if ($session_status === PHP_SESSION_ACTIVE) {
                echo '<div class="status success">✅ Session is active</div>';
            } else {
                echo '<div class="status error">❌ Session is not active: ' . $status_text[$session_status] . '</div>';
            }
            ?>
            
            <table>
                <tr><th>Session ID</th><td><?php echo session_id(); ?></td></tr>
                <tr><th>Session Name</th><td><?php echo session_name(); ?></td></tr>
                <tr><th>Session Status</th><td><?php echo $status_text[$session_status]; ?></td></tr>
                <tr><th>Session Save Path</th><td><?php echo session_save_path(); ?></td></tr>
                <tr><th>Session Cookie Params</th><td><?php echo json_encode(session_get_cookie_params()); ?></td></tr>
            </table>
        </div>

        <div class="section">
            <h2>Session Data</h2>
            <?php if (empty($_SESSION)): ?>
                <div class="status warning">⚠️ No session data found</div>
            <?php else: ?>
                <div class="status success">✅ Session data exists</div>
                <div class="code-block">
                    <?php echo htmlspecialchars(print_r($_SESSION, true)); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Login Status Check</h2>
            <?php
            $is_logged_in = isset($_SESSION['user']) && isset($_SESSION['session_token']);
            if ($is_logged_in) {
                echo '<div class="status success">✅ User appears to be logged in</div>';
                echo '<p><strong>User:</strong> ' . htmlspecialchars($_SESSION['user']) . '</p>';
                echo '<p><strong>Session Token:</strong> ' . htmlspecialchars(substr($_SESSION['session_token'], 0, 20)) . '...</p>';
            } else {
                echo '<div class="status error">❌ User is not logged in</div>';
                echo '<p>Missing: ';
                if (!isset($_SESSION['user'])) echo 'user ';
                if (!isset($_SESSION['session_token'])) echo 'session_token ';
                echo '</p>';
            }
            ?>
        </div>

        <div class="section">
            <h2>Database Connection Test</h2>
            <?php
            try {
                if (file_exists('database.php')) {
                    require_once 'database.php';
                    $database = new Database();
                    $db = $database->getConnection();
                    echo '<div class="status success">✅ Database connection successful</div>';
                    
                    if (file_exists('user.php') && $is_logged_in) {
                        require_once 'user.php';
                        $user = new User($db);
                        $sessionData = $user->validateSession($_SESSION['session_token']);
                        
                        if ($sessionData) {
                            echo '<div class="status success">✅ Session validation successful</div>';
                            echo '<div class="code-block">' . htmlspecialchars(print_r($sessionData, true)) . '</div>';
                        } else {
                            echo '<div class="status error">❌ Session validation failed</div>';
                        }
                    } else {
                        echo '<div class="status warning">⚠️ Cannot test session validation (missing user.php or not logged in)</div>';
                    }
                } else {
                    echo '<div class="status error">❌ database.php not found</div>';
                }
            } catch (Exception $e) {
                echo '<div class="status error">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>

        <div class="section">
            <h2>Server Information</h2>
            <table>
                <tr><th>PHP Version</th><td><?php echo phpversion(); ?></td></tr>
                <tr><th>Server Software</th><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td></tr>
                <tr><th>Document Root</th><td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td></tr>
                <tr><th>Current Script</th><td><?php echo $_SERVER['SCRIPT_NAME'] ?? 'Unknown'; ?></td></tr>
                <tr><th>HTTP Host</th><td><?php echo $_SERVER['HTTP_HOST'] ?? 'Unknown'; ?></td></tr>
                <tr><th>HTTPS</th><td><?php echo isset($_SERVER['HTTPS']) ? 'Yes' : 'No'; ?></td></tr>
            </table>
        </div>

        <div class="section">
            <h2>File System Check</h2>
            <?php
            $required_files = ['database.php', 'user.php', 'logger.php'];
            foreach ($required_files as $file) {
                if (file_exists($file)) {
                    echo '<div class="status success">✅ ' . $file . ' exists</div>';
                } else {
                    echo '<div class="status error">❌ ' . $file . ' not found</div>';
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>Error Logs</h2>
            <?php
            $log_files = ['error.log', 'php_errors.log', 'payment_logs.txt', 'transaction_log.txt'];
            foreach ($log_files as $log_file) {
                if (file_exists($log_file)) {
                    echo '<h3>' . $log_file . '</h3>';
                    $content = file_get_contents($log_file);
                    $lines = explode("\n", $content);
                    $recent_lines = array_slice($lines, -10); // Last 10 lines
                    echo '<div class="code-block">' . htmlspecialchars(implode("\n", $recent_lines)) . '</div>';
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>Actions</h2>
            <a href="?" class="btn">Refresh Debug Info</a>
            <a href="login.php" class="btn">Go to Login</a>
            <a href="payment.php" class="btn">Go to Payment</a>
            <a href="logout.php" class="btn">Logout</a>
        </div>

        <div style="margin-top: 30px; font-size: 12px; color: #666;">
            <p>Generated at: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>Important:</strong> Remove this debug file from production server for security.</p>
        </div>
    </div>
</body>
</html>