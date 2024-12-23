<?php
session_start();
require_once "../../includes/config.php";

header('Content-Type: application/json');

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

try {
    // Check if ID is provided
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception("មិនមានលេខសម្គាល់មុខវិជ្ជា");
    }

    $id = (int)$_POST['id'];

    // Begin transaction
    $pdo->beginTransaction();

    // Check if subject exists and get its details
    $stmt = $pdo->prepare("SELECT subject_code, subject_name FROM subjects WHERE id = ?");
    $stmt->execute([$id]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$subject) {
        throw new Exception("រកមិនឃើញមុខវិជ្ជានេះទេ");
    }

    // Delete the subject
    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$id]);

    // Commit transaction
    $pdo->commit();

    $response['success'] = true;
    $response['message'] = "មុខវិជ្ជា {$subject['subject_name']} ត្រូវបានលុបដោយជោគជ័យ";

} catch (Exception $e) {
    // Rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    // Rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response['message'] = "មានបញ្ហាក្នុងការលុបមុខវិជ្ជា: " . $e->getMessage();
}

echo json_encode($response);
