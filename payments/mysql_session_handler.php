<?php
class MySQLSessionHandler implements SessionHandlerInterface {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function open($savePath, $sessionName): bool {
        return true;
    }
    
    public function close(): bool {
        return true;
    }
    
    public function read($sessionId): string {
        $stmt = $this->pdo->prepare("
            SELECT session_data FROM login_sessions 
            WHERE id = ? AND expires_at > NOW()
        ");
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['session_data'] : '';
    }
    
    public function write($sessionId, $sessionData): bool {
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiration
        
        // Try to get customer_id from session data if available
        $customerId = null;
        if (isset($_SESSION['customer_id'])) {
            $customerId = $_SESSION['customer_id'];
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO login_sessions (id, customer_id, session_data, expires_at) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            session_data = VALUES(session_data), 
            expires_at = VALUES(expires_at),
            customer_id = COALESCE(VALUES(customer_id), customer_id)
        ");
        
        return $stmt->execute([$sessionId, $customerId, $sessionData, $expiresAt]);
    }
    
    public function destroy($sessionId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM login_sessions WHERE id = ?");
        return $stmt->execute([$sessionId]);
    }
    
    public function gc($maxLifetime): int {
        $stmt = $this->pdo->prepare("DELETE FROM login_sessions WHERE expires_at < NOW()");
        $stmt->execute();
        return $stmt->rowCount();
    }
}