<?php
session_start();
require_once "../../includes/config.php";

header('Content-Type: application/json');

try {
    // Check database connection
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    // Check if lessons table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'lessons'");
    if ($stmt->rowCount() == 0) {
        // Create lessons table
        $sql = "CREATE TABLE IF NOT EXISTS lessons (
            id INT PRIMARY KEY AUTO_INCREMENT,
            subject_id INT NOT NULL,
            lesson_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql);
    }

    // Check uploads directory
    $upload_dir = "../../uploads/lessons/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Check directory permissions
    if (!is_writable($upload_dir)) {
        chmod($upload_dir, 0777);
    }

    // Get PHP upload limits
    $max_upload = ini_get('upload_max_filesize');
    $max_post = ini_get('post_max_size');
    $memory_limit = ini_get('memory_limit');

    echo json_encode([
        'success' => true,
        'message' => 'System check completed successfully',
        'upload_dir_exists' => file_exists($upload_dir),
        'upload_dir_writable' => is_writable($upload_dir),
        'lessons_table_exists' => true,
        'upload_limits' => [
            'upload_max_filesize' => $max_upload,
            'post_max_size' => $max_post,
            'memory_limit' => $memory_limit
        ]
    ]);

} catch (Exception $e) {
    error_log("System check error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
