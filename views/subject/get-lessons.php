<?php
session_start();
require_once "../../includes/config.php";

if (!isset($_GET['subject_id'])) {
    echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
    exit;
}

try {
    $subject_id = $_GET['subject_id'];
    
    $query = "SELECT id, lesson_name, file_path, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i') as created_at 
              FROM lessons 
              WHERE subject_id = :subject_id 
              ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':subject_id' => $subject_id]);
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'lessons' => $lessons]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
