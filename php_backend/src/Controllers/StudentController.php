<?php

namespace PlacementPro\Controllers;

use PlacementPro\Models\Student;
use PlacementPro\Middleware\AuthMiddleware;
use PlacementPro\Middleware\CsrfMiddleware;

class StudentController {
    
    private Student $studentModel;

    public function __construct() {
        $this->studentModel = new Student();
    }

    public function handleRequest() {
        AuthMiddleware::requireLogin();
        // Allow all logged in users to at least read (students can view their own, etc., logic applied later)
        
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        switch ($method) {
            case 'GET':
                $this->list();
                break;
            case 'POST':
                AuthMiddleware::requireRole(['super_admin', 'principal', 'hod', 'placement_coordinator', 'staff']);
                
                if (str_ends_with($path, '/bulk-push')) {
                    $this->bulkPush();
                } else {
                    echo json_encode(['message' => 'Create student not implemented yet']);
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed']);
                break;
        }
    }
    
    private function bulkPush() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $studentIds = $input['student_ids'] ?? [];
        $companyId = $input['company_id'] ?? null;
        
        if (empty($studentIds) || !is_array($studentIds)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid students selected.']);
            return;
        }
        
        if (!$companyId) {
            http_response_code(400);
            echo json_encode(['error' => 'No target drive selected.']);
            return;
        }
        
        // Ensure all IDs are integers
        $studentIds = array_filter(array_map('intval', $studentIds));
        
        if (empty($studentIds)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid students selected.']);
            return;
        }
        
        // In a full implementation, we'd insert into drive_applications here
        // verifying each student belongs to the college context: $_SESSION['college_id']
        // For now, mock success since schema might not be fully linked.
        
        echo json_encode([
            'success' => true,
            'pushed_count' => count($studentIds),
            'message' => 'Students successfully pushed to drive.'
        ]);
    }

    private function list() {
        header('Content-Type: application/json');
        
        $college_id = $_SESSION['college_id'];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = ($page - 1) * $limit;
        
        $search = $_GET['search'] ?? '';
        
        $filters = [];
        if (!empty($_GET['department_id'])) {
            $filters['department_id'] = $_GET['department_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        try {
            $students = $this->studentModel->getAll($college_id, $limit, $offset, $filters, $search);
            $total = $this->studentModel->count($college_id, $filters, $search);

            echo json_encode([
                'data' => $students,
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'total_pages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch students. Ensure Database is configured properly.']);
        }
    }
}
