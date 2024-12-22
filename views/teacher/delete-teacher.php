<?php
session_start();
require_once "../../includes/config.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "គ្រូបង្រៀនមិនត្រូវបានរកឃើញទេ";
    header("Location: teacher-list.php");
    exit();
}

try {
    // Get teacher photo before deletion
    $stmt = $pdo->prepare("SELECT photo FROM teachers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete teacher record
    $stmt = $pdo->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    // Delete photo if exists
    if ($teacher && !empty($teacher['photo'])) {
        $photo_path = "../../uploads/teachers/" . $teacher['photo'];
        if (file_exists($photo_path)) {
            unlink($photo_path);
        }
    }

    $_SESSION['success_message'] = "គ្រូបង្រៀនត្រូវបានលុបដោយជោគជ័យ";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការលុបគ្រូបង្រៀន៖ " . $e->getMessage();
}

header("Location: teacher-list.php");
exit();
