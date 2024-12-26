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

// Fetch subjects
$subject_query = "SELECT * FROM subjects ORDER BY subject_name";
$subject_stmt = $pdo->prepare($subject_query);
$subject_stmt->execute();
$subjects = $subject_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate and sanitize input
        $student_id = $_POST['student_id'];
        $name = $_POST['name'];
        $class = $_POST['class']; // This will now be the subject ID
        $gender = $_POST['gender'];
        $date_of_birth = $_POST['date_of_birth'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $parent_name = $_POST['parent_name'];
        $parent_phone = $_POST['parent_phone'];
        $status = $_POST['status'];
        $photo = $_FILES['photo'];

        // Start transaction
        $pdo->beginTransaction();

        // Update student information
        $update_query = "UPDATE students SET 
                        student_id = :student_id,
                        name = :name,
                        class = :class,
                        gender = :gender,
                        date_of_birth = :date_of_birth,
                        email = :email,
                        phone = :phone,
                        address = :address,
                        parent_name = :parent_name,
                        parent_phone = :parent_phone,
                        status = :status
                        WHERE id = :id";

        $stmt = $pdo->prepare($update_query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':class', $class);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':date_of_birth', $date_of_birth);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':parent_name', $parent_name);
        $stmt->bindParam(':parent_phone', $parent_phone);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $_GET['id']);

        $stmt->execute();

        // Handle file upload
        if (isset($photo) && $photo['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $photo['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($filetype), $allowed)) {
                throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed');
            }

            $new_photo = time() . '.' . $filetype;
            $upload_path = $basePath . 'uploads/students/' . $new_photo;
            
            if (!move_uploaded_file($photo['tmp_name'], $upload_path)) {
                throw new Exception('Failed to upload file');
            }

            // Delete old photo if exists
            if (!empty($student['photo']) && file_exists($basePath . 'uploads/students/' . $student['photo'])) {
                unlink($basePath . 'uploads/students/' . $student['photo']);
            }

            // Update photo in database
            $update_photo_query = "UPDATE students SET photo = :photo WHERE id = :id";
            $update_photo_stmt = $pdo->prepare($update_photo_query);
            $update_photo_stmt->bindParam(':photo', $new_photo);
            $update_photo_stmt->bindParam(':id', $_GET['id']);
            $update_photo_stmt->execute();
        }

        // Commit transaction
        $pdo->commit();

        $_SESSION['success_message'] = "Student updated successfully!";
        header("Location: student-list.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>កែប្រែព័ត៌មានសិស្ស - <?php echo htmlspecialchars($student['name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                            <h2>កែប្រែព័ត៌មានសិស្ស</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="student-list.php">បញ្ជីសិស្ស</a></li>
                                    <li class="breadcrumb-item active">កែប្រែព័ត៌មានសិស្ស</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="student-list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $_GET['id']); ?>" method="post" enctype="multipart/form-data" id="editStudentForm">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានមូលដ្ឋាន</h4>
                                    
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">លេខសម្គាល់សិស្ស *</label>
                                        <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">ឈ្មោះពេញ *</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="class" class="form-label">ថ្នាក់ *</label>
                                        <select class="form-select" id="class" name="class" required>
                                            <option value="">ជ្រើសរើសថ្នាក់</option>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?php echo htmlspecialchars($subject['id']); ?>" 
                                                        <?php echo $student['class'] == $subject['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">ភេទ *</label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">ជ្រើសរើសភេទ</option>
                                            <option value="Male" <?php echo $student['gender'] == 'Male' ? 'selected' : ''; ?>>ប្រុស</option>
                                            <option value="Female" <?php echo $student['gender'] == 'Female' ? 'selected' : ''; ?>>ស្រី</option>
                                            <option value="Other" <?php echo $student['gender'] == 'Other' ? 'selected' : ''; ?>>ផ្សេងៗ</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="date_of_birth" class="form-label">ថ្ងៃខែឆ្នាំកំណើត *</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">ស្ថានភាព *</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Active" <?php echo $student['status'] == 'Active' ? 'selected' : ''; ?>>សកម្ម</option>
                                            <option value="Inactive" <?php echo $student['status'] == 'Inactive' ? 'selected' : ''; ?>>អសកម្ម</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានទំនាក់ទំនង</h4>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">អាស័យដ្ឋានអ៊ីមែល *</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">លេខទូរស័ព្ទ</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">អាស័យដ្ឋាន</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="photo" class="form-label">រូបថតសិស្ស</label>
                                        <?php if (!empty($student['photo'])): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo $basePath . 'uploads/students/' . $student['photo']; ?>" 
                                                     class="current-photo" 
                                                     alt="រូបថតបច្ចុប្បន្ន">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        <small class="text-muted">ទម្រង់អនុញ្ញាត៖ JPG, JPEG, PNG, GIF</small>
                                    </div>
                                </div>

                                <!-- Parent/Guardian Information -->
                                <div class="col-12 mt-4">
                                    <h4 class="mb-3">ព័ត៌មានឪពុកម្តាយ/អ្នកថែរក្សា</h4>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="parent_name" class="form-label">ឈ្មោះឪពុកម្តាយ/អ្នកថែរក្សា</label>
                                                <input type="text" class="form-control" id="parent_name" name="parent_name" value="<?php echo htmlspecialchars($student['parent_name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="parent_phone" class="form-label">លេខទូរស័ព្ទឪពុកម្តាយ/អ្នកថែរក្សា</label>
                                                <input type="tel" class="form-control" id="parent_phone" name="parent_phone" value="<?php echo htmlspecialchars($student['parent_phone']); ?>">
                                            </div>
                                        </div>
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
        document.getElementById('editStudentForm').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = ['student_id', 'name', 'class', 'gender', 'date_of_birth', 'email'];
            
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
