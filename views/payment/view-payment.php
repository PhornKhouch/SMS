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

// Fetch payment data with student information
try {
    $stmt = $pdo->prepare("SELECT p.*, s.name as student_name, s.student_id as student_code 
                          FROM payments p 
                          LEFT JOIN students s ON p.student_id = s.id 
                          WHERE p.id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error_message'] = "រកមិនឃើញព័ត៌មានការបង់ប្រាក់";
        header("Location: payment-list.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការទាញយកព័ត៌មាន៖ " . $e->getMessage();
    header("Location: payment-list.php");
    exit();
}

// Function to format payment status in Khmer
function getPaymentStatusKhmer($status) {
    switch ($status) {
        case 'Completed':
            return 'បានបញ្ចប់';
        case 'Pending':
            return 'កំពុងរង់ចាំ';
        case 'Failed':
            return 'បរាជ័យ';
        case 'Refunded':
            return 'បានបង្វិល';
        default:
            return $status;
    }
}

// Function to format payment method in Khmer
function getPaymentMethodKhmer($method) {
    switch ($method) {
        case 'Cash':
            return 'សាច់ប្រាក់';
        case 'ABA':
            return 'ABA';
        case 'ACLEDA':
            return 'ACLEDA';
        case 'Wing':
            return 'Wing';
        case 'True Money':
            return 'True Money';
        default:
            return $method;
    }
}

// Function to format payment type in Khmer
function getPaymentTypeKhmer($type) {
    switch ($type) {
        case 'Tuition Fee':
            return 'ថ្លៃសិក្សា';
        case 'Registration Fee':
            return 'ថ្លៃចុះឈ្មោះ';
        case 'Other Fee':
            return 'ថ្លៃផ្សេងៗ';
        default:
            return $type;
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>មើលព័ត៌មានលម្អិតនៃការបង់ប្រាក់</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .payment-details dt {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .payment-details dd {
            margin-bottom: 1rem;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-failed { background-color: #f8d7da; color: #721c24; }
        .status-refunded { background-color: #cce5ff; color: #004085; }
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h2>ព័ត៌មានលម្អិតនៃការបង់ប្រាក់</h2>
                            <div>
                                <a href="edit-payment.php?id=<?php echo $payment_id; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> កែប្រែ
                                </a>
                                <a href="payment-list.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-4">ព័ត៌មានសិស្ស</h4>
                                <dl class="payment-details">
                                    <dt>ឈ្មោះសិស្ស</dt>
                                    <dd><?php echo htmlspecialchars($payment['student_name']); ?></dd>

                                    <dt>លេខសម្គាល់សិស្ស</dt>
                                    <dd><?php echo htmlspecialchars($payment['student_code']); ?></dd>

                                    <dt>ឆ្នាំសិក្សា</dt>
                                    <dd><?php echo htmlspecialchars($payment['academic_year']); ?></dd>

                                    <dt>ឆមាស</dt>
                                    <dd>ឆមាសទី<?php echo htmlspecialchars($payment['semester']); ?></dd>
                                </dl>
                            </div>

                            <div class="col-md-6">
                                <h4 class="mb-4">ព័ត៌មានការបង់ប្រាក់</h4>
                                <dl class="payment-details">
                                    <dt>កាលបរិច្ឆេទបង់ប្រាក់</dt>
                                    <dd><?php echo date('d-m-Y', strtotime($payment['payment_date'])); ?></dd>

                                    <dt>ចំនួនទឹកប្រាក់</dt>
                                    <dd>$<?php echo number_format($payment['payment_amount'], 2); ?></dd>

                                    <dt>វិធីសាស្រ្តបង់ប្រាក់</dt>
                                    <dd><?php echo getPaymentMethodKhmer($payment['payment_method']); ?></dd>

                                    <dt>ស្ថានភាពបង់ប្រាក់</dt>
                                    <dd>
                                        <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                                            <?php echo getPaymentStatusKhmer($payment['payment_status']); ?>
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h4 class="mb-4">ព័ត៌មានបន្ថែម</h4>
                                <dl class="payment-details">
                                    <dt>បង់ប្រាក់សម្រាប់</dt>
                                    <dd><?php echo getPaymentTypeKhmer($payment['payment_for']); ?></dd>

                                    <?php if (!empty($payment['reference_number'])): ?>
                                    <dt>លេខយោង</dt>
                                    <dd><?php echo htmlspecialchars($payment['reference_number']); ?></dd>
                                    <?php endif; ?>

                                    <?php if (!empty($payment['description'])): ?>
                                    <dt>ការពិពណ៌នា</dt>
                                    <dd><?php echo nl2br(htmlspecialchars($payment['description'])); ?></dd>
                                    <?php endif; ?>

                                    <dt>កាលបរិច្ឆេទបង្កើត</dt>
                                    <dd><?php echo date('d-m-Y H:i:s', strtotime($payment['created_at'])); ?></dd>

                                    <dt>កាលបរិច្ឆេទកែប្រែ</dt>
                                    <dd><?php echo date('d-m-Y H:i:s', strtotime($payment['updated_at'])); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="generate-payment-pdf.php?id=<?php echo $payment['id']; ?>" 
                           class="btn btn-secondary" target="_blank">
                            <i class="fas fa-print"></i> បោះពុម្ព
                        </a>
                        <a href="edit-payment.php?id=<?php echo $payment['id']; ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-edit"></i> កែប្រែ
                        </a>
                        <a href="payment-list.php" class="btn btn-light">
                            <i class="fas fa-arrow-left"></i> ត្រឡប់ក្រោយ
                        </a>
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
