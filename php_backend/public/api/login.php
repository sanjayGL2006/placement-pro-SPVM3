<?php
// Autoloader setup (in a real app, use Composer's autoloader)
require_once __DIR__ . '/../../src/Config/Database.php';
require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

use PlacementPro\Controllers\AuthController;

// Handle CORS if needed, and strict methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$controller = new AuthController();
$controller->login();
