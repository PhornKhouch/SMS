<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

try {
    $stats = array();

    // Get total students
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['totalStudents'] = $result['total'];

    // Get total teachers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM teachers");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['totalTeachers'] = $result['total'];

    // Get total classes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM classes");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['totalClasses'] = $result['total'];

    // Get total assignments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM assignments");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['pendingAssignments'] = $result['total'];

    echo json_encode($stats);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
