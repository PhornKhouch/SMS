<?php
session_start();
require_once "../../includes/config.php";

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception("មិនមានលេខសម្គាល់មុខវិជ្ជា");
    }

    $id = (int)$_GET['id'];

    // Get subject details with teacher name
    $sql = "SELECT s.*, t.name as teacher_name 
            FROM subjects s 
            LEFT JOIN teachers t ON s.teacher_id = t.id 
            WHERE s.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subject) {
        throw new Exception("រកមិនឃើញមុខវិជ្ជានេះទេ");
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'subject' => $subject
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
