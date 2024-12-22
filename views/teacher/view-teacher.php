<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "គ្រូបង្រៀនមិនត្រូវបានរកឃើញទេ";
    header("Location: teacher-list.php");
    exit();
}

try {
    // Fetch teacher details
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teacher) {
        $_SESSION['error_message'] = "គ្រូបង្រៀនមិនត្រូវបានរកឃើញទេ";
        header("Location: teacher-list.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: teacher-list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>មើលព័ត៌មានគ្រូបង្រៀន - <?php echo htmlspecialchars($teacher['name']); ?></title>
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
            margin-bottom: 20px;
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
                            <h2>មើលព័ត៌មានគ្រូបង្រៀន</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="teacher-list.php">បញ្ជីគ្រូបង្រៀន</a></li>
                                    <li class="breadcrumb-item active">មើលព័ត៌មានគ្រូបង្រៀន</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="edit-teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> កែប្រែព័ត៌មាន
                            </a>
                            <a href="teacher-list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="<?php echo $basePath; ?>uploads/teachers/<?php echo !empty($teacher['photo']) ? htmlspecialchars($teacher['photo']) : 'default.png'; ?>" 
                                     class="profile-image" 
                                     alt="Teacher Photo">
                                <h3 class="mt-3"><?php echo htmlspecialchars($teacher['name']); ?></h3>
                                <p class="text-muted"><?php echo htmlspecialchars($teacher['teacher_id']); ?></p>
                                <span class="badge <?php echo $teacher['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?> mb-3">
                                    <?php echo $teacher['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                </span>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">មុខវិជ្ជា</p>
                                        <p><?php echo htmlspecialchars($teacher['subject']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">ភេទ</p>
                                        <p><?php echo htmlspecialchars($teacher['gender']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">ថ្ងៃខែឆ្នាំកំណើត</p>
                                        <p><?php echo date('d/m/Y', strtotime($teacher['date_of_birth'])); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">អាយុ</p>
                                        <p><?php 
                                            $birthDate = new DateTime($teacher['date_of_birth']);
                                            $today = new DateTime();
                                            $age = $today->diff($birthDate);
                                            echo $age->y . ' ឆ្នាំ';
                                        ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">អ៊ីមែល</p>
                                        <p><?php echo htmlspecialchars($teacher['email']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="info-label">លេខទូរស័ព្ទ</p>
                                        <p><?php echo !empty($teacher['phone']) ? htmlspecialchars($teacher['phone']) : 'N/A'; ?></p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <p class="info-label">អាស័យដ្ឋាន</p>
                                        <p><?php echo !empty($teacher['address']) ? nl2br(htmlspecialchars($teacher['address'])) : 'N/A'; ?></p>
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
</body>
</html>
