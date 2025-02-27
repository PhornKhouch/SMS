<?php
session_start();
require_once "../../includes/config.php";

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create certificates table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            certificate_no VARCHAR(50) NOT NULL UNIQUE,
            issue_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id),
            FOREIGN KEY (course_id) REFERENCES subjects(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($create_table_sql);

        // Get POST data
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $issue_date = $_POST['issue_date'];
        $certificate_no = $_POST['certificate_no'];

        // Validate inputs
        if (empty($student_id) || empty($course_id) || empty($issue_date) || empty($certificate_no)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        // Check if certificate number already exists
        $check_sql = "SELECT id FROM certificates WHERE certificate_no = :certificate_no";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->bindParam(':certificate_no', $certificate_no);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Certificate number already exists']);
            exit;
        }

        // Insert certificate data
        $insert_sql = "INSERT INTO certificates (student_id, course_id, certificate_no, issue_date) 
                      VALUES (:student_id, :course_id, :certificate_no, :issue_date)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->bindParam(':student_id', $student_id);
        $insert_stmt->bindParam(':course_id', $course_id);
        $insert_stmt->bindParam(':certificate_no', $certificate_no);
        $insert_stmt->bindParam(':issue_date', $issue_date);

        if ($insert_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Certificate saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error saving certificate']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
