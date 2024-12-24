<?php
session_start();
require_once "../../includes/config.php";

header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception("មិនមានលេខសម្គាល់ការបង់ប្រាក់");
    }

    $id = (int)$_POST['id'];

    // Start transaction
    $pdo->beginTransaction();

    // Check if payment exists
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$id]);
    $payment = $stmt->fetch();

    if (!$payment) {
        throw new Exception("រកមិនឃើញព័ត៌មានការបង់ប្រាក់នេះទេ");
    }

    // Delete payment
    $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
    $stmt->execute([$id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'បានលុបព័ត៌មានការបង់ប្រាក់ដោយជោគជ័យ'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
