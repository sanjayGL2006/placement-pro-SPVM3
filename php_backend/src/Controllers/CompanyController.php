<?php

namespace PlacementPro\Controllers;

use PlacementPro\Config\Database;
use PlacementPro\Models\Company;
use PlacementPro\Middleware\AuthMiddleware;
use Exception;

class CompanyController {
    private Company $companyModel;
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->companyModel = new Company($this->db);
    }

    /**
     * GET /api/companies
     */
    public function index() {
        AuthMiddleware::requireLogin();
        
        $college_id = $_SESSION['college_id'] ?? null;
        if (!$college_id) {
            http_response_code(403);
            echo json_encode(['error' => 'No college context for user.']);
            return;
        }

        try {
            $companies = $this->companyModel->getAll($college_id);
            
            header('Content-Type: application/json');
            echo json_encode($companies);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch companies.', 'details' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/companies/:id
     */
    public function show($id) {
        AuthMiddleware::requireLogin();
        
        $college_id = $_SESSION['college_id'] ?? null;
        
        try {
            $company = $this->companyModel->findById($id, $college_id);
            if (!$company) {
                http_response_code(404);
                echo json_encode(['error' => 'Company not found.']);
                return;
            }
            
            header('Content-Type: application/json');
            echo json_encode($company);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch company.', 'details' => $e->getMessage()]);
        }
    }

    /**
     * POST /api/companies
     */
    public function store() {
        AuthMiddleware::requireLogin();
        // Allow HR, Admin, Coordinator to create companies
        AuthMiddleware::requireRole(['admin', 'hr', 'placement_coordinator', 'principal', 'coordinator']);
        
        $college_id = $_SESSION['college_id'] ?? null;
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Company name is required.']);
            return;
        }

        try {
            $company_id = $this->companyModel->create($input, $college_id);
            $new_company = $this->companyModel->findById($company_id, $college_id);
            
            http_response_code(201);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'company' => $new_company]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create company.', 'details' => $e->getMessage()]);
        }
    }

    /**
     * DELETE /api/companies/:id
     */
    public function destroy($id) {
        AuthMiddleware::requireLogin();
        AuthMiddleware::requireRole(['admin', 'placement_coordinator', 'coordinator']);
        
        $college_id = $_SESSION['college_id'] ?? null;
        
        try {
            $success = $this->companyModel->delete($id, $college_id);
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Company deleted.']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Failed to delete company.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete company.', 'details' => $e->getMessage()]);
        }
    }

    /**
     * GET /api/companies/:id/stats
     */
    public function stats($id) {
        AuthMiddleware::requireLogin();
        
        // Mocking the stats for now as it requires complex funnel logic mapping
        $stats = [
            'total_interested' => rand(100, 300),
            'cleared_aptitude' => rand(50, 100),
            'cleared_technical' => rand(20, 50),
            'cleared_hr' => rand(5, 20),
            'selected' => rand(1, 10)
        ];
        
        header('Content-Type: application/json');
        echo json_encode($stats);
    }
}
