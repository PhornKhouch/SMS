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
    // Get all active teachers for dropdown
    $stmt = $pdo->query("SELECT id, name FROM teachers WHERE status = 'Active' ORDER BY name");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get class details
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate required fields
        $required_fields = ['class_id', 'class_name', 'grade'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("សូមបំពេញគ្រប់ប្រអប់ដែលចាំបាច់");
            }
        }

        // Check if class ID already exists for other classes
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE class_id = ? AND id != ?");
        $stmt->execute([$_POST['class_id'], $_GET['id']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("លេខសម្គាល់ថ្នាក់មានរួចហើយ");
        }

        // Update database
        $sql = "UPDATE classes SET 
                class_id = :class_id,
                class_name = :class_name,
                grade = :grade,
                section = :section,
                teacher_id = :teacher_id,
                capacity = :capacity,
                schedule = :schedule,
                room_number = :room_number,
                status = :status
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            ':class_id' => $_POST['class_id'],
            ':class_name' => $_POST['class_name'],
            ':grade' => $_POST['grade'],
            ':section' => $_POST['section'],
            ':teacher_id' => !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null,
            ':capacity' => !empty($_POST['capacity']) ? $_POST['capacity'] : null,
            ':schedule' => $_POST['schedule'],
            ':room_number' => $_POST['room_number'],
            ':status' => $_POST['status'] ?? 'Active',
            ':id' => $_GET['id']
        ]);

        if ($result) {
            $_SESSION['success_message'] = "ថ្នាក់រៀនត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ!";
            header("Location: class-list.php");
            exit();
        } else {
            throw new Exception("មានបញ្ហាក្នុងការធ្វើបច្ចុប្បន្នភាពថ្នាក់រៀន");
        }

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
    <title>កែប្រែថ្នាក់រៀន - <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .form-label {
            font-family: 'Battambang', cursive;
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
                            <h2>កែប្រែថ្នាក់រៀន</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="class-list.php">បញ្ជីថ្នាក់រៀន</a></li>
                                    <li class="breadcrumb-item active">កែប្រែថ្នាក់រៀន</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="class-list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $_GET['id']); ?>" method="post" id="editClassForm">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានមូលដ្ឋាន</h4>
                                    
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">លេខសម្គាល់ថ្នាក់ *</label>
                                        <input type="text" class="form-control" id="class_id" name="class_id" 
                                               value="<?php echo htmlspecialchars($class['class_id']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="class_name" class="form-label">ឈ្មោះថ្នាក់ *</label>
                                        <input type="text" class="form-control" id="class_name" name="class_name" 
                                               value="<?php echo htmlspecialchars($class['class_name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="grade" class="form-label">កម្រិត *</label>
                                        <input type="text" class="form-control" id="grade" name="grade" 
                                               value="<?php echo htmlspecialchars($class['grade']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="section" class="form-label">ផ្នែក</label>
                                        <input type="text" class="form-control" id="section" name="section" 
                                               value="<?php echo htmlspecialchars($class['section']); ?>">
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានបន្ថែម</h4>
                                    
                                    <div class="mb-3">
                                        <label for="teacher_id" class="form-label">គ្រូបង្រៀន</label>
                                        <select class="form-select" id="teacher_id" name="teacher_id">
                                            <option value="">ជ្រើសរើសគ្រូបង្រៀន</option>
                                            <?php foreach ($teachers as $teacher): ?>
                                                <option value="<?php echo $teacher['id']; ?>" 
                                                        <?php echo $class['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($teacher['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">ចំនួនសិស្សអតិបរមា</label>
                                        <input type="number" class="form-control" id="capacity" name="capacity" 
                                               value="<?php echo htmlspecialchars($class['capacity']); ?>" min="1">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="room_number" class="form-label">លេខបន្ទប់</label>
                                        <input type="text" class="form-control" id="room_number" name="room_number" 
                                               value="<?php echo htmlspecialchars($class['room_number']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="status" class="form-label">ស្ថានភាព</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="Active" <?php echo $class['status'] == 'Active' ? 'selected' : ''; ?>>សកម្ម</option>
                                            <option value="Inactive" <?php echo $class['status'] == 'Inactive' ? 'selected' : ''; ?>>អសកម្ម</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Schedule -->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="schedule" class="form-label">កាលវិភាគ</label>
                                        <textarea class="form-control" id="schedule" name="schedule" rows="3"><?php echo htmlspecialchars($class['schedule']); ?></textarea>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <hr>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> រក្សាទុកការផ្លាស់ប្តូរ
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> កំណត់ឡើងវិញ
                                    </button>
                                </div>
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
        // Form validation
        document.getElementById('editClassForm').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = ['class_id', 'class_name', 'grade'];
            
            requiredFields.forEach(field => {
                let input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    toastr.warning(`សូមបំពេញ ${field.replace('_', ' ')}`, 'Warning');
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Show success message
        <?php if (isset($_SESSION['success_message'])): ?>
        toastr.success('<?php echo $_SESSION['success_message']; ?>');
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        // Show error message
        <?php if (isset($_SESSION['error_message'])): ?>
        toastr.error('<?php echo $_SESSION['error_message']; ?>');
        <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
