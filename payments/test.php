<?php
// Basic PHP test
echo "PHP is working!<br>";

// Check if files exist
$files = ['database.php', 'user.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file is MISSING<br>";
    }
}

// Test database.php inclusion
echo "<br>Testing database.php inclusion...<br>";
try {
    require_once 'database.php';
    echo "✅ database.php included successfully<br>";
    
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        echo "✅ Database connection successful<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Error with database.php: " . $e->getMessage() . "<br>";
}

// Test user.php inclusion
echo "<br>Testing user.php inclusion...<br>";
try {
    require_once 'user.php';
    echo "✅ user.php included successfully<br>";
} catch (Exception $e) {
    echo "❌ Error with user.php: " . $e->getMessage() . "<br>";
}

// Test session start
echo "<br>Testing session...<br>";
if (session_status() === PHP_SESSION_NONE) {
    if (session_start()) {
        echo "✅ Session started successfully<br>";
        echo "Session ID: " . session_id() . "<br>";
    } else {
        echo "❌ Failed to start session<br>";
    }
} else {
    echo "✅ Session already active<br>";
}

// Show PHP version and settings
echo "<br>PHP Info:<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Error Reporting: " . error_reporting() . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";

// Check for recent errors
if (function_exists('error_get_last')) {
    $lastError = error_get_last();
    if ($lastError) {
        echo "<br>Last PHP Error:<br>";
        echo "Type: " . $lastError['type'] . "<br>";
        echo "Message: " . $lastError['message'] . "<br>";
        echo "File: " . $lastError['file'] . "<br>";
        echo "Line: " . $lastError['line'] . "<br>";
    }
}
?>

<h2>If you see this, PHP is working!</h2>
<p>Now we can identify what's preventing login.php from loading.</p>