<?php
// Define base path
$basePath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;

// Certificate styling
$styles = '
<style>
    .certificate {
        font-family: Arial, sans-serif;
        text-align: center;
        padding: 40px;
        position: relative;
    }
    .certificate-header {
        margin-bottom: 30px;
    }
    .school-logo {
        width: 100px;
        height: auto;
        margin-bottom: 20px;
    }
    .certificate-title {
        font-size: 24pt;
        font-weight: bold;
        color: #1e3a8a;
        margin: 20px 0;
    }
    .certificate-body {
        margin: 40px 0;
        line-height: 1.8;
        font-size: 12pt;
    }
    .student-name {
        font-size: 20pt;
        font-weight: bold;
        color: #1e3a8a;
        margin: 20px 0;
    }
    .course-name {
        font-size: 16pt;
        font-weight: bold;
        color: #1e3a8a;
        margin: 15px 0;
        padding: 10px 30px;
        display: inline-block;
        border: 2px solid #1e3a8a;
        border-radius: 5px;
    }
    .certificate-footer {
        margin-top: 60px;
    }
    .signature-section {
        display: inline-block;
        margin: 0 50px;
    }
    .signature-line {
        width: 200px;
        border-top: 1px solid #000;
        margin: 10px auto;
    }
    .certificate-number {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 10pt;
    }
    .issue-date {
        position: absolute;
        bottom: 20px;
        right: 20px;
        font-size: 10pt;
    }
</style>';

// Certificate content
$html = $styles . '
<div class="certificate">
    <div class="certificate-number">
        លេខៈ ' . htmlspecialchars($certificate_no) . '
    </div>
    
    <div class="certificate-header">
        <img src="data:image/png;base64,' . base64_encode(file_get_contents($basePath . 'assets/images/logo.png')) . '" class="school-logo" alt="School Logo">
        <h1 class="certificate-title">វិញ្ញាបនបត្របញ្ជាក់ការសិក្សា</h1>
    </div>
    
    <div class="certificate-body">
        <p>សូមបញ្ជាក់ថា និស្សិត</p>
        <div class="student-name">' . htmlspecialchars($student['name']) . '</div>
        <p>បានបញ្ចប់ការសិក្សាដោយជោគជ័យនូវវគ្គសិក្សា</p>
        <div class="course-name">' . htmlspecialchars($student['class_name']) . '</div>
        <p>ដែលមានរយៈពេល ៦ខែ ចាប់ពីថ្ងៃទី០១ ខែមករា ឆ្នាំ២០២៥ ដល់ថ្ងៃទី៣០ ខែមិថុនា ឆ្នាំ២០២៥</p>
    </div>
    
    <div class="certificate-footer">
        <div class="signature-section">
            <div class="signature-line"></div>
            <p>នាយកមជ្ឈមណ្ឌល</p>
        </div>
    </div>
    
    <div class="issue-date">
        ថ្ងៃទី ' . date('d') . ' ខែ ' . date('m') . ' ឆ្នាំ ' . date('Y') . '
    </div>
</div>';

echo $html;
?>
