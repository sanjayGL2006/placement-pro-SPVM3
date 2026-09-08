<?php

namespace PlacementPro\Middleware;

class AuthMiddleware {
    
    /**
     * Start secure session.
     */
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 0); // Disable for HTTP local dev

            ini_set('session.use_strict_mode', 1);
            
            // For older PHP versions that don't support samesite in session_set_cookie_params
            if (PHP_VERSION_ID < 70300) {
                session_set_cookie_params(86400, '/; samesite=Lax', 'localhost', false, true);
            } else {
                session_set_cookie_params([
                    'lifetime' => 86400,
                    'path' => '/',
                    'domain' => 'localhost',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            session_start();
        }
    }

    /**
     * Require the user to be logged in.
     */
    public static function requireLogin(): void {
        self::startSession();

        if (empty($_SESSION['user_id'])) {
            self::denyAccess('Unauthorized: Please log in.', 401);
        }

        // Check for session timeout (e.g., 2 hours idle)
        $timeout_duration = 7200;
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
            session_unset();
            session_destroy();
            self::denyAccess('Session expired. Please log in again.', 401);
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Require the user to have one of the specified roles.
     * @param array $allowed_roles Array of role names (e.g., ['super_admin', 'hod'])
     */
    public static function requireRole(array $allowed_roles): void {
        self::requireLogin();
        
        $user_role = $_SESSION['role_name'] ?? '';
        
        if (!in_array($user_role, $allowed_roles, true)) {
            self::denyAccess('Forbidden: You do not have permission to access this resource.', 403);
        }
    }

    /**
     * Enforce data isolation by college ID.
     * Ensure the logged-in user can only access data belonging to their college.
     * Super admins are exempt.
     * @param string $requested_college_id
     */
    public static function verifyCollegeScope(string $requested_college_id): void {
        self::requireLogin();
        
        if (($_SESSION['role_name'] ?? '') === 'super_admin') {
            return; // Super admin can access any college data
        }

        $user_college_id = $_SESSION['college_id'] ?? null;
        if ($user_college_id !== $requested_college_id) {
            self::denyAccess('Forbidden: Cross-college data access is strictly prohibited.', 403);
        }
    }

    /**
     * Helper to return a standard JSON error response and exit.
     */
    private static function denyAccess(string $message, int $code): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
}
