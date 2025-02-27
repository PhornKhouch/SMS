<?php
require_once '../../includes/config.php';
require_once '../../includes/telegram_config.php';

// Define base path for links
$basePath = "../../";

// Get student ID from URL
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

// Fetch student details
if ($student_id) {
    $query = "SELECT s.*, sub.subject_name as class_name 
              FROM students s 
              LEFT JOIN subjects sub ON s.class = sub.id 
              WHERE s.id = :student_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all subjects
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $pdo->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate unique certificate number
$year = date('Y');
$month = date('m');
$query = "SELECT MAX(CAST(SUBSTRING_INDEX(certificate_no, '/', 1) AS UNSIGNED)) as max_num 
          FROM certificates 
          WHERE certificate_no LIKE :pattern";
$stmt = $pdo->prepare($query);
$pattern = "%/$year$month";
$stmt->bindParam(':pattern', $pattern);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$next_num = ($result['max_num'] ?? 0) + 1;
$certificate_no = sprintf("%04d/%s%s", $next_num, $year, $month);

// Save certificate and send via Telegram
if (isset($_POST['save_certificate'])) {
    try {
        // Generate PDF certificate
        require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('SMS Demo');
        $pdf->SetAuthor('Your School Name');
        $pdf->SetTitle('Certificate - ' . $student['name']);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add a page
        $pdf->AddPage();
        
        // Get certificate HTML content
        ob_start();
        include 'certificate_template.php';
        $html = ob_get_clean();
        
        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Create certificates directory if it doesn't exist
        $cert_dir = "../../uploads/certificates";
        if (!file_exists($cert_dir)) {
            mkdir($cert_dir, 0777, true);
        }
        
        // Save PDF
        $filename = "certificate_{$student['student_id']}_{$certificate_no}.pdf";
        $filepath = $cert_dir . '/' . $filename;
        $pdf->Output($filepath, 'F');
        
        // Save certificate record to database
        $query = "INSERT INTO certificates (student_id, certificate_no, issue_date, created_at) 
                 VALUES (:student_id, :certificate_no, :issue_date, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':student_id' => $student_id,
            ':certificate_no' => $certificate_no,
            ':issue_date' => date('Y-m-d')
        ]);
        sendTelegramDocument('1001388981', $filepath, $caption);
        // Send via Telegram if chat ID exists
        if (!empty($student['telegram_chat_id'])) {
            error_log("Student Telegram chat ID: " . $student['telegram_chat_id']);
            error_log("Student name: " . $student['name']);
            error_log("File path: " . $filepath);
            
            $caption = "វិញ្ញាបនបត្របញ្ជាក់ការសិក្សា\nសម្រាប់: " . $student['name'];
            if (sendTelegramDocument($student['telegram_chat_id'], $filepath, $caption)) {
                $_SESSION['success_message'] = "វិញ្ញាបនបត្រត្រូវបានបង្កើត និងផ្ញើទៅ Telegram ដោយជោគជ័យ!";
            } else {
                error_log("Failed to send Telegram document");
                $_SESSION['success_message'] = "វិញ្ញាបនបត្រត្រូវបានបង្កើត ប៉ុន្តែមានបញ្ហាក្នុងការផ្ញើទៅ Telegram";
            }
        } else {
            error_log("No Telegram chat ID found for student");
            $_SESSION['success_message'] = "វិញ្ញាបនបត្រត្រូវបានបង្កើតដោយជោគជ័យ!";
        }
        
        header("Location: ../student/index.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = "មានបញ្ហាក្នុងការបង្កើតវិញ្ញាបនបត្រ: " . $e->getMessage();
        header("Location: ../student/index.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បង្កើតវិញ្ញាបនបត្រ - <?php echo htmlspecialchars($student['name'] ?? ''); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
            background: #f0f2f5;
        }
        .certificate-preview {
            width: 100%;
            max-width: 29.7cm;
            height: auto;
            aspect-ratio: 1.414;
            margin: 20px auto;
            padding: 2cm;
            background: white;
            position: relative;
            border: 20px solid #1e3a8a;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            overflow: hidden;
            transform: scale(0.8);
            transform-origin: top center;
        }
        #preview-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }
        .form-card {
            margin-bottom: 2rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 0.5rem;
            border: none;
        }
        .form-card .card-body {
            padding: 1.5rem;
        }
        .preview-title {
            text-align: center;
            margin: 1rem 0;
            color: #1e3a8a;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .inner-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #ffd700;
        }
        .corner-design {
            position: absolute;
            width: 100px;
            height: 100px;
            border: 3px solid #ffd700;
        }
        .top-left {
            top: 10px;
            left: 10px;
            border-right: none;
            border-bottom: none;
        }
        .top-right {
            top: 10px;
            right: 10px;
            border-left: none;
            border-bottom: none;
        }
        .bottom-left {
            bottom: 10px;
            left: 10px;
            border-right: none;
            border-top: none;
        }
        .bottom-right {
            bottom: 10px;
            right: 10px;
            border-left: none;
            border-top: none;
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }
        .certificate-title {
            color: #1e3a8a;
            font-size: 52px;
            font-weight: bold;
            margin: 20px 0 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .certificate-subtitle {
            color: #1e3a8a;
            font-size: 32px;
            margin: 10px 0 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .club-logo {
            max-width: 180px;
            margin-bottom: 30px;
        }
        .certificate-content {
            text-align: center;
            padding: 0 80px;
            position: relative;
            z-index: 2;
        }
        .hereby-text {
            font-size: 24px;
            margin: 30px 0;
            color: #333;
        }
        .student-name {
            font-size: 48px;
            color: #1e3a8a;
            margin: 30px 0;
            font-weight: bold;
            padding: 0 40px 10px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            position: relative;
        }
        .student-name:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 3px;
            background: linear-gradient(to right, #1e3a8a, #ffd700, #1e3a8a);
        }
        .description-text {
            font-size: 24px;
            color: #333;
            margin: 30px 0;
            line-height: 1.6;
        }
        .course-name {
            font-size: 36px;
            color: #1e3a8a;
            margin: 30px 0;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            padding: 20px;
            position: relative;
        }
        .course-name:before, .course-name:after {
            content: '★';
            color: #ffd700;
            position: absolute;
            font-size: 24px;
        }
        .course-name:before {
            left: 0;
        }
        .course-name:after {
            right: 0;
        }
        .certificate-footer {
            margin-top: 80px;
            position: relative;
            z-index: 2;
            padding: 0 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-section {
            text-align: center;
            flex: 1;
            margin: 0 40px;
        }
        .signature-line {
            width: 200px;
            border-top: 2px solid #1e3a8a;
            margin: 0 auto 10px;
        }
        .signature-title {
            font-size: 20px;
            color: #1e3a8a;
            font-weight: bold;
            margin: 10px 0;
        }
        .certificate-details {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 60px;
            font-size: 16px;
            color: #666;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 150px;
            color: rgba(30, 58, 138, 0.03);
            white-space: nowrap;
            pointer-events: none;
            z-index: 1;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #preview-container, #preview-container * {
                visibility: visible;
            }
            #preview-container {
                position: absolute;
                left: 0;
                top: 0;
            }
            .certificate-preview {
                box-shadow: none;
                margin: 0;
                padding: 2cm;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include $basePath . 'includes/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation -->
            <?php include $basePath . 'includes/topnav.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid">
                <div class="content-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>បង្កើតវិញ្ញាបនបត្រ</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../../index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="../student/student-list.php">សិស្ស</a></li>
                                    <li class="breadcrumb-item active">បង្កើតវិញ្ញាបនបត្រ</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <?php if ($student): ?>
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card form-card">
                            <div class="card-body">
                                <form id="certificateForm">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="student">សិស្ស / Student</label>
                                            <input type="text" class="form-control" id="student" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="course">មុខវិជ្ជា / Course</label>
                                            <select class="form-select" id="course">
                                                <?php foreach ($subjects as $subject): ?>
                                                    <option value="<?php echo $subject['id']; ?>" <?php echo ($student['class'] == $subject['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="date">កាលបរិច្ឆេទ / Date</label>
                                            <input type="date" class="form-control" id="date" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="certificate_no">លេខវិញ្ញាបនបត្រ / Certificate No</label>
                                            <input type="text" class="form-control" id="certificate_no" value="<?php echo $certificate_no; ?>" readonly>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="language">ភាសា / Language</label>
                                            <select class="form-select" id="language">
                                                <option value="en">English</option>
                                                <option value="km">ខ្មែរ</option>
                                            </select>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="button" class="btn btn-primary" id="generateCertificate">
                                                <i class="fas fa-certificate"></i> បង្កើតវិញ្ញាបនបត្រ / Generate Certificate
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div id="preview-container">
                            <div class="preview-title">បង្ហាញពិពណ៌នា / Preview</div>
                            <div id="certificatePreview" class="certificate-preview">
                                <div class="inner-border"></div>
                                <div class="corner-design top-left"></div>
                                <div class="corner-design top-right"></div>
                                <div class="corner-design bottom-left"></div>
                                <div class="corner-design bottom-right"></div>
                                <div class="watermark">ClubCode</div>
                                
                                <div class="certificate-header">
                                    <img src="../../asset/img/logo.jpg" alt="ClubCode Logo" class="club-logo">
                                    <h1 class="certificate-title" data-km="វិញ្ញាបនបត្រ" data-en="CERTIFICATE">វិញ្ញាបនបត្រ</h1>
                                    <h2 class="certificate-subtitle" data-km="នៃការបញ្ចប់ការសិក្សា" data-en="OF COMPLETION">នៃការបញ្ចប់ការសិក្សា</h2>
                                </div>
                                
                                <div class="certificate-content">
                                    <div class="hereby-text" data-km="នេះជាការបញ្ជាក់ថា" data-en="This is to certify that">នេះជាការបញ្ជាក់ថា</div>
                                    <div class="student-name" id="studentName"><?php echo htmlspecialchars($student['name']); ?></div>
                                    <div class="description-text" data-km="បានបញ្ចប់ដោយជោគជ័យនូវវគ្គសិក្សា" data-en="has successfully completed the course">បានបញ្ចប់ដោយជោគជ័យនូវវគ្គសិក្សា</div>
                                    <div class="course-name" id="courseName">_______________</div>
                                </div>

                                <div class="certificate-footer">
                                    <div class="signature-section">
                                        <div class="signature-line"></div>
                                        <div class="signature-title" data-km="នាយកវិទ្យាស្ថាន" data-en="Director">នាយកវិទ្យាស្ថាន</div>
                                    </div>
                                    <div class="signature-section">
                                        <div class="signature-line"></div>
                                        <div class="signature-title" data-km="ប្រធានផ្នែកបណ្តុះបណ្តាល" data-en="Head of Training">ប្រធានផ្នែកបណ្តុះបណ្តាល</div>
                                    </div>
                                </div>
                                
                                <div class="certificate-details">
                                    <div>
                                        <span data-km="លេខវិញ្ញាបនបត្រ" data-en="Certificate No">លេខវិញ្ញាបនបត្រ</span>: 
                                        <span id="certNo"><?php echo htmlspecialchars($certificate_no); ?></span>
                                    </div>
                                    <div>
                                        <span data-km="កាលបរិច្ឆេទ" data-en="Date">កាលបរិច្ឆេទ</span>: 
                                        <span id="certDate"><?php echo date('d/m/Y'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> រកមិនឃើញព័ត៌មានសិស្ស
                </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle certificate generation
            $('#generateCertificate').click(function() {
                const studentId = '<?php echo $student_id; ?>';
                const courseId = $('#course').val();
                const certificateNo = $('#certificate_no').val();
                const issueDate = $('#date').val();
                const language = $('#language').val();

                // Show loading state
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> កំពុងដំណើរការ...');

                // Generate certificate
                $.ajax({
                    url: '../../actions/certificate/generate_certificate.php',
                    method: 'POST',
                    data: {
                        student_id: studentId,
                        course_id: courseId,
                        certificate_no: certificateNo,
                        issue_date: issueDate,
                        language: language
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response) {
                        // Create blob link to download
                        const blob = new Blob([response], { type: 'application/pdf' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'certificate_' + studentId + '.pdf';
                        document.body.appendChild(a);
                        a.click();
                        
                        // Cleanup
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        
                        // Reset button state
                        $('#generateCertificate').prop('disabled', false)
                            .html('<i class="fas fa-certificate"></i> បង្កើតវិញ្ញាបនបត្រ / Generate Certificate');
                        
                        // Show success message
                        toastr.success('វិញ្ញាបនបត្របានបង្កើតដោយជោគជ័យ', 'ជោគជ័យ!');
                    },
                    error: function(xhr, status, error) {
                        // Reset button state
                        $('#generateCertificate').prop('disabled', false)
                            .html('<i class="fas fa-certificate"></i> បង្កើតវិញ្ញាបនបត្រ / Generate Certificate');
                        
                        // Show error message
                        toastr.error('មានបញ្ហាក្នុងការបង្កើតវិញ្ញាបនបត្រ', 'បរាជ័យ!');
                        console.error('Error generating certificate:', error);
                    }
                });
            });

            // Initialize toastr options
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000
            };
        });
    </script>
    <script>
        function updateLanguage(lang) {
            document.querySelectorAll('[data-km], [data-en]').forEach(el => {
                el.textContent = el.getAttribute(`data-${lang}`);
            });
        }

        function generateCertificate() {
            const studentId = $('#student_id').val();
            const courseId = $('#course').val();
            const issueDate = $('#issue_date').val();
            const certificateNo = $('#certificate_no').val();
            const language = $('#language').val();

            if (!courseId || !issueDate || !certificateNo) {
                toastr.error(language === 'km' ? 'សូមបំពេញព័ត៌មានទាំងអស់' : 'Please fill in all information');
                return;
            }

            // First save the certificate
            $.ajax({
                url: '../../actions/certificate/save_certificate.php',
                method: 'POST',
                data: {
                    student_id: studentId,
                    course_id: courseId,
                    issue_date: issueDate,
                    certificate_no: certificateNo,
                    language: language
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        toastr.success(language === 'km' ? 
                            'វិញ្ញាបនបត្រត្រូវបានរក្សាទុកដោយជោគជ័យ' : 
                            'Certificate saved successfully');
                        
                        // Generate PDF using the new endpoint
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '../../actions/certificate/generate_certificate.php';
                        form.target = '_blank';

                        const fields = {
                            student_id: studentId,
                            course_id: courseId,
                            issue_date: issueDate,
                            certificate_no: certificateNo,
                            language: language
                        };

                        for (const key in fields) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = fields[key];
                            form.appendChild(input);
                        }

                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);
                        
                        // Show success message
                        toastr.success('វិញ្ញាបនបត្របានបង្កើតដោយជោគជ័យ', 'ជោគជ័យ!');
                    } else {
                        toastr.error(data.message || (language === 'km' ? 
                            'មានបញ្ហាក្នុងការរក្សាទុកវិញ្ញាបនបត្រ' : 
                            'Error saving certificate'));
                    }
                },
                error: function() {
                    toastr.error(language === 'km' ? 
                        'មានបញ្ហាក្នុងការរក្សាទុកវិញ្ញាបនបត្រ' : 
                        'Error saving certificate');
                }
            });
        }

        // Update preview when form changes
        $('#course, #issue_date, #certificate_no, #language').on('change', function() {
            const courseName = $('#course option:selected').text();
            const issueDate = $('#issue_date').val();
            const certificateNo = $('#certificate_no').val();
            const language = $('#language').val();

            updateLanguage(language);
            
            $('#courseName').text(courseName !== '-- ជ្រើសរើសវគ្គសិក្សា / Select Course --' ? courseName : '_______________');
            $('#certNo').text(certificateNo || '_______________');
            $('#certDate').text(issueDate ? 
                new Date(issueDate).toLocaleDateString(language === 'km' ? 'km-KH' : 'en-US') : 
                '_______________');
        });
    </script>
</body>
</html>
