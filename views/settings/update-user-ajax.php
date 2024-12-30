<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = $_POST['password'];

        // Start with basic update query
        $query = "UPDATE users SET username = :username, email = :email, role = :role";
        $params = [
            ':user_id' => $user_id,
            ':username' => $username,
            ':email' => $email,
            ':role' => $role
        ];

        // If password is provided, update it
        if (!empty($password)) {
            $query .= ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $query .= " WHERE id = :user_id";

        $stmt = $pdo->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'មានបញ្ហាក្នុងការធ្វើបច្ចុប្បន្នភាពអ្នកប្រើប្រាស់']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'មានបញ្ហាក្នុងការធ្វើបច្ចុប្បន្នភាពអ្នកប្រើប្រាស់: ' . $e->getMessage()]);
}
?>
