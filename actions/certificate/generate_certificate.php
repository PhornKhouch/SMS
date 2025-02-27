<?php
require_once '../../vendor/autoload.php';
require_once "../../includes/config.php";
require_once "../../includes/telegram_config.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $certificate_no = $_POST['certificate_no'];
    $issue_date = $_POST['issue_date'];
    $language = $_POST['language'] ?? 'en';

    // Fetch student and course details
    $query = "SELECT s.*, sub.subject_name as course_name 
              FROM students s 
              LEFT JOIN subjects sub ON s.class = sub.id 
              WHERE s.id = :student_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Initialize Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);

    // Certificate HTML content
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @font-face {
                font-family: "Open Sans";
                src: url("../../asset/fonts/OpenSans-Regular.ttf") format("truetype");
            }
            body {
                font-family: "Open Sans", Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: #ffffff;
            }
            .page {
                position: relative;
                width: 100%;
                height: 100vh;
                margin: 0;
                padding: 0;
                page-break-after: avoid;
            }
            .outer-border {
                position: absolute;
                top: 20px;
                left: 20px;
                right: 20px;
                bottom: 20px;
                border: 2px solid #1e3a8a;
            }
            .certificate-border {
                position: absolute;
                top: 30px;
                left: 30px;
                right: 30px;
                bottom: 30px;
                border: 15px solid #1e3a8a;
            }
            .inner-border {
                position: absolute;
                top: 45px;
                left: 45px;
                right: 45px;
                bottom: 45px;
                border: 2px solid #ffd700;
            }
            .certificate-content {
                position: relative;
                margin: 60px;
                text-align: center;
                height: calc(100% - 120px);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .logo {
                width: 80px;
                margin: 10px auto;
            }
            .certificate-type {
                color: #666;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 4px;
                margin-bottom: 0;
            }
            .title {
                font-size: 36px;
                color: #1e3a8a;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 4px;
                margin: 5px 0;
                line-height: 1.2;
            }
            .subtitle {
                font-size: 20px;
                color: #1e3a8a;
                text-transform: uppercase;
                letter-spacing: 3px;
                margin-bottom: 20px;
            }
            .intro-text {
                font-size: 14px;
                color: #666;
                margin: 10px 0;
            }
            .student-name {
                font-size: 28px;
                color: #1e3a8a;
                font-weight: bold;
                margin: 10px auto;
                padding-bottom: 5px;
                display: inline-block;
                border-bottom: 2px solid #ffd700;
                min-width: 300px;
            }
            .course-name {
                font-size: 22px;
                color: #1e3a8a;
                font-weight: bold;
                margin: 15px 0;
                padding: 0 30px;
                display: inline-block;
                position: relative;
            }
            .course-name:before,
            .course-name:after {
                content: "★";
                color: #ffd700;
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                font-size: 14px;
            }
            .course-name:before {
                left: 0;
            }
            .course-name:after {
                right: 0;
            }
            .signature-section {
                margin-top: auto;
                padding-bottom: 60px;
            }
            .signatures {
                display: flex;
                justify-content: space-evenly;
                margin: 0 100px;
                margin-bottom: 15px;
            }
            .signature {
                text-align: center;
                min-width: 180px;
            }
            .signature-line {
                width: 100%;
                border-top: 2px solid #1e3a8a;
                margin-bottom: 8px;
            }
            .signature-name {
                font-size: 11px;
                color: #1e3a8a;
                margin-bottom: 3px;
                font-weight: bold;
            }
            .signature-title {
                font-size: 12px;
                color: #1e3a8a;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-weight: bold;
            }
            .certificate-footer {
                position: absolute;
                bottom: 25px;
                left: 0;
                right: 0;
                text-align: center;
                color: #666;
                font-size: 10px;
            }
            .certificate-footer span {
                margin: 0 15px;
            }
            .certificate-no {
                color: #1e3a8a;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="outer-border"></div>
            <div class="certificate-border"></div>
            <div class="inner-border"></div>
            
            <div class="certificate-content">
                <div class="certificate-type">CLUB CODE</div>
                <div class="certificate-type">Certificate of</div>
                <div class="title">Achievement</div>
                <div class="subtitle">Professional Development Program</div>
                
                <div class="intro-text">This is to certify that</div>
                <div class="student-name">'.htmlspecialchars($student['name']).'</div>
                <div class="intro-text">has successfully completed the course</div>
                <div class="course-name">'.htmlspecialchars($student['course_name']).'</div>

                <div class="signature-section">
                    <div class="signatures">
                        <div class="signature">
                            <div class="signature-line"></div>
                            <div class="signature-title">Director</div>
                        </div>
                        <div class="signature">
                            <div class="signature-line"></div>
                            <div class="signature-name">PHORN KHOUCH</div>
                            <div class="signature-title">Head of Training</div>
                        </div>
                    </div>
                </div>

                <div class="certificate-footer">
                    <span>Certificate No: <span class="certificate-no">'.htmlspecialchars($certificate_no).'</span></span>
                    <span>Date: <span class="certificate-no">'.date('F d, Y', strtotime($issue_date)).'</span></span>
                </div>
            </div>
        </div>
    </body>
    </html>';

    // Load HTML content
    $dompdf->loadHtml($html);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render PDF
    $dompdf->render();

    // Save PDF to a temporary file
    $filename = 'certificate_' . $student_id . '_' . date('Ymd') . '.pdf';
    $tempPath = sys_get_temp_dir() . '/' . $filename;
    file_put_contents($tempPath, $dompdf->output());

    $selectChartID="SELECT telegram_chat_id FROM students WHERE id= :student_id";
    $stmt = $pdo->prepare($selectChartID);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $studentChatID = $stmt->fetch(PDO::FETCH_ASSOC)['telegram_chat_id'];

    // Send to Telegram if chat ID exists
    // if (!empty($student['telegram_chat_id'])) {
        error_log("Attempting to send certificate to Telegram - Chat ID: " . $studentChatID);
        $caption = "វិញ្ញាបនបត្របញ្ជាក់ការសិក្សា\nសម្រាប់: " . $student['name'];
        
        if (sendTelegramDocument($studentChatID, $tempPath, $caption)) {
            error_log("Successfully sent certificate to student's Telegram");
        } else {
            error_log("Failed to send certificate to student's Telegram");
        }
    // }

    // Stream the PDF to browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($dompdf->output()));
    echo $dompdf->output();

    // Clean up temporary file
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
    
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
