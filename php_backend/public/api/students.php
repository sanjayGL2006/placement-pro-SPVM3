<?php
require_once __DIR__ . '/../../src/Config/Database.php';
require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../src/Models/Student.php';
require_once __DIR__ . '/../../src/Controllers/StudentController.php';

use PlacementPro\Controllers\StudentController;

$controller = new StudentController();
$controller->handleRequest();
