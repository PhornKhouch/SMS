<?php
session_start();
require_once '../../includes/config.php';

header('Content-Type: application/json');

try {
    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['accessToken']) || !isset($data['userID'])) {
        throw new Exception('Invalid request data');
    }

    $accessToken = $data['accessToken'];
    $userID = $data['userID'];

    // Verify the access token and get user data from Facebook
    $fb_response = file_get_contents("https://graph.facebook.com/v18.0/me?fields=id,name,email&access_token=" . $accessToken);
    $fb_user = json_decode($fb_response, true);

    if (!$fb_user || isset($fb_user['error'])) {
        throw new Exception('Failed to verify Facebook token');
    }

    // Verify that the user ID matches
    if ($fb_user['id'] !== $userID) {
        throw new Exception('User ID mismatch');
    }

    // Check if user exists in your database
    $stmt = $conn->prepare("SELECT * FROM users WHERE facebook_id = ?");
    $stmt->bind_param("s", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        // Create new user if they don't exist
        $stmt = $conn->prepare("INSERT INTO users (facebook_id, name, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $userID, $fb_user['name'], $fb_user['email']);
        $stmt->execute();
        
        // Get the newly created user
        $user_id = $conn->insert_id;
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    echo json_encode([
        'success' => true,
        'redirect' => '../../index.php'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
