<?php
session_start();
require_once "../../includes/config.php";
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payment-list.php');
    exit;
}

// Get filters
$search = isset($_POST['search']) ? $_POST['search'] : '';
$payment_type_filter = isset($_POST['payment_type']) ? $_POST['payment_type'] : '';
$status_filter = isset($_POST['status']) ? $_POST['status'] : '';

try {
    // Prepare query
    $query = "SELECT p.*, s.name as student_name, s.student_id as student_code 
              FROM payments p 
              LEFT JOIN students s ON p.student_id = s.id";
    
    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(s.name LIKE :search 
                              OR p.reference_number LIKE :search 
                              OR p.payment_method LIKE :search
                              OR p.pay_type LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if (!empty($payment_type_filter)) {
        $where_conditions[] = "p.pay_type = :payment_type";
        $params[':payment_type'] = $payment_type_filter;
    }

    if (!empty($status_filter)) {
        $where_conditions[] = "p.payment_status = :status";
        $params[':status'] = $status_filter;
    }

    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $query .= " ORDER BY p.payment_date DESC";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Khmer Unicode font
    $sheet->getStyle('A1:J1')->getFont()->setName('Khmer OS Battambang');
    $sheet->getStyle('A2:J500')->getFont()->setName('Khmer OS Battambang');

    // Set headers
    $headers = [
        'ល.រ',
        'អត្តលេខសិស្ស',
        'ឈ្មោះសិស្ស',
        'កាលបរិច្ឆេទបង់ប្រាក់',
        'ចំនួនទឹកប្រាក់',
        'វិធីសាស្ត្របង់ប្រាក់',
        'ប្រភេទបង់ប្រាក់',
        'ស្ថានភាព',
        'ឆ្នាំសិក្សា',
        'លេខយោង'
    ];

    foreach (range('A', 'J') as $key => $column) {
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
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'E2E2E2',
            ],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];
    $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

    // Define status colors (matching your CSS)
    $statusColors = [
        'Completed' => ['fill' => 'd4edda', 'text' => '155724'],
        'Pending' => ['fill' => 'fff3cd', 'text' => '856404'],
        'Failed' => ['fill' => 'f8d7da', 'text' => '721c24'],
        'Refunded' => ['fill' => 'e2e3e5', 'text' => '383d41']
    ];

    // Add data
    $row = 2;
    foreach ($payments as $index => $payment) {
        $payTypeLabels = [
            'Full' => 'បង់ពេញ',
            'Monthly' => 'ជាខែ',
            'Half' => 'ពាក់កណ្តាល'
        ];
        
        $sheet->setCellValue('A' . $row, $index + 1);
        $sheet->setCellValue('B' . $row, $payment['student_code']);
        $sheet->setCellValue('C' . $row, $payment['student_name']);
        $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime($payment['payment_date'])));
        $sheet->setCellValue('E' . $row, '$' . number_format($payment['payment_amount'], 2));
        $sheet->setCellValue('F' . $row, $payment['payment_method']);
        $sheet->setCellValue('G' . $row, $payTypeLabels[$payment['pay_type']] ?? $payment['pay_type']);
        $sheet->setCellValue('H' . $row, $payment['payment_status']);
        $sheet->setCellValue('I' . $row, $payment['academic_year']);
        $sheet->setCellValue('J' . $row, $payment['reference_number']);

        // Apply status color
        if (isset($statusColors[$payment['payment_status']])) {
            $colors = $statusColors[$payment['payment_status']];
            $sheet->getStyle('H' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $colors['fill']]
                ],
                'font' => [
                    'color' => ['rgb' => $colors['text']]
                ]
            ]);
        }
        
        $row++;
    }

    // Create writer and output file
    $writer = new Xlsx($spreadsheet);

    // Set filename with timestamp
    $filename = 'payment_list_' . date('Y-m-d_H-i-s') . '.xlsx';

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Output file
    $writer->save('php://output');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការនាំចេញឯកសារ: " . $e->getMessage();
    header('Location: payment-list.php');
    exit;
}
