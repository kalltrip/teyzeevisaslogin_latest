<?php
// Database connection settings
$servername = "localhost";           // Change this based on your setup
$username = "devuser";               // Your MySQL username
$password = "webcoder01@2905";       // Your MySQL password
$dbname = "test";                    // Database name

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create DB connection with timeout settings
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Set connection timeout (optional)
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    
    echo "✅ Database connection successful!<br><br>";
    
    // SQL query to read records
    $sql = "SELECT name, phone FROM test_cust";
    $result = $conn->query($sql);
    
    // Display results
    if ($result && $result->num_rows > 0) {
        echo "<h3>Customer Records:</h3>";
        while ($row = $result->fetch_assoc()) {
            echo "Name: <strong>" . htmlspecialchars($row["name"]) . "</strong> — Phone: <strong>" . htmlspecialchars($row["phone"]) . "</strong><br>";
        }
    } else {
        echo "No records found in the test_cust table.";
    }
    
} catch (mysqli_sql_exception $e) {
    // Better error handling
    echo "❌ Database Connection Error:<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
    echo "Error Message: " . $e->getMessage() . "<br><br>";
    
    // Provide troubleshooting hints
    echo "<strong>Troubleshooting Tips:</strong><br>";
    echo "1. Check if MySQL service is running<br>";
    echo "2. Verify database credentials<br>";
    echo "3. Ensure database server is accessible<br>";
    echo "4. Check firewall settings<br>";
    
} finally {
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>