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

// Fetch teacher details
try {
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate input
        $required_fields = ['teacher_id', 'name', 'subject', 'gender', 'date_of_birth', 'email'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("សូមបំពេញគ្រប់ប្រអប់ដែលចាំបាច់");
            }
        }

        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ");
        }

        // Check if teacher ID already exists (excluding current teacher)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teachers WHERE teacher_id = ? AND id != ?");
        $stmt->execute([$_POST['teacher_id'], $_GET['id']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("លេខសម្គាល់គ្រូបង្រៀនមានរួចហើយ");
        }

        // Check if email already exists (excluding current teacher)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teachers WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $_GET['id']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("អ៊ីមែលនេះត្រូវបានប្រើរួចហើយ");
        }

        // Handle file upload
        $photo = $teacher['photo']; // Keep existing photo by default
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['photo']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($filetype), $allowed)) {
                throw new Exception('អនុញ្ញាតតែឯកសារ JPG, JPEG, PNG & GIF ប៉ុណ្ណោះ');
            }

            $photo = time() . '.' . $filetype;
            $upload_path = $basePath . 'uploads/teachers/' . $photo;
            
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                throw new Exception('បរាជ័យក្នុងការបញ្ជូនឯកសារ');
            }

            // Delete old photo if exists
            if (!empty($teacher['photo']) && file_exists($basePath . 'uploads/teachers/' . $teacher['photo'])) {
                unlink($basePath . 'uploads/teachers/' . $teacher['photo']);
            }
        }

        // Update database
        $sql = "UPDATE teachers SET 
                teacher_id = :teacher_id,
                name = :name,
                subject = :subject,
                gender = :gender,
                date_of_birth = :date_of_birth,
                email = :email,
                phone = :phone,
                address = :address,
                photo = :photo,
                status = :status
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':teacher_id' => $_POST['teacher_id'],
            ':name' => $_POST['name'],
            ':subject' => $_POST['subject'],
            ':gender' => $_POST['gender'],
            ':date_of_birth' => $_POST['date_of_birth'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'] ?? null,
            ':address' => $_POST['address'] ?? null,
            ':photo' => $photo,
            ':status' => $_POST['status'],
            ':id' => $_GET['id']
        ]);

        $_SESSION['success_message'] = "គ្រូបង្រៀនត្រូវបានកែប្រែដោយជោគជ័យ!";
        header("Location: teacher-list.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>កែប្រែព័ត៌មានគ្រូបង្រៀន - <?php echo htmlspecialchars($teacher['name']); ?></title>
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
        .current-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 10px;
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
                            <h2>កែប្រែព័ត៌មានគ្រូបង្រៀន</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="teacher-list.php">បញ្ជីគ្រូបង្រៀន</a></li>
                                    <li class="breadcrumb-item active">កែប្រែព័ត៌មានគ្រូបង្រៀន</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="teacher-list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $_GET['id']); ?>" method="post" enctype="multipart/form-data" id="editTeacherForm">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានមូលដ្ឋាន</h4>
                                    
                                    <div class="mb-3">
                                        <label for="teacher_id" class="form-label">លេខសម្គាល់គ្រូបង្រៀន *</label>
                                        <input type="text" class="form-control" id="teacher_id" name="teacher_id" value="<?php echo htmlspecialchars($teacher['teacher_id']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">ឈ្មោះពេញ *</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">មុខវិជ្ជា *</label>
                                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($teacher['subject']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">ភេទ *</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">ជ្រើសរើសភេទ</option>
                                            <option value="Male" <?php echo $teacher['gender'] == 'Male' ? 'selected' : ''; ?>>ប្រុស</option>
                                            <option value="Female" <?php echo $teacher['gender'] == 'Female' ? 'selected' : ''; ?>>ស្រី</option>
                                            <option value="Other" <?php echo $teacher['gender'] == 'Other' ? 'selected' : ''; ?>>ផ្សេងៗ</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="date_of_birth" class="form-label">ថ្ងៃខែឆ្នាំកំណើត *</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($teacher['date_of_birth']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">ស្ថានភាព</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="Active" <?php echo $teacher['status'] == 'Active' ? 'selected' : ''; ?>>សកម្ម</option>
                                            <option value="Inactive" <?php echo $teacher['status'] == 'Inactive' ? 'selected' : ''; ?>>អសកម្ម</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានទំនាក់ទំនង</h4>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">អាស័យដ្ឋានអ៊ីមែល *</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">លេខទូរស័ព្ទ</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher['phone']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">អាស័យដ្ឋាន</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($teacher['address']); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="photo" class="form-label">រូបថត</label>
                                        <?php if (!empty($teacher['photo'])): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo $basePath . 'uploads/teachers/' . $teacher['photo']; ?>" 
                                                     class="current-photo" 
                                                     alt="រូបថតបច្ចុប្បន្ន">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        <small class="text-muted">ទម្រង់អនុញ្ញាត៖ JPG, JPEG, PNG, GIF</small>
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
        document.getElementById('editTeacherForm').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = ['teacher_id', 'name', 'subject', 'gender', 'date_of_birth', 'email'];
            
            requiredFields.forEach(field => {
                let input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    toastr.warning(`សូមបំពេញ ${field.replace('_', ' ')}`, 'Warning');
                }
            });

            // Validate email format
            let email = document.getElementById('email');
            if (email.value && !email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                isValid = false;
                toastr.warning('សូមបំពេញអាស័យដ្ឋានអ៊ីមែលដែលត្រឹមត្រូវ', 'Warning');
            }

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
