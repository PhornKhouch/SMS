<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// Get filters
$class_filter = isset($_GET['class']) ? $_GET['class'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Fetch all subjects for the filter
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $pdo->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build where clause for the payment report
$where_conditions = [];
$params = [];

if (!empty($class_filter)) {
    $where_conditions[] = "s.class = :class";
    $params[':class'] = $class_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "p.payment_status = :month";
    $params[':status'] = $status_filter;
}

if (!empty($from_date)) {
    $where_conditions[] = "p.payment_date >= :from_date";
    $params[':from_date'] = $from_date;
}

if (!empty($to_date)) {
    $where_conditions[] = "p.payment_date <= :to_date";
    $params[':to_date'] = $to_date;
}

$where = '';
if (!empty($where_conditions)) {
    $where = "WHERE " . implode(" AND ", $where_conditions);
}

// Fetch payment report data
$query = "SELECT s.student_id, s.name as student_name, 
          sub.subject_name as class_name, 
          p.payment_date, p.payment_amount as amount, p.payment_status as status,
          p.payment_method
          FROM students s 
          LEFT JOIN subjects sub ON s.class = sub.id 
          LEFT JOIN payments p ON s.id = p.student_id
          Where p.pay_type in ('Monthly', 'Half') AND payment_status='Pending'
          ORDER BY p.payment_date DESC";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_amount = 0;
foreach ($payments as $payment) {
    if ($payment['amount'] !== null) {
        $total_amount += $payment['amount'];
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>របាយការណ៍ការបង់ថ្លៃសិក្សា - ក្លឹបកូដ</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .table th, .table td, 
        .form-control, .form-select, 
        .btn, .page-link {
            font-family: 'Battambang', cursive;
        }
        .table {
            border: 1px solid #dee2e6;
        }
        .table thead th {
            background-color: #198754;
            color: white;
            border-bottom: 2px solid #0f5132;
            vertical-align: middle;
            font-weight: 500;
        }
        .table td {
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }
        .table tbody tr:hover {
            background-color: #f5f5f5;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        @media print {
            .no-print {
                display: none;
            }
            .table thead th {
                background-color: #198754 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
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
                            <h2>របាយការណ៍ការបង់ថ្លៃសិក្សា</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../../index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item">របាយការណ៍</li>
                                    <li class="breadcrumb-item active">របាយការណ៍ការបង់ថ្លៃសិក្សា</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="no-print">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-export"></i> នាំចេញ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li>
                                        <a class="dropdown-item export-option" href="#" data-format="pdf">
                                            <i class="fas fa-file-pdf text-danger"></i> PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-option" href="#" data-format="excel">
                                            <i class="fas fa-file-excel text-success"></i> Excel
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-option" href="#" data-format="word">
                                            <i class="fas fa-file-word text-primary"></i> Word
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item export-option" href="#" data-format="image">
                                            <i class="fas fa-file-image text-info"></i> រូបភាព
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <!-- Filters -->
                        <form action="" method="GET" class="row g-3 mb-4 no-print">
                            <div class="col-md-3">
                                <select name="class" class="form-select">
                                    <option value="">ថ្នាក់ទាំងអស់</option>
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['id']; ?>" <?php echo $class_filter == $subject['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">ស្ថានភាពទាំងអស់</option>
                                    <option value="Paid" <?php echo $status_filter === 'Paid' ? 'selected' : ''; ?>>បានបង់</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>មិនទាន់បង់</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="from_date" class="form-control datepicker" placeholder="ចាប់ពីថ្ងៃទី" value="<?php echo $from_date; ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="to_date" class="form-control datepicker" placeholder="ដល់ថ្ងៃទី" value="<?php echo $to_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> ស្វែងរក
                                </button>
                                <a href="RPTPayment.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> កំណត់ឡើងវិញ
                                </a>
                            </div>
                        </form>

                        <!-- Report Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>អត្តលេខសិស្ស</th>
                                        <th>ឈ្មោះសិស្ស</th>
                                        <th>ថ្នាក់</th>
                                        <th>កាលបរិច្ឆេទបង់ប្រាក់</th>
                                        <th>ចំនួនទឹកប្រាក់</th>
                                        <th>វិធីបង់ប្រាក់</th>
                                        <th>ស្ថានភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['class_name']); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                        <td class="text-end">$<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $payment['status'] == 'Paid' ? 'success' : 'warning'; ?>">
                                                <?php echo $payment['status'] == 'Paid' ? 'បានបង់' : 'មិនទាន់បង់'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="total-row">
                                        <td colspan="4" class="text-end">សរុប:</td>
                                        <td class="text-end">$<?php echo number_format($total_amount, 2); ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date pickers
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        // Handle export options
        $('.export-option').click(function(e) {
            e.preventDefault();
            const format = $(this).data('format');
            
            // Create a form to submit the current filters
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export-payment.php';
            
            // Add the export type
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'export_type';
            formatInput.value = format;
            form.appendChild(formatInput);
            
            // Add current filters
            const urlParams = new URLSearchParams(window.location.search);
            ['class', 'status', 'from_date', 'to_date'].forEach(param => {
                const value = urlParams.get(param);
                if (value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = param;
                    input.value = value;
                    form.appendChild(input);
                }
            });
            
            // Submit the form
            document.body.appendChild(form);
            form.submit();
        });
    </script>
</body>
</html>
