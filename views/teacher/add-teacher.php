<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

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

        // Check if teacher ID already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teachers WHERE teacher_id = ?");
        $stmt->execute([$_POST['teacher_id']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("លេខសម្គាល់គ្រូបង្រៀនមានរួចហើយ");
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM teachers WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("អ៊ីមែលនេះត្រូវបានប្រើរួចហើយ");
        }

        // Handle file upload
        $photo = '';
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
        }

        // Insert into database
        $sql = "INSERT INTO teachers (teacher_id, name, subject, gender, date_of_birth, email, phone, address, photo, status) 
                VALUES (:teacher_id, :name, :subject, :gender, :date_of_birth, :email, :phone, :address, :photo, :status)";
        
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            ':teacher_id' => $_POST['teacher_id'],
            ':name' => $_POST['name'],
            ':subject' => $_POST['subject'],
            ':gender' => $_POST['gender'],
            ':date_of_birth' => $_POST['date_of_birth'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'] ?? null,
            ':address' => $_POST['address'] ?? null,
            ':photo' => $photo,
            ':status' => $_POST['status'] ?? 'Active'
        ]);

        if ($result) {
            $_SESSION['success_message'] = "គ្រូបង្រៀនត្រូវបានបន្ថែមដោយជោគជ័យ!";
            header("Location: teacher-list.php");
            exit();
        } else {
            throw new Exception("មានបញ្ហាក្នុងការបន្ថែមគ្រូបង្រៀន");
        }

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
    <title>បន្ថែមគ្រូបង្រៀន</title>
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
                            <h2>បន្ថែមគ្រូបង្រៀន</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item"><a href="teacher-list.php">បញ្ជីគ្រូបង្រៀន</a></li>
                                    <li class="breadcrumb-item active">បន្ថែមគ្រូបង្រៀន</li>
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
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" id="addTeacherForm">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h4 class="mb-3">ព័ត៌មានមូលដ្ឋាន</h4>
                                    
                                    <div class="mb-3">
                                        <label for="teacher_id" class="form-label">លេខសម្គាល់គ្រូបង្រៀន *</label>
                                        <input type="text" class="form-control" id="teacher_id" name="teacher_id" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">ឈ្មោះពេញ *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">មុខវិជ្ជា *</label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="">ជ្រើសមុខវិជ្ជា</option>
                                            <?php
                                                $query = "SELECT * FROM subjects WHERE status = '1' ORDER BY subject_name ASC";
                                                $result = mysqli_query($conn, $query);
                                                
                                                if (!$result) {
                                                    echo "<!-- Error: " . mysqli_error($conn) . " -->";
                                                    echo "<option value=''>No classes available</option>";
                                                } else {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo "<option value='" . htmlspecialchars($row['subject_code']) . "'>" . 
                                                             htmlspecialchars($row['subject_name']) . "</option>";
                                                    }
                                                }
                                            ?> 
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">ភេទ *</label>
                                        <select class="form-select" id="gender" name="gender" required>
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

                                    <div class="mb-3">
                                        <label for="status" class="form-label">ស្ថានភាព</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="Active">សកម្ម</option>
                                            <option value="Inactive">អសកម្ម</option>
                                        </select>
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
                                        <label for="photo" class="form-label">រូបថត</label>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        <small class="text-muted">ទម្រង់អនុញ្ញាត៖ JPG, JPEG, PNG, GIF</small>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="col-12 mt-4">
                                    <hr>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> រក្សាទុក
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
        document.getElementById('addTeacherForm').addEventListener('submit', function(e) {
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
