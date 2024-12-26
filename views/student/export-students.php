<?php
session_start();
require_once "../../includes/config.php";
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student-list.php');
    exit;
}

// Get search parameter if exists
$search = isset($_POST['search']) ? $_POST['search'] : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE name LIKE :search OR student_id LIKE :search OR class LIKE :search";
}

// Fetch all students
$query = "SELECT * FROM students $where ORDER BY name";
$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Khmer Unicode font
$sheet->getStyle('A1:H1')->getFont()->setName('Khmer OS Battambang');
$sheet->getStyle('A2:H500')->getFont()->setName('Khmer OS Battambang');

// Set headers
$headers = [
    'អត្តលេខសិស្ស',
    'ឈ្មោះ',
    'ថ្នាក់',
    'ភេទ',
    'ថ្ងៃខែឆ្នាំកំណើត',
    'លេខទូរស័ព្ទ',
    'អាសយដ្ឋាន',
    'ស្ថានភាព'
];

foreach (range('A', 'H') as $key => $column) {
    $sheet->setCellValue($column . '1', $headers[$key]);
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Style the header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'size' => 12
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'E2E2E2',
        ],
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

// Add data
$row = 2;
foreach ($students as $student) {
    $gender = $student['gender'] == 'Male' ? 'ប្រុស' : ($student['gender'] == 'Female' ? 'ស្រី' : 'ផ្សេងៗ');
    $status = $student['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម';
    
    $sheet->setCellValue('A' . $row, $student['student_id']);
    $sheet->setCellValue('B' . $row, $student['name']);
    $sheet->setCellValue('C' . $row, $student['class']);
    $sheet->setCellValue('D' . $row, $gender);
    $sheet->setCellValue('E' . $row, $student['date_of_birth']);
    $sheet->setCellValue('F' . $row, $student['phone']);
    $sheet->setCellValue('G' . $row, $student['address']);
    $sheet->setCellValue('H' . $row, $status);
    
    $row++;
}

// Create writer and output file
$writer = new Xlsx($spreadsheet);

// Set filename with timestamp
$filename = 'student_list_' . date('Y-m-d_H-i-s') . '.xlsx';

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output file
$writer->save('php://output');
exit;
