<?php
require_once __DIR__ . '/../../src/Config/Database.php';
require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

use PlacementPro\Controllers\AuthController;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Usually CSRF token is required to logout to prevent CSRF logout attacks
// CsrfMiddleware::validateToken(); // Depending on exact requirements

$controller = new AuthController();
$controller->logout();
