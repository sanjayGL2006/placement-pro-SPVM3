<?php
require_once __DIR__ . '/src/Config/Database.php';

use PlacementPro\Config\Database;

echo "Starting Database Setup...\n";

try {
    $db = Database::getConnection();
    echo "Connected to PostgreSQL successfully.\n";

    // 1. Run Schema Migration
    echo "Skipping Schema (handled by Docker init)...\n";
    // $schema = file_get_contents(__DIR__ . '/database/migrations/001_initial_schema.sql');
    // $db->exec($schema);
    // echo "Schema applied successfully.\n";

    // 2. Seed Initial Roles
    echo "Seeding Roles...\n";
    $roles = ['super_admin', 'principal', 'hod', 'placement_coordinator', 'staff', 'student'];
    $roleIds = [];
    $stmt = $db->prepare("INSERT INTO roles (name) VALUES (:name) ON CONFLICT (name) DO NOTHING RETURNING id, name");
    foreach ($roles as $role) {
        $stmt->execute(['name' => $role]);
        $result = $stmt->fetch();
        if ($result) {
            $roleIds[$role] = $result['id'];
        }
    }
    
    // Fetch role IDs if they already existed
    $stmt = $db->query("SELECT id, name FROM roles");
    while ($row = $stmt->fetch()) {
        $roleIds[$row['name']] = $row['id'];
    }

    // 3. Seed College
    echo "Seeding College...\n";
    $stmt = $db->prepare("INSERT INTO colleges (name, code) VALUES ('PES Institute of Advanced Management Studies', 'PESIAMS') ON CONFLICT (code) DO NOTHING RETURNING id");
    $stmt->execute();
    $college = $stmt->fetch();
    if (!$college) {
        $stmt = $db->query("SELECT id FROM colleges WHERE code = 'PESIAMS'");
        $college = $stmt->fetch();
    }
    $collegeId = $college['id'];

    // 4. Seed Coordinator User
    echo "Seeding Users...\n";
    $email = 'coordinator@pesiams.edu.in';
    $password = 'Coordinator@2026';
    $hash = password_hash($password, PASSWORD_ARGON2ID);
    
    // Check if exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if (!$stmt->fetch()) {
        $insertUser = $db->prepare("
            INSERT INTO users (email, password_hash, role_id, college_id, first_name, last_name)
            VALUES (:email, :hash, :role_id, :college_id, 'Placement', 'Coordinator')
        ");
        $insertUser->execute([
            'email' => $email,
            'hash' => $hash,
            'role_id' => $roleIds['placement_coordinator'],
            'college_id' => $collegeId
        ]);
        echo "Coordinator user created!\n";
    } else {
        echo "Coordinator user already exists.\n";
    }
    
    // Seed Staff User
    $staffEmail = 'staff.bca@pesiams.edu.in';
    $stmt->execute(['email' => $staffEmail]);
    if (!$stmt->fetch()) {
        $insertUser = $db->prepare("
            INSERT INTO users (email, password_hash, role_id, college_id, first_name, last_name)
            VALUES (:email, :hash, :role_id, :college_id, 'Department', 'Staff')
        ");
        $insertUser->execute([
            'email' => $staffEmail,
            'hash' => password_hash('Staff@2026', PASSWORD_ARGON2ID),
            'role_id' => $roleIds['staff'],
            'college_id' => $collegeId
        ]);
        echo "Staff user created!\n";
    }

    // Seed Principal User
    $principalEmail = 'principal@pesiams.edu.in';
    $stmt->execute(['email' => $principalEmail]);
    if (!$stmt->fetch()) {
        $insertUser = $db->prepare("
            INSERT INTO users (email, password_hash, role_id, college_id, first_name, last_name)
            VALUES (:email, :hash, :role_id, :college_id, 'College', 'Principal')
        ");
        $insertUser->execute([
            'email' => $principalEmail,
            'hash' => password_hash('Principal@2026', PASSWORD_ARGON2ID),
            'role_id' => $roleIds['principal'],
            'college_id' => $collegeId
        ]);
        echo "Principal user created!\n";
    }

    echo "\nSetup Complete! You can now log in.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Make sure PostgreSQL is installed, running on port 5432, and the 'postgres' user has a blank password (or update .env / Database.php).\n";
}
