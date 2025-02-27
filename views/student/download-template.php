<?php
require_once "../../includes/config.php";
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$headers = ['អត្តលេខសិស្ស', 'ឈ្មោះ', 'ថ្នាក់', 'ភេទ', 'ថ្ងៃខែឆ្នាំកំណើត', 'អាសយដ្ឋាន', 'ស្ថានភាព'];
$sheet->fromArray($headers, NULL, 'A1');

// Style the header row
$headerStyle = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '198754',
        ],
    ],
    'font' => [
        'color' => [
            'rgb' => 'FFFFFF',
        ],
    ],
];

$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

// Auto-size columns
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Add sample data
$sampleData = [
    ['ST001', 'ឈ្មោះសិស្ស', 'ថ្នាក់ A', 'Male', '2000-01-01', 'អាសយដ្ឋាន', 'Active'],
];
$sheet->fromArray($sampleData, NULL, 'A2');

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="student_import_template.xlsx"');
header('Cache-Control: max-age=0');

// Save file to PHP output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
