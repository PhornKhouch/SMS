<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "សិស្សមិនត្រូវបានរកឃើញទេ";
    header("Location: student-list.php");
    exit();
}

// Fetch student details
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $_SESSION['error_message'] = "សិស្សមិនត្រូវបានរកឃើញទេ";
        header("Location: student-list.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: student-list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>មើលព័ត៌មានសិស្ស - <?php echo htmlspecialchars($student['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .info-label {
            font-weight: bold;
            color: #666;
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
                            <h2>ព័ត៌មានលម្អិតរបស់សិស្ស</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="student-list.php">បញ្ជីសិស្ស</a></li>
                                    <li class="breadcrumb-item active">មើលព័ត៌មានសិស្ស</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="edit-student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> កែប្រែ
                            </a>
                            <a href="student-list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <img src="<?php echo !empty($student['photo']) ? $basePath . 'uploads/students/' . $student['photo'] : $basePath . 'assets/images/default-avatar.png'; ?>" 
                                     class="profile-image mb-3" 
                                     alt="រូបថតសិស្ស">
                                <h4><?php echo htmlspecialchars($student['name']); ?></h4>
                                <p class="text-muted"><?php echo htmlspecialchars($student['student_id']); ?></p>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">ថ្នាក់</p>
                                        <p><?php echo htmlspecialchars($student['class']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">ភេទ</p>
                                        <p><?php echo $student['gender'] == 'Male' ? 'ប្រុស' : ($student['gender'] == 'Female' ? 'ស្រី' : 'ផ្សេងៗ'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">ថ្ងៃខែឆ្នាំកំណើត</p>
                                        <p><?php echo htmlspecialchars($student['date_of_birth']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">អ៊ីមែល</p>
                                        <p><?php echo htmlspecialchars($student['email']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">លេខទូរស័ព្ទ</p>
                                        <p><?php echo htmlspecialchars($student['phone'] ?: 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">ស្ថានភាព</p>
                                        <p>
                                            <span class="badge bg-<?php echo $student['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo $student['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <p class="info-label">អាស័យដ្ឋាន</p>
                                        <p><?php echo htmlspecialchars($student['address'] ?: 'N/A'); ?></p>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <h5 class="mb-3">ព័ត៌មានឪពុកម្តាយ/អាណាព្យាបាល</h5>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">ឈ្មោះឪពុកម្តាយ/អាណាព្យាបាល</p>
                                        <p><?php echo htmlspecialchars($student['parent_name'] ?: 'N/A'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">លេខទូរស័ព្ទឪពុកម្តាយ/អាណាព្យាបាល</p>
                                        <p><?php echo htmlspecialchars($student['parent_phone'] ?: 'N/A'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>
