<?php
session_start();
require_once "../../includes/config.php";
require_once '../../vendor/autoload.php'; // Make sure you have PhpSpreadsheet installed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;
use Dompdf\Dompdf;

// Get filters from POST
$class_filter = isset($_POST['class']) ? $_POST['class'] : '';
$status_filter = isset($_POST['status']) ? $_POST['status'] : '';
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';
$export_type = isset($_POST['export_type']) ? $_POST['export_type'] : 'pdf';

// Build query with filters
$where_conditions = [];
$params = [];

if (!empty($class_filter)) {
    $where_conditions[] = "s.class = :class";
    $params[':class'] = $class_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "p.payment_status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($from_date)) {
    $where_conditions[] = "p.payment_date >= :from_date";
    $params[':from_date'] = $from_date;
}

if (!empty($to_date)) {
    $where_conditions[] = "p.payment_date <= :to_date";
    $params[':to_date'] = $to_date;
}

$where = '';
if (!empty($where_conditions)) {
    $where = "WHERE " . implode(" AND ", $where_conditions);
}

// Fetch data
$query = "SELECT s.student_id, s.name as student_name, 
          sub.subject_name as class_name, 
          p.payment_date, p.payment_amount as amount, p.payment_status as status,
          p.payment_method
          FROM students s 
          LEFT JOIN subjects sub ON s.class = sub.id 
          LEFT JOIN payments p ON s.id = p.student_id
          $where 
          ORDER BY p.payment_date DESC";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total_amount = 0;
foreach ($payments as $payment) {
    if ($payment['amount'] !== null) {
        $total_amount += $payment['amount'];
    }
}

