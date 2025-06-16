<?php
class User {
    private $conn;

    public function __construct($database) {
        $this->conn = $database;
    }

    public function register($email, $password, $full_name = null, $phone = null, $address = null) {
        try {
            // Check for existing user
            $stmt = $this->conn->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }

            // Securely hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into DB
            $stmt = $this->conn->prepare("INSERT INTO customers (email, password, full_name, phone, address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $result = $stmt->execute([
                $email,
                $hashed_password,
                $full_name,
                $phone,
                $address
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Registration successful'];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration error'];
        }
    }


    public function login($email, $password) {
        try {
            // Fetch user record
            $stmt = $this->conn->prepare("SELECT * FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validate password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            // Create session token
            $session_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // Insert new session
            $stmt = $this->conn->prepare("INSERT INTO login_sessions (user_id, session_token, expires_at, created_at, ip_address, user_agent) VALUES (?, ?, ?, NOW(), ?, ?)");
            $stmt->execute([$user['id'], $session_token, $expires_at, $ip_address, $user_agent]);

            // Remove password before returning
            unset($user['password']);

            return [
                'success' => true,
                'session_token' => $session_token,
                'user' => $user
            ];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login error'];
        }
    }

    public function validateSession($session_token) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.*, ls.expires_at 
                FROM customers u
                JOIN login_sessions ls ON u.id = ls.user_id
                WHERE ls.session_token = ? AND ls.expires_at > NOW()
            ");
            $stmt->execute([$session_token]);
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                unset($user['password']);
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid or expired session'];
            }
        } catch (Exception $e) {
            error_log("Session validation error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Session validation error'];
        }
    }
public function logout($session_token) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM login_sessions WHERE session_token = ?");
            $stmt->execute([$session_token]);
            return ['success' => true, 'message' => 'Logged out successfully'];
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Logout error'];
        }
    }

    public function cleanExpiredSessions() {
        try {
            $stmt = $this->conn->prepare("DELETE FROM login_sessions WHERE expires_at <= NOW()");
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            error_log("Clean expired sessions error: " . $e->getMessage());
            return false;
        }
    }
}
?>