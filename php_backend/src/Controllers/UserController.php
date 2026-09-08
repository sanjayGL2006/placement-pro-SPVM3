<?php

namespace PlacementPro\Controllers;

use PlacementPro\Config\Database;
use PlacementPro\Middleware\AuthMiddleware;
use PDO;

class UserController {
    
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function handleRequest() {
        AuthMiddleware::requireLogin();
        // Only Principal can manage users
        AuthMiddleware::requireRole(['principal']);
        
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Basic routing logic
        if ($method === 'GET') {
            $this->listUsers();
        } elseif ($method === 'POST') {
            $this->createUser();
        } elseif ($method === 'DELETE' && preg_match('#^/api/users/([0-9]+)$#', $path, $matches)) {
            $this->deleteUser($matches[1]);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
        }
    }
    
    private function listUsers() {
        header('Content-Type: application/json');
        
        $college_id = $_SESSION['college_id'];
        
        $stmt = $this->db->prepare("
            SELECT u.id, u.email, u.is_active, u.created_at, r.name as role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.college_id = :college_id AND u.deleted_at IS NULL
            ORDER BY u.created_at DESC
        ");
        $stmt->execute(['college_id' => $college_id]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['data' => $users]);
    }
    
    private function createUser() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $email = filter_var($input['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $input['password'] ?? '';
        $roleName = $input['role'] ?? '';
        
        if (empty($email) || empty($password) || empty($roleName)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email, password, and role are required.']);
            return;
        }
        
        $college_id = $_SESSION['college_id'];
        
        // Find role_id
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = :role_name");
        $stmt->execute(['role_name' => $roleName]);
        $role = $stmt->fetch();
        
        if (!$role) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role.']);
            return;
        }
        
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND deleted_at IS NULL");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'User with this email already exists.']);
            return;
        }
        
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password_hash, role_id, college_id, is_active, created_at, updated_at) 
            VALUES (:email, :password_hash, :role_id, :college_id, true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        
        $success = $stmt->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
            'role_id' => $role['id'],
            'college_id' => $college_id
        ]);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully.',
                'id' => $this->db->lastInsertId()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user.']);
        }
    }
    
    private function deleteUser($id) {
        header('Content-Type: application/json');
        
        $college_id = $_SESSION['college_id'];
        
        // Prevent deleting oneself
        if ($id == $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'You cannot delete your own account.']);
            return;
        }
        
        // Soft delete
        $stmt = $this->db->prepare("
            UPDATE users 
            SET deleted_at = CURRENT_TIMESTAMP, is_active = false 
            WHERE id = :id AND college_id = :college_id
        ");
        
        $stmt->execute([
            'id' => $id,
            'college_id' => $college_id
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found or not authorized.']);
        }
    }
}
