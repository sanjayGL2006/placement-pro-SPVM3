<?php
require_once 'config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['token']) || !isset($input['user'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$_SESSION['token'] = $input['token'];
$_SESSION['user'] = $input['user'];
echo json_encode(['ok' => true]);
