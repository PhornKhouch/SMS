<?php
session_start();
require_once "../../includes/config.php";
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Payment ID not found";
    header("Location: payment-list.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.*, s.name as student_name, s.student_id as student_code 
                          FROM payments p 
                          LEFT JOIN students s ON p.student_id = s.id 
                          WHERE p.id = ?");
    $stmt->execute([$_GET['id']]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error_message'] = "Payment not found";
        header("Location: payment-list.php");
        exit;
    }

    // Create new PDF document
    class MYPDF extends TCPDF {
        public function Header() {
            // Logo
            $image_file = '../../assets/img/logo.jpg';
            if (file_exists($image_file)) {
                $this->Image($image_file, 15, 10, 30);
            }
            
            // Set font
            $this->SetFont('helvetica', 'B', 16);
            
            // Title
            $this->Cell(0, 15, 'PAYMENT RECEIPT', 0, 1, 'C');
            $this->SetFont('helvetica', '', 12);
            $this->Cell(0, 10, 'CLUB CODE IT.TRAINING ', 0, 1, 'C');
            $this->Ln(10);
        }
        
        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
        }
    }

    // Create new PDF instance
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('School Management System');
    $pdf->SetAuthor('PUC');
    $pdf->SetTitle('Payment Receipt');
    
    // Set margins
    $pdf->SetMargins(15, 50, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Add a page
    $pdf->AddPage();

    // Set default font
    $pdf->SetFont('helvetica', '', 11);

    // Payment Details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'STUDENT INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    // Create a table-like structure
    $pdf->SetFillColor(245, 245, 245);
    
    // Student Info
    $pdf->Cell(60, 8, 'Student ID:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $payment['student_code'], 1, 1, 'L');
    
    $pdf->Cell(60, 8, 'Student Name:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $payment['student_name'], 1, 1, 'L');

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'PAYMENT INFORMATION', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    
    // Payment Info
    $pdf->Cell(60, 8, 'Payment Date:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, date('d/m/Y', strtotime($payment['payment_date'])), 1, 1, 'L');
    
    $pdf->Cell(60, 8, 'Amount:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, '$' . number_format($payment['payment_amount'], 2), 1, 1, 'L');
    
    $pdf->Cell(60, 8, 'Payment Method:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $payment['payment_method'], 1, 1, 'L');
    
    $pdf->Cell(60, 8, 'Reference Number:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $payment['reference_number'], 1, 1, 'L');
    
    $pdf->Cell(60, 8, 'Academic Year:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $payment['academic_year'], 1, 1, 'L');
    
    $pdf->Cell(60, 8, 'Semester:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $payment['semester'], 1, 1, 'L');
    
    $pdf->Cell(60, 8, 'Status:', 1, 0, 'L', true);
    $pdf->Cell(0, 8, $payment['payment_status'], 1, 1, 'L');

    if (!empty($payment['description'])) {
        $pdf->Ln(5);
        $pdf->Cell(60, 8, 'Description:', 1, 0, 'L', true);
        $pdf->MultiCell(0, 8, $payment['description'], 1, 'L');
    }

    // Signature section
    $pdf->Ln(20);
    
    // Left side - Receiver
    $pdf->Cell(95, 10, 'Receiver Signature', 0, 0, 'C');
    $pdf->Cell(95, 10, 'Payer Signature', 0, 1, 'C');
    
    // Names under the signature labels
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(95, 8, 'Phorn Khouch', 0, 0, 'C');
    $pdf->Cell(95, 8, $payment['student_name'], 0, 1, 'C');
    
    // Signature lines
    $pdf->Cell(95, 20, '', 'B', 0, 'C');
    $pdf->Cell(95, 20, '', 'B', 1, 'C');

    // Add date printed
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 8);
    // $pdf->Cell(0, 10, 'Printed on: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
    $pdf->Cell(0, 10, 'Auto-generated from the system on: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
    // Output the PDF
    $pdf->Output('Payment_Receipt_' . $payment['id'] . '.pdf', 'I');
    
} catch(PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: payment-list.php");
    exit;
}
?>
