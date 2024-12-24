<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// Get payment ID from URL
$payment_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$payment_id) {
    $_SESSION['error_message'] = "មិនមានលេខសម្គាល់ការបង់ប្រាក់";
    header("Location: payment-list.php");
    exit();
}

// Fetch payment data
try {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error_message'] = "រកមិនឃើញព័ត៌មានការបង់ប្រាក់";
        header("Location: payment-list.php");
        exit();
    }

    // Fetch students for dropdown
    $stmt = $pdo->query("SELECT id, name as student_name, student_id as student_code FROM students ORDER BY name");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការទាញយកព័ត៌មាន៖ " . $e->getMessage();
    header("Location: payment-list.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate and sanitize input
        $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_NUMBER_INT);
        $payment_date = htmlspecialchars($_POST['payment_date'] ?? '', ENT_QUOTES, 'UTF-8');
        $payment_amount = filter_input(INPUT_POST, 'payment_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $payment_method = htmlspecialchars($_POST['payment_method'] ?? '', ENT_QUOTES, 'UTF-8');
        $payment_status = htmlspecialchars($_POST['payment_status'] ?? '', ENT_QUOTES, 'UTF-8');
        $academic_year = htmlspecialchars($_POST['academic_year'] ?? '', ENT_QUOTES, 'UTF-8');
        $semester = htmlspecialchars($_POST['semester'] ?? '', ENT_QUOTES, 'UTF-8');
        $payment_for = htmlspecialchars($_POST['payment_for'] ?? '', ENT_QUOTES, 'UTF-8');
        $reference_number = htmlspecialchars($_POST['reference_number'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');

        // Validate required fields
        if (empty($student_id) || empty($payment_date) || empty($payment_amount) || 
            empty($payment_method) || empty($payment_status) || empty($academic_year) || 
            empty($semester) || empty($payment_for)) {
            throw new Exception("សូមបំពេញគ្រប់ប្រអប់ដែលចាំបាច់");
        }

        // Update database
        $query = "UPDATE payments SET 
            student_id = :student_id,
            payment_date = :payment_date,
            payment_amount = :payment_amount,
            payment_method = :payment_method,
            payment_status = :payment_status,
            academic_year = :academic_year,
            semester = :semester,
            payment_for = :payment_for,
            reference_number = :reference_number,
            description = :description
            WHERE id = :id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':student_id' => $student_id,
            ':payment_date' => $payment_date,
            ':payment_amount' => $payment_amount,
            ':payment_method' => $payment_method,
            ':payment_status' => $payment_status,
            ':academic_year' => $academic_year,
            ':semester' => $semester,
            ':payment_for' => $payment_for,
            ':reference_number' => $reference_number,
            ':description' => $description,
            ':id' => $payment_id
        ]);

        $_SESSION['success_message'] = "ការបង់ប្រាក់ត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ";
        header("Location: payment-list.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "មានបញ្ហាក្នុងការធ្វើបច្ចុប្បន្នភាពការបង់ប្រាក់៖ " . $e->getMessage();
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
    <title>កែប្រែការបង់ប្រាក់</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
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
                <div class="row mb-3">
                    <div class="col">
                        <h2>កែប្រែការបង់ប្រាក់</h2>
                    </div>
                </div>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">សិស្ស *</label>
                                        <select class="form-select" name="student_id" id="student_id" required>
                                            <option value="">ជ្រើសរើសសិស្ស</option>
                                            <?php foreach ($students as $student): ?>
                                                <option value="<?php echo $student['id']; ?>" <?php echo ($student['id'] == $payment['student_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($student['student_name'] . ' (' . $student['student_code'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_date" class="form-label">កាលបរិច្ឆេទបង់ប្រាក់ *</label>
                                        <input type="date" class="form-control" name="payment_date" id="payment_date" value="<?php echo htmlspecialchars($payment['payment_date']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_amount" class="form-label">ចំនួនទឹកប្រាក់ *</label>
                                        <input type="number" step="0.01" class="form-control" name="payment_amount" id="payment_amount" value="<?php echo htmlspecialchars($payment['payment_amount']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">វិធីសាស្រ្តបង់ប្រាក់ *</label>
                                        <select class="form-select" name="payment_method" id="payment_method" required>
                                            <option value="">ជ្រើសរើសវិធីសាស្រ្តបង់ប្រាក់</option>
                                            <option value="Cash" <?php echo ($payment['payment_method'] == 'Cash') ? 'selected' : ''; ?>>សាច់ប្រាក់</option>
                                            <option value="ABA" <?php echo ($payment['payment_method'] == 'ABA') ? 'selected' : ''; ?>>ABA</option>
                                            <option value="ACLEDA" <?php echo ($payment['payment_method'] == 'ACLEDA') ? 'selected' : ''; ?>>ACLEDA</option>
                                            <option value="Wing" <?php echo ($payment['payment_method'] == 'Wing') ? 'selected' : ''; ?>>Wing</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_status" class="form-label">ស្ថានភាពបង់ប្រាក់ *</label>
                                        <select class="form-select" name="payment_status" id="payment_status" required>
                                            <option value="">ជ្រើសរើសស្ថានភាព</option>
                                            <option value="Completed" <?php echo ($payment['payment_status'] == 'Completed') ? 'selected' : ''; ?>>បានបញ្ចប់</option>
                                            <option value="Pending" <?php echo ($payment['payment_status'] == 'Pending') ? 'selected' : ''; ?>>កំពុងរង់ចាំ</option>
                                            <option value="Failed" <?php echo ($payment['payment_status'] == 'Failed') ? 'selected' : ''; ?>>បរាជ័យ</option>
                                            <option value="Refunded" <?php echo ($payment['payment_status'] == 'Refunded') ? 'selected' : ''; ?>>បានបង្វិល</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="academic_year" class="form-label">ឆ្នាំសិក្សា *</label>
                                        <input type="text" class="form-control" name="academic_year" id="academic_year" value="<?php echo htmlspecialchars($payment['academic_year']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="semester" class="form-label">ឆមាស *</label>
                                        <select class="form-select" name="semester" id="semester" required>
                                            <option value="">ជ្រើសរើសឆមាស</option>
                                            <option value="1" <?php echo ($payment['semester'] == '1') ? 'selected' : ''; ?>>ឆមាសទី១</option>
                                            <option value="2" <?php echo ($payment['semester'] == '2') ? 'selected' : ''; ?>>ឆមាសទី២</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_for" class="form-label">បង់ប្រាក់សម្រាប់ *</label>
                                        <input type="text" class="form-control" name="payment_for" id="payment_for" value="<?php echo htmlspecialchars($payment['payment_for']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reference_number" class="form-label">លេខយោង</label>
                                        <input type="text" class="form-control" name="reference_number" id="reference_number" value="<?php echo htmlspecialchars($payment['reference_number']); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">ការពិពណ៌នា</label>
                                        <textarea class="form-control" name="description" id="description" rows="3"><?php echo htmlspecialchars($payment['description']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> រក្សាទុក
                                    </button>
                                    <a href="payment-list.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> បោះបង់
                                    </a>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $basePath; ?>js/script.js"></script>
</body>
</html>
