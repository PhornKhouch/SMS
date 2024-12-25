<?php
header('Content-Type: application/json');
session_start();
require_once '../../includes/config.php';

// Function to send JSON response
function sendResponse($success, $message, $redirect = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Debug logging
    error_log("Login attempt - Username: " . $username);
    error_log("POST data: " . print_r($_POST, true));

    // Basic validation
    if (empty($username) || empty($password)) {
        sendResponse(false, "All fields are required");
    }

    try {
        // Test database connection
        error_log("Database connection status: " . ($pdo ? "Connected" : "Not connected"));
        
        // Query debugging
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("Query result: " . print_r($user, true));

        if ($user) {
            error_log("User found, verifying password");
            error_log("Stored hash: " . $user['password']);
            
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                error_log("Password verified successfully");
                error_log("Session data: " . print_r($_SESSION, true));
                
                // Update last login timestamp
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                error_log("Login successful for user: " . $username);
                sendResponse(true, "Login successful", "/SMS/index.php");
            } else {
                error_log("Password verification failed");
                sendResponse(false, "Invalid password");
            }
        } else {
            error_log("No user found with username: " . $username);
            sendResponse(false, "User not found");
        }
    } catch (PDOException $e) {
        error_log("Database error during login: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        sendResponse(false, "System error. Please try again later.");
    }
} else {
    sendResponse(false, "Invalid request method");
}
