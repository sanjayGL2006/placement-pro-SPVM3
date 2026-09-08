<?php

namespace PlacementPro\Middleware;

use Exception;

class CsrfMiddleware {
    
    /**
     * Generate a CSRF token for the current session.
     */
    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                // Fallback if random_bytes fails, though very unlikely in PHP 7/8
                $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
            }
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate the CSRF token from the request.
     */
    public static function validateToken(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $headers = apache_request_headers();
            $token = $_POST['csrf_token'] ?? $headers['X-CSRF-Token'] ?? '';
            
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid CSRF token. Please refresh the page and try again.']);
                exit;
            }
        }
    }
}
