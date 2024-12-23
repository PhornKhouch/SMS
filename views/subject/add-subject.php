<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get all active teachers for the dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM teachers WHERE status = 1 ORDER BY name");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error fetching teachers: " . $e->getMessage();
    $teachers = [];
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate input
        $subject_code = trim($_POST['subject_code']);
        $subject_name = trim($_POST['subject_name']);
        $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;
        $credits = (int)$_POST['credits'];
        $description = trim($_POST['description']);

        // Check if subject code already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE subject_code = ?");
        $stmt->execute([$subject_code]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("កូដមុខវិជ្ជានេះមានរួចហើយ");
        }

        // Insert new subject
        $sql = "INSERT INTO subjects (subject_code, subject_name, teacher_id, credits, description) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$subject_code, $subject_name, $teacher_id, $credits, $description]);

        $_SESSION['success_message'] = "បានបន្ថែមមុខវិជ្ជាថ្មីដោយជោគជ័យ";
        header("Location: subject-list.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បន្ថែមមុខវិជ្ជាថ្មី</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .required::after {
            content: " *";
            color: red;
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
                            <h2>បន្ថែមមុខវិជ្ជាថ្មី</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="subject-list.php">បញ្ជីមុខវិជ្ជា</a></li>
                                    <li class="breadcrumb-item active">បន្ថែមមុខវិជ្ជាថ្មី</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Add Subject Form -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subject_code" class="form-label required">កូដមុខវិជ្ជា</label>
                                        <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                                        <div class="invalid-feedback">
                                            សូមបញ្ចូលកូដមុខវិជ្ជា
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subject_name" class="form-label required">ឈ្មោះមុខវិជ្ជា</label>
                                        <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                                        <div class="invalid-feedback">
                                            សូមបញ្ចូលឈ្មោះមុខវិជ្ជា
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="teacher_id" class="form-label">គ្រូបង្រៀន</label>
                                        <select class="form-select" id="teacher_id" name="teacher_id">
                                            <option value="">ជ្រើសរើសគ្រូបង្រៀន</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?php echo $teacher['id']; ?>">
                                                    <?php echo htmlspecialchars($teacher['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="credits" class="form-label required">ឥណទាន</label>
                                        <input type="number" class="form-control" id="credits" name="credits" min="0" required>
                                        <div class="invalid-feedback">
                                            សូមបញ្ចូលចំនួនឥណទាន
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">ពិពណ៌នា</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>

                            <div class="text-end">
                                <a href="subject-list.php" class="btn btn-secondary">បោះបង់</a>
                                <button type="submit" class="btn btn-primary">បន្ថែម</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
    // Initialize toastr options
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()

    // Show success message if exists
    <?php if (isset($_SESSION['success_message'])): ?>
        toastr.success('<?php echo $_SESSION['success_message']; ?>');
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    // Show error message if exists
    <?php if (isset($_SESSION['error_message'])): ?>
        toastr.error('<?php echo $_SESSION['error_message']; ?>');
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    </script>
</body>
</html>
