<?php
session_start();
require_once "../../includes/config.php";

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Lesson ID is required']);
    exit;
}

try {
    $lesson_id = $_POST['id'];
    
    // Get file path before deleting
    $query = "SELECT file_path FROM lessons WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $lesson_id]);
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lesson) {
        // Delete file
        $file_path = "../../" . $lesson['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete from database
        $query = "DELETE FROM lessons WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $lesson_id]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lesson not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
