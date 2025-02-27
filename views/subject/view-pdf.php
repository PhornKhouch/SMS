<?php
session_start();
require_once "../../includes/config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['file'])) {
    error_log("PDF Viewer: No file parameter provided");
    header("HTTP/1.0 404 Not Found");
    echo "No file specified";
    exit;
}

$file = $_GET['file'];
// Remove any leading slash
$file = ltrim($file, '/');
// Get the absolute path to the web root
$webroot = dirname(dirname(dirname(__FILE__)));
$filepath = $webroot . '/' . $file;

// Log the attempted file access
error_log("PDF Viewer: Attempting to access file: " . $filepath);

// Validate file exists and is a PDF
if (!file_exists($filepath)) {
    error_log("PDF Viewer: File does not exist: " . $filepath);
    header("HTTP/1.0 404 Not Found");
    echo "File not found: " . htmlspecialchars($filepath);
    exit;
}

if (pathinfo($filepath, PATHINFO_EXTENSION) !== 'pdf') {
    error_log("PDF Viewer: Invalid file type for: " . $filepath);
    header("HTTP/1.0 403 Forbidden");
    echo "Invalid file type";
    exit;
}

// Get file size
$filesize = filesize($filepath);
if ($filesize === false) {
    error_log("PDF Viewer: Could not get file size for: " . $filepath);
    header("HTTP/1.0 500 Internal Server Error");
    echo "Could not read file";
    exit;
}

// Set headers
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
header('Content-Length: ' . $filesize);
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=0');

// Clear output buffer
if (ob_get_level()) ob_end_clean();

// Read and output file
if (!readfile($filepath)) {
    error_log("PDF Viewer: Failed to read file: " . $filepath);
    header("HTTP/1.0 500 Internal Server Error");
    echo "Failed to read file";
    exit;
}
?>
