<?php

// CORS Headers - Allow cross-origin requests from any localhost port
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = false;

// Allow any localhost/127.0.0.1 origin (any port) for local development
if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin)) {
    $allowed = true;
}

// Also allow production domains if set via environment variable
$prodOrigins = getenv('CORS_ORIGINS') ? explode(',', getenv('CORS_ORIGINS')) : [];
if (in_array($origin, $prodOrigins)) {
    $allowed = true;
}

if ($allowed && $origin) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost:7500");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');

// Simple Router
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// ─────────────────────────────────────────────────────────────────────────────
// Check if PostgreSQL is available. If not, run in MOCK MODE.
// ─────────────────────────────────────────────────────────────────────────────
$dbAvailable = false;
if (extension_loaded('pdo_pgsql')) {
    try {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '5432';
        $db   = getenv('DB_NAME') ?: 'placement_pro';
        $user = getenv('DB_USER') ?: 'postgres';
        $pass = getenv('DB_PASS') ?: '';
        $pdo  = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dbAvailable = true;
    } catch (Exception $e) {
        $dbAvailable = false;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// MOCK DATA DEFINITIONS
// ─────────────────────────────────────────────────────────────────────────────
$mockStudents = array_map(fn($i) => [
    'id' => $i,
    'name' => ['Arjun Sharma','Priya Nair','Ravi Kumar','Sneha Patel','Mohammed Ali','Divya Rao','Karthik Iyer','Ananya Singh','Rahul Gupta','Pooja Menon'][$i % 10],
    'email' => "student{$i}@pesiams.edu.in",
    'department' => ['BCA','BBA','B.Com','B.Sc CS','BBA Hospitality'][$i % 5],
    'section' => ['A','B','C'][$i % 3],
    'cgpa' => round(6.5 + ($i % 35) * 0.1, 2),
    'placed' => $i % 3 === 0,
    'skills' => ['Python','Java','React','SQL','Excel'][array_rand(['Python','Java','React','SQL','Excel'])],
    'year' => 2024,
    'phone' => '9' . str_pad($i * 7, 9, '8', STR_PAD_LEFT),
], range(1, 120));

$mockCompanies = [
    ['id' => 1, 'name' => 'Infosys', 'job_role' => 'Systems Engineer', 'package_lpa' => 4.5, 'visit_date' => '2026-09-20', 'status' => 'Upcoming'],
    ['id' => 2, 'name' => 'TCS', 'job_role' => 'Associate Developer', 'package_lpa' => 3.8, 'visit_date' => '2026-10-05', 'status' => 'Upcoming'],
    ['id' => 3, 'name' => 'Wipro', 'job_role' => 'Project Engineer', 'package_lpa' => 4.0, 'visit_date' => '2026-10-15', 'status' => 'Upcoming'],
    ['id' => 4, 'name' => 'Accenture', 'job_role' => 'Associate', 'package_lpa' => 5.0, 'visit_date' => '2026-09-25', 'status' => 'Upcoming'],
    ['id' => 5, 'name' => 'Cognizant', 'job_role' => 'Programmer Analyst', 'package_lpa' => 4.2, 'visit_date' => '2026-11-01', 'status' => 'Upcoming'],
];

// ─────────────────────────────────────────────────────────────────────────────
// ROUTING
// ─────────────────────────────────────────────────────────────────────────────

// ── AUTH ──────────────────────────────────────────────────────────────────────
if (strpos($path, '/api/auth/login') === 0) {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    $accounts = [
        'coordinator@pesiams.edu.in' => ['password' => 'Coordinator@2026', 'role' => 'coordinator', 'name' => 'Placement Coordinator'],
        'principal@pesiams.edu.in'   => ['password' => 'Principal@2026',   'role' => 'principal',   'name' => 'Principal'],
        'hod@pesiams.edu.in'         => ['password' => 'HOD@2026',         'role' => 'hod',         'name' => 'Head of Department'],
        'admin@pesiams.edu.in'       => ['password' => 'Admin@2026',       'role' => 'admin',       'name' => 'System Admin'],
        'staff.bca@pesiams.edu.in'   => ['password' => 'Staff@2026',       'role' => 'faculty',     'name' => 'BCA Staff'],
        'student@pesiams.edu.in'     => ['password' => 'Student@2026',     'role' => 'student',     'name' => 'Student User'],
    ];

    if ($dbAvailable) {
        // Real DB login
        require_once __DIR__ . '/../src/Config/Database.php';
        require_once __DIR__ . '/../src/Controllers/AuthController.php';
        $controller = new PlacementPro\Controllers\AuthController();
        $controller->login();
    } else {
        // Mock login
        if (isset($accounts[$email]) && $accounts[$email]['password'] === $password) {
            $user = $accounts[$email];
            $token = base64_encode(json_encode(['email' => $email, 'role' => $user['role'], 'exp' => time() + 86400]));
            echo json_encode([
                'token' => "mock-jwt-$token",
                'user'  => ['id' => 1, 'name' => $user['name'], 'email' => $email, 'role' => $user['role'], 'department' => null]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials. Check user&pass.txt for valid test accounts.']);
        }
    }

// ── AUTH LOGOUT ───────────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/auth/logout') === 0) {
    echo json_encode(['ok' => true]);

// ── AUTH CHANGE PASSWORD ──────────────────────────────────────────────────────
} elseif (strpos($path, '/api/auth/change-password') === 0) {
    echo json_encode(['ok' => true, 'message' => 'Password updated (mock mode — no DB).']);

// ── STUDENTS ──────────────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/students') === 0) {
    if ($dbAvailable) {
        require_once __DIR__ . '/api/students.php';
    } else {
        $page     = (int)($_GET['page'] ?? 1);
        $perPage  = (int)($_GET['per_page'] ?? 25);
        $search   = strtolower($_GET['search'] ?? '');
        $dept     = $_GET['department'] ?? '';

        $filtered = array_filter($mockStudents, function($s) use ($search, $dept) {
            $matchSearch = !$search || str_contains(strtolower($s['name']), $search) || str_contains(strtolower($s['email']), $search);
            $matchDept   = !$dept || $s['department'] === $dept;
            return $matchSearch && $matchDept;
        });
        $filtered = array_values($filtered);
        $total    = count($filtered);
        $slice    = array_slice($filtered, ($page - 1) * $perPage, $perPage);

        echo json_encode([
            'students'   => $slice,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'total_pages'=> ceil($total / $perPage),
        ]);
    }

// ── COMPANIES ─────────────────────────────────────────────────────────────────
} elseif (preg_match('#^/api/companies#', $path)) {
    if ($dbAvailable) {
        require_once __DIR__ . '/../src/Controllers/CompanyController.php';
        require_once __DIR__ . '/../src/Models/Company.php';
        $controller = new PlacementPro\Controllers\CompanyController();
        $controller->index();
    } else {
        echo json_encode(['companies' => $mockCompanies, 'total' => count($mockCompanies)]);
    }

// ── DASHBOARD STATS ───────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/dashboard/stats') === 0) {
    $placed   = count(array_filter($mockStudents, fn($s) => $s['placed']));
    $total    = count($mockStudents);
    echo json_encode([
        'total_students'   => $total,
        'placed_students'  => $placed,
        'active_companies' => count($mockCompanies),
        'upcoming_drives'  => count(array_filter($mockCompanies, fn($c) => $c['status'] === 'Upcoming')),
        'placement_rate'   => round($placed / $total * 100, 1),
        'mock_mode'        => !$dbAvailable,
    ]);

// ── DASHBOARD FILTERS ─────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/dashboard/filters') === 0) {
    echo json_encode([
        'departments' => ['BCA', 'BBA', 'B.Com', 'B.Sc CS', 'BBA Hospitality'],
        'years'       => [2024, 2025, 2026],
        'sections'    => ['A', 'B', 'C'],
    ]);

// ── SKILL GAP ANALYSIS ────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/skill-gap/analysis') === 0) {
    echo json_encode([
        'recruiter_demand' => [
            ['skill' => 'Python', 'demand' => 85],
            ['skill' => 'SQL', 'demand' => 72],
            ['skill' => 'React', 'demand' => 65],
            ['skill' => 'Java', 'demand' => 60],
            ['skill' => 'Excel', 'demand' => 55],
        ],
        'student_prevalence' => [
            ['skill' => 'Python', 'prevalence' => 45],
            ['skill' => 'SQL', 'prevalence' => 38],
            ['skill' => 'React', 'prevalence' => 22],
            ['skill' => 'Java', 'prevalence' => 55],
            ['skill' => 'Excel', 'prevalence' => 60],
        ],
    ]);

// ── DRIVES / REPEAT ALERTS ────────────────────────────────────────────────────
} elseif (strpos($path, '/api/drives/repeat-alerts') === 0) {
    echo json_encode(['alerts' => []]);

// ── NOTIFICATIONS ─────────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/notifications') === 0) {
    echo json_encode([
        'notifications' => [
            ['id' => 1, 'message' => 'Infosys drive scheduled for Sept 20', 'type' => 'info', 'read' => false, 'created_at' => date('Y-m-d H:i:s')],
            ['id' => 2, 'message' => 'TCS registration deadline tomorrow',  'type' => 'warning', 'read' => false, 'created_at' => date('Y-m-d H:i:s')],
        ],
        'unread_count' => 2,
    ]);

// ── REPORTS / EXPORTS ─────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/reports') === 0) {
    echo json_encode(['message' => 'Report generation requires a connected database.', 'mock_mode' => true]);

// ── IMPORTS ───────────────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/imports') === 0) {
    echo json_encode(['ok' => true, 'message' => 'Import preview accepted (mock mode).', 'rows' => 0]);

// ── RECYCLE BIN / RESET ───────────────────────────────────────────────────────
} elseif (strpos($path, '/api/recycle-bin') === 0) {
    echo json_encode(['ok' => true, 'message' => 'Reset acknowledged (mock mode — no data was changed).']);

// ── USERS ─────────────────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/users') === 0) {
    echo json_encode(['users' => [
        ['id' => 1, 'name' => 'Placement Coordinator', 'email' => 'coordinator@pesiams.edu.in', 'role' => 'coordinator'],
        ['id' => 2, 'name' => 'Principal', 'email' => 'principal@pesiams.edu.in', 'role' => 'principal'],
        ['id' => 3, 'name' => 'System Admin', 'email' => 'admin@pesiams.edu.in', 'role' => 'admin'],
    ]]);

// ── AI HUB & RECOMMENDATIONS ────────────────────────────────────────────────
} elseif (strpos($path, '/api/ai/eligibility-recommendation') === 0) {
    $cid = isset($_GET['company_id']) ? (int)$_GET['company_id'] : 1;
    echo json_encode([
        'success' => true,
        'company_id' => $cid,
        'recommendations' => [
            [
                'student_id' => 1,
                'name' => 'Rahul Sharma',
                'register_number' => '1SP21CS045',
                'cgpa' => '8.70',
                'department' => 'BCA',
                'section' => 'A',
                'is_eligible' => true,
                'fit_score' => 94,
                'matched_skills' => ['Python', 'SQL', 'JavaScript', 'React'],
                'missing_skills' => ['Docker']
            ],
            [
                'student_id' => 2,
                'name' => 'Sneha Patel',
                'register_number' => '1SP21CS089',
                'cgpa' => '8.20',
                'department' => 'BCA',
                'section' => 'B',
                'is_eligible' => true,
                'fit_score' => 88,
                'matched_skills' => ['Python', 'SQL', 'React'],
                'missing_skills' => ['Docker', 'AWS']
            ],
            [
                'student_id' => 3,
                'name' => 'Kiran Kumar',
                'register_number' => '1SP21CS034',
                'cgpa' => '7.80',
                'department' => 'BBA',
                'section' => 'A',
                'is_eligible' => true,
                'fit_score' => 82,
                'matched_skills' => ['SQL', 'JavaScript', 'Communication'],
                'missing_skills' => ['Python', 'Docker']
            ],
            [
                'student_id' => 4,
                'name' => 'Deepak Verma',
                'register_number' => '1SP21CS012',
                'cgpa' => '6.40',
                'department' => 'BCA',
                'section' => 'A',
                'is_eligible' => false,
                'fit_score' => 45,
                'matched_skills' => ['Python'],
                'missing_skills' => ['SQL', 'React', 'Docker']
            ]
        ]
    ]);

} elseif (strpos($path, '/api/ai/chatbot') === 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    $query = strtolower(isset($input['query']) ? $input['query'] : '');
    $resp = "Placement Pro AI Assistant: I can help analyze placement metrics, predict student eligibility, and provide corporate drive interview preparation questions.";
    if (strpos($query, 'eligible') !== false || strpos($query, 'cgpa') !== false) {
        $resp = "Eligibility is computed based on minimum CGPA and active backlogs configured for each campus drive. See the **Drive Recommender** tab for real-time ranked lists.";
    } elseif (strpos($query, 'resume') !== false || strpos($query, 'ats') !== false) {
        $resp = "Our ATS Resume Audit evaluates keyword density, formatting checks, and AI content ratios. Head to the **Resume Analyzer** tab to audit any CV.";
    }
    echo json_encode(['success' => true, 'response' => $resp, 'message' => $resp]);

} elseif (strpos($path, '/api/ai/analyze-resume') === 0) {
    echo json_encode([
        'success' => true,
        'section1_ats' => [
            'ats_score' => 82,
            'detected_skills' => ['Python', 'SQL', 'React', 'Git', 'Data Structures'],
            'keyword_optimization' => [
                ['category' => 'Core Technologies', 'found' => 4, 'total' => 5],
                ['category' => 'Tools & DevOps', 'found' => 2, 'total' => 3],
                ['category' => 'Soft Skills', 'found' => 3, 'total' => 4]
            ],
            'formatting_check' => [
                'overall' => 'pass',
                'checks' => [
                    ['item' => 'Contact Details', 'status' => 'pass'],
                    ['item' => 'Action Verbs', 'status' => 'pass'],
                    ['item' => 'Quantifiable Metrics', 'status' => 'pass']
                ]
            ],
            'critical_fixes' => []
        ],
        'section2_ai' => [
            'ai_content_pct' => 10,
            'human_content_pct' => 90,
            'tone_analysis' => 'Natural technical tone with demonstrated project accomplishments.',
            'phrases_to_rewrite' => []
        ],
        'section3_recruiter' => [
            'readability_impact' => 'Structured hierarchy suitable for campus technical recruiters.',
            'final_verdict' => 'Ready to submit — High ATS Profile'
        ]
    ]);

} elseif (strpos($path, '/api/ai/interview-prep') === 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    $role = isset($input['job_role']) && !empty($input['job_role']) ? $input['job_role'] : 'Software Engineer';
    echo json_encode([
        'success' => true,
        'role' => $role,
        'technical_questions' => [
            [
                'question' => "Explain how you would architect a scalable service for {$role}.",
                'suggested_answer' => 'Discuss component modularity, API design, database indexing, and caching strategy.'
            ],
            [
                'question' => 'How do you profile performance bottlenecks in full-stack web applications?',
                'suggested_answer' => 'Use browser dev tools, database query plans (EXPLAIN), and APM server tracing.'
            ]
        ],
        'hr_questions' => [
            [
                'question' => 'Describe a situation where you resolved a technical conflict within your project team.',
                'tip' => 'Use the STAR method emphasizing empathy, collaboration, and objective technical evaluation.'
            ]
        ]
    ]);

// ── HEALTH CHECK ──────────────────────────────────────────────────────────────
} elseif (strpos($path, '/api/health') === 0) {
    echo json_encode(['status' => 'ok', 'db' => $dbAvailable ? 'connected' : 'mock_mode', 'version' => '2.0']);

// ── 404 ───────────────────────────────────────────────────────────────────────
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found', 'path' => $path]);
}
