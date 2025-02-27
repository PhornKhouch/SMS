<?php
session_start();
require_once "../../includes/config.php";

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate input
$ids = isset($_POST['ids']) ? $_POST['ids'] : [];
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (empty($ids) || !is_array($ids)) {
    echo json_encode(['success' => false, 'message' => 'No students selected']);
    exit;
}

if (!in_array($status, ['Active', 'Inactive'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Prepare the update query
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $query = "UPDATE students SET status = ? WHERE id IN ($placeholders)";
    
    // Prepare statement
    $stmt = $pdo->prepare($query);
    
    // Create array of parameters with status as first parameter
    $params = array_merge([$status], $ids);
    
    // Execute the update
    $stmt->execute($params);
    
    // Check if any rows were affected
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No records were updated']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
