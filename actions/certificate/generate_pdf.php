<?php
require_once '../../vendor/autoload.php';
require_once "../../includes/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $certificate_no = $_POST['certificate_no'];
    $issue_date = $_POST['issue_date'];
    $language = $_POST['language'] ?? 'km';

    // Fetch student and course details
    $query = "SELECT s.*, sub.subject_name as course_name 
              FROM students s 
              LEFT JOIN subjects sub ON s.class = sub.id 
              WHERE s.id = :student_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create new PDF document
    class MYPDF extends TCPDF {
        public function Header() {}
        public function Footer() {}
        
        public function DrawBorder($x, $y, $w, $h) {
            $this->SetLineStyle(array('width' => 2, 'color' => array(30, 58, 138)));
            $this->Rect($x, $y, $w, $h);
            
            // Inner border
            $margin = 10;
            $this->SetLineStyle(array('width' => 1, 'color' => array(255, 215, 0)));
            $this->Rect($x + $margin, $y + $margin, $w - (2 * $margin), $h - (2 * $margin));
            
            // Corner designs
            $corner_size = 40;
            $this->SetLineStyle(array('width' => 1.5, 'color' => array(255, 215, 0)));
            
            // Top left corner
            $this->Line($x + $margin, $y + $margin, $x + $margin + $corner_size, $y + $margin);
            $this->Line($x + $margin, $y + $margin, $x + $margin, $y + $margin + $corner_size);
            
            // Top right corner
            $this->Line($x + $w - $margin - $corner_size, $y + $margin, $x + $w - $margin, $y + $margin);
            $this->Line($x + $w - $margin, $y + $margin, $x + $w - $margin, $y + $margin + $corner_size);
            
            // Bottom left corner
            $this->Line($x + $margin, $y + $h - $margin - $corner_size, $x + $margin, $y + $h - $margin);
            $this->Line($x + $margin, $y + $h - $margin, $x + $margin + $corner_size, $y + $h - $margin);
            
            // Bottom right corner
            $this->Line($x + $w - $margin, $y + $h - $margin - $corner_size, $x + $w - $margin, $y + $h - $margin);
            $this->Line($x + $w - $margin - $corner_size, $y + $h - $margin, $x + $w - $margin, $y + $h - $margin);
        }
    }

    // Create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('ClubCode');
    $pdf->SetAuthor('ClubCode');
    $pdf->SetTitle('Certificate of Completion');

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(20, 20, 20);

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 20);

    // Set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Add a page in landscape
    $pdf->AddPage('L', 'A4');

    // Draw certificate border
    $pdf->DrawBorder(15, 15, $pdf->getPageWidth()-30, $pdf->getPageHeight()-30);

    // Add watermark
    $pdf->SetFont('helvetica', '', 100);
    $pdf->SetTextColor(30, 58, 138, 3); // Very light navy blue
    $pdf->StartTransform();
    $pdf->Rotate(45, $pdf->getPageWidth()/2, $pdf->getPageHeight()/2);
    $pdf->Text($pdf->getPageWidth()/2 - 100, $pdf->getPageHeight()/2, 'ClubCode');
    $pdf->StopTransform();

    // Logo
    $pdf->Image('../../asset/img/logo.jpg', 120, 30, 60);

    // Set font for text
    $pdf->SetFont('dejavusans', 'B', 48);
    $pdf->SetTextColor(30, 58, 138);
    
    // Title
    $pdf->Ln(80);
    $pdf->Cell(0, 0, $language === 'km' ? 'វិញ្ញាបនបត្រ' : 'CERTIFICATE', 0, 1, 'C');
    
    $pdf->SetFont('dejavusans', 'B', 32);
    $pdf->Cell(0, 20, $language === 'km' ? 'នៃការបញ្ចប់ការសិក្សា' : 'OF COMPLETION', 0, 1, 'C');
    
    // Main content
    $pdf->SetFont('dejavusans', '', 24);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Ln(20);
    $pdf->Cell(0, 0, $language === 'km' ? 'នេះជាការបញ្ជាក់ថា' : 'This is to certify that', 0, 1, 'C');
    
    // Student name
    $pdf->Ln(20);
    $pdf->SetFont('dejavusans', 'B', 36);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 0, $student['name'], 0, 1, 'C');
    
    // Course completion text
    $pdf->Ln(20);
    $pdf->SetFont('dejavusans', '', 24);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(0, 0, 
        $language === 'km' ? 
        'បានបញ្ចប់ដោយជោគជ័យនូវវគ្គសិក្សា' : 
        'has successfully completed the course', 
        0, 1, 'C'
    );
    
    // Course name
    $pdf->Ln(20);
    $pdf->SetFont('dejavusans', 'B', 32);
    $pdf->SetTextColor(30, 58, 138);
    $pdf->Cell(0, 0, $student['course_name'], 0, 1, 'C');
    
    // Signatures
    $pdf->Ln(40);
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->SetTextColor(0, 0, 0);
    
    // Calculate positions for signatures
    $page_width = $pdf->getPageWidth();
    $sig_width = 80;
    $left_sig_x = ($page_width/2) - $sig_width - 40;
    $right_sig_x = ($page_width/2) + 40;
    
    // Signature lines
    $pdf->SetLineStyle(array('width' => 0.5, 'color' => array(30, 58, 138)));
    
    // Left signature
    $pdf->Line($left_sig_x, $pdf->GetY(), $left_sig_x + $sig_width, $pdf->GetY());
    $pdf->SetXY($left_sig_x, $pdf->GetY() + 5);
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell($sig_width, 10, $language === 'km' ? 'នាយកវិទ្យាស្ថាន' : 'Director', 0, 0, 'C');
    
    // Right signature
    $pdf->Line($right_sig_x, $pdf->GetY() - 5, $right_sig_x + $sig_width, $pdf->GetY() - 5);
    $pdf->SetXY($right_sig_x, $pdf->GetY());
    $pdf->Cell($sig_width, 10, $language === 'km' ? 'ប្រធានផ្នែកបណ្តុះបណ្តាល' : 'Head of Training', 0, 0, 'C');
    
    // Certificate details
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->SetTextColor(102, 102, 102);
    $pdf->SetXY($left_sig_x, $pdf->GetY() + 20);
    $pdf->Cell($sig_width, 10, 
        ($language === 'km' ? 'លេខវិញ្ញាបនបត្រ' : 'Certificate No') . ': ' . $certificate_no, 
        0, 0, 'L'
    );
    
    $pdf->SetXY($right_sig_x, $pdf->GetY());
    $pdf->Cell($sig_width, 10, 
        ($language === 'km' ? 'កាលបរិច្ឆេទ' : 'Date') . ': ' . date('d/m/Y', strtotime($issue_date)), 
        0, 0, 'L'
    );

    // Output PDF
    $filename = 'certificate_' . $student_id . '_' . date('Ymd') . '.pdf';
    $pdf->Output($filename, 'D');
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
