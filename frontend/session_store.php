<?php
require_once 'config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['token']) || !isset($input['user'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$token = $input['token'];

// Validate mock-jwt token format and embedded payload
if (strpos($token, 'mock-jwt-') === 0) {
    $encoded = substr($token, strlen('mock-jwt-'));
    $payload = json_decode(base64_decode($encoded), true);

    if (!$payload || !isset($payload['email']) || !isset($payload['exp'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Malformed token']);
        exit;
    }

    if ($payload['exp'] < time()) {
        http_response_code(401);
        echo json_encode(['error' => 'Token expired']);
        exit;
    }

    // Cross-check the user email matches what is encoded in the token
    $userEmail = $input['user']['email'] ?? '';
    if ($payload['email'] !== $userEmail) {
        http_response_code(401);
        echo json_encode(['error' => 'Token/user mismatch']);
        exit;
    }
} else {
    // Reject anything that isn't a recognised token format
    http_response_code(401);
    echo json_encode(['error' => 'Unrecognised token format']);
    exit;
}

$_SESSION['token'] = $token;
$_SESSION['user']  = $input['user'];
echo json_encode(['ok' => true]);
