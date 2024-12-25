<?php
require_once '../includes/config.php';

// Admin user details
$username = 'admin';
$password = '123'; // You can change this password
$email = 'admin@example.com';
$full_name = 'System Administrator';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // First, delete existing admin if exists
    $delete = $pdo->prepare("DELETE FROM users WHERE username = ?");
    $delete->execute([$username]);

    // Insert new admin user
    $sql = "INSERT INTO users (username, password, email, full_name, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashed_password, $email, $full_name]);

    echo "Admin user created successfully!\n";
    echo "Username: " . $username . "\n";
    echo "Password: " . $password . "\n";
    echo "You can now login with these credentials.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
