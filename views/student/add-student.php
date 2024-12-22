<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create uploads directory if it doesn't exist
        if (!file_exists($basePath.'uploads/students')) {
            mkdir($basePath.'uploads/students', 0777, true);
        }

        // Validate input
        $required_fields = ['student_id', 'name', 'class', 'gender', 'date_of_birth', 'email'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required fields");
            }
        }

        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Handle file upload
        $photo = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['photo']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($filetype), $allowed)) {
                throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed');
            }

            $photo = time() . '.' . $filetype;
            $upload_path = $basePath.'uploads/students/' . $photo;
            
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                throw new Exception('Failed to upload file');
            }
        }

        // Check if student ID already exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
        $check_stmt->execute([$_POST['student_id']]);
        if ($check_stmt->fetchColumn() > 0) {
            throw new Exception("Student ID already exists");
        }

        // Check if email already exists
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
        $check_stmt->execute([$_POST['email']]);
        if ($check_stmt->fetchColumn() > 0) {
            throw new Exception("Email already exists");
        }

        // Insert into database
        $sql = "INSERT INTO students (student_id, name, class, gender, date_of_birth, email, phone, address, 
                                    parent_name, parent_phone, photo, status, created_at) 
                VALUES (:student_id, :name, :class, :gender, :date_of_birth, :email, :phone, :address,
                        :parent_name, :parent_phone, :photo, :status, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            ':student_id' => $_POST['student_id'],
            ':name' => $_POST['name'],
            ':class' => $_POST['class'],
            ':gender' => $_POST['gender'],
            ':date_of_birth' => $_POST['date_of_birth'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'] ?? null,
            ':address' => $_POST['address'] ?? null,
            ':parent_name' => $_POST['parent_name'] ?? null,
            ':parent_phone' => $_POST['parent_phone'] ?? null,
            ':photo' => $photo,
            ':status' => 'Active'
        ]);

        $_SESSION['success_message'] = "Student added successfully!";
        header("Location: student-list.php");
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
    <title>Add Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        .form-label {
            font-family: 'Battambang', cursive;
        }
        h4 {
            font-family: 'Battambang', cursive;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include $basePath.'includes/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation -->
            <?php include $basePath.'includes/topnav.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid">
                <div class="content-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 style="font-family: 'Battambang', cursive;">បន្ថែមសិស្សថ្មី</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb" style="font-family: 'Battambang', cursive;">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item">សិស្ស</li>
                                    <li class="breadcrumb-item active">បន្ថែមសិស្ស</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="student-list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" id="addStudentForm">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានមូលដ្ឋាន</h4>
                                    
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">លេខសម្គាល់សិស្ស *</label>
                                        <input type="text" class="form-control" id="student_id" name="student_id" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">ឈ្មោះពេញ *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="class" class="form-label">ថ្នាក់ *</label>
                                        <select class="form-select" id="class" name="class" required style="font-family: 'Battambang', cursive;">
                                            <option value="">ជ្រើសរើសថ្នាក់</option>
                                            <option value="Class 1">ថ្នាក់ទី ១</option>
                                            <option value="Class 2">ថ្នាក់ទី ២</option>
                                            <option value="Class 3">ថ្នាក់ទី ៣</option>
                                            <option value="Class 4">ថ្នាក់ទី ៤</option>
                                            <option value="Class 5">ថ្នាក់ទី ៥</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">ភេទ *</label>
                                        <select class="form-select" id="gender" name="gender" required style="font-family: 'Battambang', cursive;">
                                            <option value="">ជ្រើសរើសភេទ</option>
                                            <option value="Male">ប្រុស</option>
                                            <option value="Female">ស្រី</option>
                                            <option value="Other">ផ្សេងៗ</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="date_of_birth" class="form-label">ថ្ងៃខែឆ្នាំកំណើត *</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានទំនាក់ទំនង</h4>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">អាស័យដ្ឋានអ៊ីមែល *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">លេខទូរស័ព្ទ</label>
                                        <input type="tel" class="form-control" id="phone" name="phone">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">អាស័យដ្ឋាន</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="photo" class="form-label">រូបភាពសិស្ស</label>
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
                                                <input type="text" class="form-control" id="parent_name" name="parent_name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="parent_phone" class="form-label">លេខទូរស័ព្ទឪពុកម្តាយ/អ្នកថែរក្សា</label>
                                                <input type="tel" class="form-control" id="parent_phone" name="parent_phone">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <hr>
                                    <button type="submit" class="btn btn-primary" style="font-family: 'Battambang', cursive;">
                                        <i class="fas fa-save"></i> រក្សាទុកសិស្ស
                                    </button>
                                    <button type="reset" class="btn btn-secondary" style="font-family: 'Battambang', cursive;">
                                        <i class="fas fa-undo"></i> កំណត់ឡើងវិញ
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include $basePath.'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Form validation
        document.getElementById('addStudentForm').addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = ['student_id', 'name', 'class', 'gender', 'date_of_birth', 'email'];
            
            requiredFields.forEach(field => {
                let input = document.getElementById(field);
                if (!input.value.trim()) {
                    isValid = false;
                    toastr.warning(`សូមបំពេញពាក្យគម្រូលេខសម្គាល់សិស្ស ${field.replace('_', ' ')}`, 'Warning');
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
    </script>
</body>
</html>
