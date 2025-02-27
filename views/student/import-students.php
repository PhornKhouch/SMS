<?php
session_start();
require_once "../../includes/config.php";
require '../../vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_FILES['excelFile']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("សូមជ្រើសរើសឯកសារ Excel");
        }

        $inputFileName = $_FILES['excelFile']['tmp_name'];
        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $pdo->beginTransaction();

        // Start from row 2 to skip header
        for ($row = 2; $row <= $highestRow; $row++) {
            $studentId = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
            $name = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
            $class = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
            $gender = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
            $dob = $worksheet->getCellByColumnAndRow(5, $row)->getValue();
            $address = $worksheet->getCellByColumnAndRow(6, $row)->getValue();
            $status = $worksheet->getCellByColumnAndRow(7, $row)->getValue() ?? 'Active';

            // Skip empty rows
            if (empty($studentId) || empty($name)) {
                continue;
            }

            // Convert date format if needed
            if ($dob instanceof \PhpOffice\PhpSpreadsheet\Shared\Date) {
                $dob = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dob)->format('Y-m-d');
            }

            // Get class ID from subject name
            $stmt = $pdo->prepare("SELECT id FROM subjects WHERE subject_name = ?");
            $stmt->execute([$class]);
            $classId = $stmt->fetchColumn();

            if (!$classId) {
                continue; // Skip if class not found
            }

            // Check if student already exists
            $stmt = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
            $stmt->execute([$studentId]);
            $existingStudent = $stmt->fetch();

            if ($existingStudent) {
                // Update existing student
                $sql = "UPDATE students SET 
                        name = ?, class = ?, gender = ?, 
                        date_of_birth = ?, address = ?, status = ? 
                        WHERE student_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $classId, $gender, $dob, $address, $status, $studentId]);
            } else {
                // Insert new student
                $sql = "INSERT INTO students (student_id, name, class, gender, date_of_birth, address, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$studentId, $name, $classId, $gender, $dob, $address, $status]);
            }
        }

        $pdo->commit();
        $_SESSION['success_message'] = "ទិន្នន័យសិស្សត្រូវបាននាំចូលដោយជោគជ័យ";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "មានបញ្ហាក្នុងការនាំចូលទិន្នន័យ: " . $e->getMessage();
    }
}

header("Location: student-list.php");
exit();
