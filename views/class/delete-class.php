<?php
session_start();
require_once "../../includes/config.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ថ្នាក់រៀនមិនត្រូវបានរកឃើញទេ";
    header("Location: class-list.php");
    exit();
}

try {
    // Get class details before deletion
    $stmt = $pdo->prepare("SELECT class_name FROM classes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
        $_SESSION['error_message'] = "ថ្នាក់រៀនមិនត្រូវបានរកឃើញទេ";
        header("Location: class-list.php");
        exit();
    }

    // Delete class record
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$_GET['id']]);

    $_SESSION['success_message'] = "ថ្នាក់រៀនត្រូវបានលុបដោយជោគជ័យ";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការលុបថ្នាក់រៀន៖ " . $e->getMessage();
}

header("Location: class-list.php");
exit();
?>
