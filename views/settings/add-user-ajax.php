<?php
require_once "../../includes/config.php";
require_once "../../includes/auth.php";

// Ensure user is logged in and is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'អ្នកមិនមានសិទ្ធិគ្រប់គ្រាន់']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'សំណើមិនត្រឹមត្រូវ']);
    exit();
}

try {
    // Get form data
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    $status = trim($_POST['status']);

    // Validate required fields
    if (empty($username) || empty($full_name) || empty($email) || empty($password) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'សូមបំពេញព័ត៌មានទាំងអស់']);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ']);
        exit();
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'ឈ្មោះអ្នកប្រើប្រាស់មានរួចហើយ']);
        exit();
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'អ៊ីមែលនេះមានរួចហើយ']);
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, full_name, email, password, role, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $username,
        $full_name,
        $email,
        $hashed_password,
        $role,
        $status
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'មានបញ្ហាក្នុងការបន្ថែមអ្នកប្រើប្រាស់']);
}
