<?php

namespace PlacementPro\Controllers;

use PlacementPro\Config\Database;
use PlacementPro\Middleware\AuthMiddleware;
use PlacementPro\Middleware\CsrfMiddleware;
use PDO;
use Exception;

class AuthController {
    
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_TIME_MINUTES = 15;

    public function login(): void {
        header('Content-Type: application/json');
        AuthMiddleware::startSession();
        // CsrfMiddleware::validateToken(); // Disabled for login route currently

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['error' => 'Email and password are required.']);
            http_response_code(400);
            return;
        }

        $ip_address = $_SERVER['REMOTE_ADDR'];
        $db = Database::getConnection();

        // 1. Check Rate Limiting / Lockout
        if ($this->isRateLimited($db, $email, $ip_address)) {
            echo json_encode(['error' => 'Too many failed login attempts. Please try again later.']);
            http_response_code(429);
            return;
        }

        // 2. Fetch User and Role
        $stmt = $db->prepare("
            SELECT u.id, u.password_hash, u.is_active, u.college_id, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email AND u.deleted_at IS NULL
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // 3. Verify Password
        if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
            // Success
            $this->logAttempt($db, $email, $ip_address, true);

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['college_id'] = $user['college_id'];
            $_SESSION['LAST_ACTIVITY'] = time();

            // Update last login timestamp
            $updateStmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
            $updateStmt->execute(['id' => $user['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => bin2hex(random_bytes(16)), // Generate a session token
                'user' => [
                    'id' => $user['id'],
                    'email' => $email,
                    'role' => $user['role_name'],
                    'college_id' => $user['college_id']
                ]
            ]);
            return;
        }

        // 4. Failed Login
        $this->logAttempt($db, $email, $ip_address, false);
        echo json_encode(['error' => 'Invalid email or password.']);
        http_response_code(401);
    }

    public function logout(): void {
        AuthMiddleware::startSession();
        
        // Unset all session variables
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
    }
    
    public function changePassword(): void {
        header('Content-Type: application/json');
        AuthMiddleware::requireLogin();
        AuthMiddleware::requireRole(['principal', 'hod']);
        // CsrfMiddleware::validateToken();

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['error' => 'Current and new passwords are required.']);
            http_response_code(400);
            return;
        }

        if (strlen($newPassword) < 8) {
            echo json_encode(['error' => 'New password must be at least 8 characters long.']);
            http_response_code(400);
            return;
        }

        $userId = $_SESSION['user_id'];
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            echo json_encode(['error' => 'Incorrect current password.']);
            http_response_code(401);
            return;
        }

        $newHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $updateStmt = $db->prepare("UPDATE users SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $updateStmt->execute(['hash' => $newHash, 'id' => $userId]);

        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    }

    private function isRateLimited(PDO $db, string $email, string $ip_address): bool {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM login_attempts 
            WHERE email = :email 
              AND ip_address = :ip_address 
              AND success = false 
              AND attempt_time > (CURRENT_TIMESTAMP - INTERVAL '" . self::LOCKOUT_TIME_MINUTES . " minutes')
        ");
        $stmt->execute(['email' => $email, 'ip_address' => $ip_address]);
        $attempts = $stmt->fetchColumn();

        return $attempts >= self::MAX_LOGIN_ATTEMPTS;
    }

    private function logAttempt(PDO $db, string $email, string $ip_address, bool $success): void {
        $stmt = $db->prepare("
            INSERT INTO login_attempts (email, ip_address, success)
            VALUES (:email, :ip_address, :success)
        ");
        $stmt->execute([
            'email' => $email,
            'ip_address' => $ip_address,
            'success' => $success ? 1 : 0
        ]);
    }
}
