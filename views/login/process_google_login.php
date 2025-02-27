<?php
session_start();
require_once '../../includes/config.php';
require_once '../../vendor/autoload.php'; // Google API PHP client

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Response array
$response = ['success' => false, 'message' => '', 'redirect' => '', 'debug' => []];

try {
    // Get the credential from the POST request
    $data = json_decode(file_get_contents('php://input'), true);
    $credential = $data['credential'] ?? null;
    $response['debug']['received_data'] = $data;

    if (!$credential) {
        throw new Exception('No credential received');
    }

    // Create Google Client with SSL verification disabled for local development
    $client = new Google_Client([
        'client_id' => '411254282610-6l7r6fbj8ahu7ocij4e3v71ba7jg5j7g.apps.googleusercontent.com'
    ]);
    
    // Set SSL verification options for local development
    $guzzleClient = new \GuzzleHttp\Client([
        'verify' => false // Disable SSL verification for local development
    ]);
    $client->setHttpClient($guzzleClient);
    
    $response['debug']['client_id'] = '411254282610-6l7r6fbj8ahu7ocij4e3v71ba7jg5j7g.apps.googleusercontent.com';

    // Verify the token
    try {
        $payload = $client->verifyIdToken($credential);
        $response['debug']['payload'] = $payload;

        if (!$payload) {
            throw new Exception('Invalid token');
        }
    }catch (\Exception $e) {
        throw new Exception('Token verification failed: ' . $e->getMessage());
    }

    // Get user information from payload
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $name = $payload['name'];
    $picture = $payload['picture'] ?? '';
    
    $response['debug']['user_info'] = [
        'google_id' => $google_id,
        'email' => $email,
        'name' => $name
    ];

    // Connect to database
    $conn = mysqli_connect('localhost', 'root', '', 'sms_db_test');
    if (!$conn) {
        $response['debug']['db_error'] = mysqli_connect_error();
        throw new Exception('Database connection failed: ' . mysqli_connect_error());
    }
    
    // Check if the users table exists
    $tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (!$tableCheck) {
        throw new Exception('Failed to check for users table: ' . mysqli_error($conn));
    }
    
    if (mysqli_num_rows($tableCheck) === 0) {
        // Create users table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            role VARCHAR(50) NOT NULL,
            google_id VARCHAR(255),
            profile_picture TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!mysqli_query($conn, $createTable)) {
            throw new Exception('Failed to create users table: ' . mysqli_error($conn));
        }
        $response['debug']['table_created'] = true;
    }

    // Check if user exists in your database
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE email = ?");
    if (!$stmt) {
        $response['debug']['prepare_error'] = mysqli_error($conn);
        throw new Exception('Prepare statement failed: ' . mysqli_error($conn));
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        $response['debug']['execute_error'] = $stmt->error;
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $response['debug']['user_exists'] = $result->num_rows > 0;

    if ($result->num_rows > 0) {
        // User exists - log them in
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $email;
        
        $response['success'] = true;
        $response['redirect'] = '../../index.php';
        $response['debug']['action'] = 'existing_user_login';
    } else {
        // Optional: Auto-register new users
        $username = explode('@', $email)[0];
        $role = 'student';
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, role, google_id, profile_picture) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            $response['debug']['insert_prepare_error'] = mysqli_error($conn);
            throw new Exception('Prepare insert statement failed: ' . mysqli_error($conn));
        }
        
        $stmt->bind_param("sssss", $username, $email, $role, $google_id, $picture);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $email;
            
            $response['success'] = true;
            $response['redirect'] = '../../index.php';
            $response['debug']['action'] = 'new_user_created';
        } else {
            $response['debug']['insert_error'] = $stmt->error;
            throw new Exception('Failed to create new user: ' . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['debug']['error'] = $e->getMessage();
    $response['debug']['trace'] = $e->getTraceAsString();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
