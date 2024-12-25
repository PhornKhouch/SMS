<?php
require_once '../includes/config.php';

try {
    // Check if table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    echo "Tables found: " . print_r($tables, true) . "\n";

    // Check users in database
    $stmt = $pdo->query("SELECT id, username, role, status FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Users in database: " . print_r($users, true);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
