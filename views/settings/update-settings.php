<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $theme = $_POST['theme'];
    $language = $_POST['language'];
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;

    // Validate theme
    if (!in_array($theme, ['light', 'dark'])) {
        $_SESSION['message'] = 'Invalid theme selected.';
        $_SESSION['message_type'] = 'danger';
        header('Location: list-settings.php');
        exit();
    }

    // Validate language
    if (!in_array($language, ['en', 'km'])) {
        $_SESSION['message'] = 'Invalid language selected.';
        $_SESSION['message_type'] = 'danger';
        header('Location: list-settings.php');
        exit();
    }

    try {
        $stmt = $conn->prepare("
            UPDATE user_settings 
            SET theme = ?, 
                language = ?, 
                notifications = ?, 
                email_notifications = ? 
            WHERE user_id = ?
        ");
        
        $stmt->bind_param("ssiii", 
            $theme, 
            $language, 
            $notifications, 
            $emailNotifications, 
            $userId
        );

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Settings updated successfully!';
            $_SESSION['message_type'] = 'success';
        } else {
            throw new Exception('Failed to update settings.');
        }

    } catch (Exception $e) {
        $_SESSION['message'] = 'Error updating settings: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }

    header('Location: list-settings.php');
    exit();
}

// If not POST request, redirect to settings page
header('Location: list-settings.php');
exit();
