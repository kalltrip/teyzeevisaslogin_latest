<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === CONFIG: DB DETAILS ===
$host = "localhost";
$db_name = 'visas_db';
$username = 'devuser';
$password = 'webcoder01@2905';

$error = "";
$success = "";

// === PROCESS FORM SUBMISSIONS ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $inputPassword = trim($_POST['password'] ?? '');

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action === 'login') {
            // === HANDLE LOGIN ===
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($inputPassword, $user['password'])) {
                    // Login success — store session
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['user_id'] = $user['id'] ?? null;
                    
                    if (headers_sent($file, $line)) {
                        die("Headers already sent in $file on line $line");
                    }
                    header("Location: https://teyzeevisas.com/payments/payment.php");
                    exit;
                } else {
                    $error = "Incorrect password.";
                }
            } else {
                $error = "User not found.";
            }

        } elseif ($action === 'register') {
            // === HANDLE REGISTRATION ===
            
            // Validate input
            if (empty($email) || empty($inputPassword)) {
                $error = "Email and password are required.";
            } elseif (strlen($inputPassword) < 6) {
                $error = "Password must be at least 6 characters long.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = :email");
                $stmt->bindParam(":email", $email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $error = "An account with this email already exists. Please try logging in instead.";
                } else {
                    // Hash password and create new user
                    $hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO customers (email, password, created_at) VALUES (:email, :password, NOW())");
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":password", $hashedPassword);
                    
                    if ($stmt->execute()) {
                        $success = "Successfully registered! You can now log in with your credentials.";
                        // Clear form data after successful registration
                        $email = '';
                        $inputPassword = '';
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        }

    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - Teyzee Visas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .logo {
            text-align: center;
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 30px;
        }
        
        .auth-tabs {
            display: flex;
            margin-bottom: 30px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #f0f0f0;
        }
        
        .tab-button {
            flex: 1;
            padding: 12px;
            background: #f8f9ff;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
            color: #666;
        }
        
        .tab-button.active {
            background: #667eea;
            color: white;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo">Teyzee Visas</div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="auth-tabs">
            <button class="tab-button active" onclick="switchTab('login')">Login</button>
            <button class="tab-button" onclick="switchTab('register')">Register</button>
        </div>
        
        <!-- Login Form -->
        <div id="login-form" class="tab-content active">
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="login-email">Email Address</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
        
        <!-- Register Form -->
        <div id="register-form" class="tab-content">
            <form method="POST" action="">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="register-email">Email Address</label>
                    <input type="email" id="register-email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="password" required minlength="6">
                    <div class="password-requirements">
                        Password must be at least 6 characters long
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Create Account</button>
            </form>
        </div>
        
        <div class="back-link">
            <a href="/index.php">← Back to Home</a>

        </div>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-form').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>