switch($export_type) {
    case 'pdf':
        // Create PDF
        $html = '<style>
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #198754; color: white; }
            .total-row { font-weight: bold; background-color: #f8f9fa; }
            .header-en { font-size: 0.9em; color: white; }
        </style>';
        
        $html .= '<h2>Payment report<br><span style="font-size: 0.8em; color: white;">Payment Report</span></h2>';
        $html .= '<table>';
        $html .= '<thead><tr>
            <th><span class="header-en">Student ID</span></th>
            <th><span class="header-en">Student Name</span></th>
            <th><span class="header-en">Class</span></th>
            <th><span class="header-en">Payment Date</span></th>
            <th><span class="header-en">Amount</span></th>
            <th><span class="header-en">Payment Method</span></th>
            <th><span class="header-en">Status</span></th>
        </tr></thead><tbody>';

        foreach ($payments as $payment) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($payment['student_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($payment['student_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($payment['class_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($payment['payment_date']) . '</td>';
            $html .= '<td>$' . number_format($payment['amount'], 2) . '</td>';
            $html .= '<td>' . htmlspecialchars($payment['payment_method']) . '</td>';
            $html .= '<td>' . ($payment['status'] == 'Paid' ? 'paid' : 'unpaid') . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr class="total-row">
            <td colspan="4" style="text-align: right;">Total:</td>
            <td>$' . number_format($total_amount, 2) . '</td>
            <td colspan="2"></td>
        </tr></tbody></table>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="payment_report.pdf"');
        echo $dompdf->output();
        break;

    case 'excel':
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Student ID');
        $sheet->setCellValue('B1', 'Student Name');
        $sheet->setCellValue('C1', 'Class');
        $sheet->setCellValue('D1', 'Payment Date');
        $sheet->setCellValue('E1', 'Amount');
        $sheet->setCellValue('F1', 'Payment Method');
        $sheet->setCellValue('G1', 'Status');

        // Style the header row
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('198754');
        $sheet->getStyle('A1:G1')->getFont()->getColor()->setRGB('FFFFFF');

        // Add data
        $row = 2;
        foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment['student_id']);
            $sheet->setCellValue('B' . $row, $payment['student_name']);
            $sheet->setCellValue('C' . $row, $payment['class_name']);
            $sheet->setCellValue('D' . $row, $payment['payment_date']);
            $sheet->setCellValue('E' . $row, $payment['amount']);
            $sheet->setCellValue('F' . $row, $payment['payment_method']);
            $sheet->setCellValue('G' . $row, $payment['status'] == 'Paid' ? 'paid' : 'unpaid');
            $row++;
        }

        // Add total
        $sheet->setCellValue('D' . $row, 'Total:');
        $sheet->setCellValue('E' . $row, $total_amount);

        // Auto-size columns
        foreach(range('A','G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="payment_report.xlsx"');
        $writer->save('php://output');
        break;

    case 'word':
        // Create Word document
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment; filename="payment_report.doc"');
        
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="utf-8"><title>Payment Report</title></head>';
        echo '<body>';
        echo '<h2>Payment Report</h2>';
        echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background-color: #198754; color: white;">
            <th><span style="font-size: 1.2em;">ID</span><br><span style="font-size: 0.9em;">Student ID</span></th>
            <th><span style="font-size: 1.2em;">Student Name</span><br><span style="font-size: 0.9em;">Student Name</span></th>
            <th><span style="font-size: 1.2em;">class</span><br><span style="font-size: 0.9em;">Class</span></th>
            <th><span style="font-size: 1.2em;">Payment date</span><br><span style="font-size: 0.9em;">Payment Date</span></th>
            <th><span style="font-size: 1.2em;">Amount</span><br><span style="font-size: 0.9em;">Amount</span></th>
            <th><span style="font-size: 1.2em;">Payment Method</span><br><span style="font-size: 0.9em;">Payment Method</span></th>
            <th><span style="font-size: 1.2em;">status</span><br><span style="font-size: 0.9em;">Status</span></th>
        </tr>';
        
        foreach ($payments as $payment) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($payment['student_id']) . '</td>';
            echo '<td>' . htmlspecialchars($payment['student_name']) . '</td>';
            echo '<td>' . htmlspecialchars($payment['class_name']) . '</td>';
            echo '<td>' . htmlspecialchars($payment['payment_date']) . '</td>';
            echo '<td>$' . number_format($payment['amount'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($payment['payment_method']) . '</td>';
            echo '<td>' . ($payment['status'] == 'Paid' ? 'paid' : 'unpaid') . '</td>';
            echo '</tr>';
        }

        echo '<tr><td colspan="4" style="text-align: right;">Total:</td><td>$' . number_format($total_amount, 2) . '</td><td colspan="2"></td></tr>';
        echo '</table>';
        echo '</body></html>';
        break;

    case 'image':
        // For image export, we'll create a simple HTML canvas and convert it to image using JavaScript
        header('Content-Type: text/html');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Payment Report</title>
            <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
        </head>
        <body>
            <div id="report">
                <h2>Payment Report</h2>
                <table border="1" style="border-collapse: collapse; width: 100%;">
                    <tr style="background-color: #198754; color: white;">
                        <th><span style="font-size: 1.2em;">ID</span><br><span style="font-size: 0.9em;">Student ID</span></th>
                        <th><span style="font-size: 1.2em;">Student name</span><br><span style="font-size: 0.9em;">Student Name</span></th>
                        <th><span style="font-size: 1.2em;">class</span><br><span style="font-size: 0.9em;">Class</span></th>
                        <th><span style="font-size: 1.2em;">Payment date</span><br><span style="font-size: 0.9em;">Payment Date</span></th>
                        <th><span style="font-size: 1.2em;">Amount</span><br><span style="font-size: 0.9em;">Amount</span></th>
                        <th><span style="font-size: 1.2em;">Payment Method</span><br><span style="font-size: 0.9em;">Payment Method</span></th>
                        <th><span style="font-size: 1.2em;">status</span><br><span style="font-size: 0.9em;">Status</span></th>
                    </tr>';
        
        foreach ($payments as $payment) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($payment['student_id']) . '</td>';
            echo '<td>' . htmlspecialchars($payment['student_name']) . '</td>';
            echo '<td>' . htmlspecialchars($payment['class_name']) . '</td>';
            echo '<td>' . htmlspecialchars($payment['payment_date']) . '</td>';
            echo '<td>$' . number_format($payment['amount'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($payment['payment_method']) . '</td>';
            echo '<td>' . ($payment['status'] == 'Paid' ? 'Paid' : 'Unpaid') . '</td>';
            echo '</tr>';
        }

        echo '<tr><td colspan="4" style="text-align: right;">Total:</td><td>$' . number_format($total_amount, 2) . '</td><td colspan="2"></td></tr>';
        echo '</table>
            </div>
            <script>
            html2canvas(document.getElementById("report")).then(function(canvas) {
                var link = document.createElement("a");
                link.download = "payment_report.png";
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
            </script>
        </body>
        </html>';
        break;
}
exit;
?>
