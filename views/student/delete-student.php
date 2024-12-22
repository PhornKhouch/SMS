<?php
session_start();
require_once "../../includes/config.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "សិស្សមិនត្រូវបានរកឃើញទេ";
    header("Location: student-list.php");
    exit();
}

try {
    // Get student photo before deletion
    $stmt = $pdo->prepare("SELECT photo FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete student record
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    // Delete photo if exists
    if ($student && !empty($student['photo'])) {
        $photo_path = "../../uploads/students/" . $student['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    $_SESSION['success_message'] = "សិស្សត្រូវបានលុបដោយជោគជ័យ";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការលុបសិស្ស៖ " . $e->getMessage();
}

header("Location: student-list.php");
exit();
