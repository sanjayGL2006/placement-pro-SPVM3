<?php
require_once __DIR__ . '/../../src/Config/Database.php';

use PlacementPro\Config\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required.']);
    exit;
}

$db = Database::getConnection();

// 1. Check if user exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND is_active = true AND deleted_at IS NULL");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

// To prevent email enumeration, we always return a success message
// even if the email doesn't exist in our system.
if ($user) {
    // 2. Generate Token
    try {
        $token = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $token = bin2hex(openssl_random_pseudo_bytes(32));
    }
    $token_hash = hash('sha256', $token);
    
    // Expire in 1 hour
    $expires_at = date('Y-m-d H:i:s', time() + 3600);
    
    // 3. Store Token
    $insertStmt = $db->prepare("
        INSERT INTO password_resets (user_id, token_hash, expires_at) 
        VALUES (:user_id, :token_hash, :expires_at)
    ");
    $insertStmt->execute([
        'user_id' => $user['id'],
        'token_hash' => $token_hash,
        'expires_at' => $expires_at
    ]);
    
    // 4. Send Email (Mocked here - replace with actual email logic like PHPMailer)
    $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password?token=" . $token . "&email=" . urlencode($email);
    // mail($email, "Password Reset Request", "Click here to reset your password: " . $reset_link);
    error_log("Password reset link for {$email}: {$reset_link}");
}

echo json_encode([
    'success' => true, 
    'message' => 'If an account exists with that email, a password reset link has been sent.'
]);
