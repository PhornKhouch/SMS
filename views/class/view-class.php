<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ថ្នាក់រៀនមិនត្រូវបានរកឃើញទេ";
    header("Location: class-list.php");
    exit();
}

try {
    // Get class details with teacher name
    $stmt = $pdo->prepare("
        SELECT c.*, t.name as teacher_name 
        FROM classes c 
        LEFT JOIN teachers t ON c.teacher_id = t.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
        throw new Exception("ថ្នាក់រៀនមិនត្រូវបានរកឃើញទេ");
    }

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: class-list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>មើលព័ត៌មានថ្នាក់រៀន - <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .schedule-box {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
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
                            <h2>មើលព័ត៌មានថ្នាក់រៀន</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="class-list.php">បញ្ជីថ្នាក់រៀន</a></li>
                                    <li class="breadcrumb-item active">មើលព័ត៌មានថ្នាក់រៀន</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="edit-class.php?id=<?php echo $class['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> កែប្រែព័ត៌មាន
                            </a>
                            <a href="class-list.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h4 class="mb-3">ព័ត៌មានមូលដ្ឋាន</h4>
                                <table class="table">
                                    <tr>
                                        <td class="info-label">អត្តលេខថ្នាក់:</td>
                                        <td><?php echo htmlspecialchars($class['class_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">ឈ្មោះថ្នាក់:</td>
                                        <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">កម្រិត:</td>
                                        <td><?php echo htmlspecialchars($class['grade']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">ផ្នែក:</td>
                                        <td><?php echo htmlspecialchars($class['section'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">គ្រូបង្រៀន:</td>
                                        <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-md-6">
                                <h4 class="mb-3">ព័ត៌មានបន្ថែម</h4>
                                <table class="table">
                                    <tr>
                                        <td class="info-label">ចំនួនសិស្សអតិបរមា:</td>
                                        <td><?php echo $class['capacity'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">លេខបន្ទប់:</td>
                                        <td><?php echo htmlspecialchars($class['room_number'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">ស្ថានភាព:</td>
                                        <td>
                                            <span class="badge <?php echo $class['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $class['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">បង្កើតនៅ:</td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($class['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">ធ្វើបច្ចុប្បន្នភាពនៅ:</td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($class['updated_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Schedule -->
                            <?php if (!empty($class['schedule'])): ?>
                            <div class="col-12">
                                <h4 class="mb-3">កាលវិភាគ</h4>
                                <div class="schedule-box">
                                    <?php echo nl2br(htmlspecialchars($class['schedule'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
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
