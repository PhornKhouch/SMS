<?php
session_start();
require_once "../../includes/config.php";

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if file was uploaded
if (!isset($_FILES['lesson_file']) || !isset($_POST['subject_id']) || !isset($_POST['lesson_name'])) {
    echo json_encode(array('success' => false, 'message' => 'សូមបំពេញព័ត៌មានទាំងអស់'));
    exit;
}

// Verify database connection
if (!isset($pdo)) {
    error_log("Database connection not established");
    echo json_encode(array('success' => false, 'message' => 'កំហុសក្នុងការតភ្ជាប់ទៅកាន់មូលដ្ឋានទិន្នន័យ'));
    exit;
}

try {
    // Sanitize inputs
    $subject_id = filter_var($_POST['subject_id'], FILTER_SANITIZE_NUMBER_INT);
    $lesson_name = filter_var($_POST['lesson_name'], FILTER_SANITIZE_STRING);
    $file = $_FILES['lesson_file'];

    // Validate subject_id
    if (!is_numeric($subject_id) || $subject_id <= 0) {
        echo json_encode(array('success' => false, 'message' => 'លេខសម្គាល់មុខវិជ្ជាមិនត្រឹមត្រូវ'));
        exit;
    }
    // Check file size (max 64MB)
    $maxFileSize = 128 * 1024 * 1024; // 64MB in bytes
    if ($file['size'] > $maxFileSize) {
        echo json_encode(array('success' => false, 'message' => 'ឯកសារធំជាង 64MB'));
        exit;
    }
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $message = 'ឯកសារធំជាងទំហំកំណត់ (upload_max_filesize)';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'ឯកសារធំជាងទំហំកំណត់ (MAX_FILE_SIZE)';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = 'ឯកសារត្រូវបានផ្ទុកឡើងដោយផ្នែក';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = 'មិនមានឯកសារត្រូវបានជ្រើសរើស';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'កំហុសក្នុងការរក្សាទុកឯកសារបណ្តោះអាសន្ន';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = 'កំហុសក្នុងការសរសេរឯកសារ';
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = 'ការផ្ទុកឡើងត្រូវបានបញ្ឈប់ដោយកម្មវិធីបន្ថែម PHP';
                break;
            default:
                $message = 'កំហុសមិនស្គាល់ក្នុងការផ្ទុកឡើង';
        }
        echo json_encode(array('success' => false, 'message' => $message));
        exit;
    }

    

    // Validate file type and MIME type
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileMimeType = mime_content_type($file['tmp_name']);
    if ($fileType !== "pdf" || $fileMimeType !== 'application/pdf') {
        echo json_encode(array('success' => false, 'message' => 'អនុញ្ញាតតែឯកសារ PDF ប៉ុណ្ណោះ'));
        exit;
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "lessons" . DIRECTORY_SEPARATOR;
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            error_log("Failed to create directory: " . $upload_dir);
            echo json_encode(array('success' => false, 'message' => 'មិនអាចបង្កើតថតឯកសារសម្រាប់ផ្ទុកឡើងបានទេ'));
            exit;
        }
    }

    // Ensure directory is writable
    if (!is_writable($upload_dir)) {
        chmod($upload_dir, 0755);
        if (!is_writable($upload_dir)) {
            error_log("Directory not writable: " . $upload_dir);
            echo json_encode(array('success' => false, 'message' => 'មិនអាចសរសេរទៅក្នុងថតឯកសារបានទេ'));
            exit;
        }
    }

    // Generate unique filename with better entropy
    $filename = uniqid(time() . '_', true) . '.pdf';
    $filepath = $upload_dir . $filename;
    $db_filepath = "uploads/lessons/" . $filename; // Store relative path from web root

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        try {
            // Save to database
            $query = "INSERT INTO lessons (subject_id, lesson_name, file_path, created_at) 
                     VALUES (:subject_id, :lesson_name, :file_path, NOW())";
            $stmt = $pdo->prepare($query);
            $params = array(
                ':subject_id' => $subject_id,
                ':lesson_name' => $lesson_name,
                ':file_path' => $db_filepath
            );
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                echo json_encode(array('success' => true, 'message' => 'ឯកសារត្រូវបានផ្ទុកឡើងដោយជោគជ័យ'));
            } else {
                throw new Exception("Failed to insert record into database");
            }
        } catch (Exception $e) {
            // If database insert fails, delete the uploaded file
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    } else {
        error_log("Failed to move uploaded file to: " . $filepath);
        echo json_encode(array('success' => false, 'message' => 'មិនអាចផ្ទុកឯកសារឡើងបានទេ'));
    }
} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    echo json_encode(array('success' => false, 'message' => 'កំហុសម៉ាស៊ីនមេ៖ ' . $e->getMessage()));
}
?>
