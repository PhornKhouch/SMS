<?php
// Start session and include required files
require_once "../../includes/config.php";
require_once "../../includes/auth.php";

// Ensure user is logged in and is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "អត្តសញ្ញាណអ្នកប្រើប្រាស់មិនត្រូវបានផ្តល់ឱ្យ";
    header('Location: list-settings.php');
    exit();
}

$user_id = (int)$_GET['id'];

try {
    // Check if user exists
    $check_stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $check_stmt->execute([$user_id]);
    
    if (!$check_stmt->fetch()) {
        $_SESSION['error_message'] = "រកមិនឃើញអ្នកប្រើប្រាស់";
        header('Location: list-settings.php');
        exit();
    }

    // Delete the user
    $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->execute([$user_id]);

    $_SESSION['success_message'] = "អ្នកប្រើប្រាស់ត្រូវបានលុបដោយជោគជ័យ";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការលុបអ្នកប្រើប្រាស់: " . $e->getMessage();
}

// Redirect back to the settings page
header('Location: list-settings.php');
exit();
