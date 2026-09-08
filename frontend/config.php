<?php
// config.php — shared settings for the PHP frontend
$host = isset($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : 'localhost';
$envApiBase = getenv('PLACEMENT_API_BASE') ?: ($_ENV['PLACEMENT_API_BASE'] ?? ($_SERVER['PLACEMENT_API_BASE'] ?? null));
define('API_BASE', $envApiBase ?: 'http://' . $host . ':5500/api');


session_start();

function is_logged_in() {
    return isset($_SESSION['token']) && isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_role($roles) {
    require_login();
    // Normalize to lowercase for case-insensitive comparison
    $userRole = strtolower($_SESSION['user']['role'] ?? '');
    $allowedRoles = array_map('strtolower', $roles);
    if (!in_array($userRole, $allowedRoles)) {
        http_response_code(403);
        die('Access denied for role: ' . htmlspecialchars($_SESSION['user']['role'] ?? 'unknown'));
    }
}
