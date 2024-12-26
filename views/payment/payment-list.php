<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default records per page
$records_per_page = isset($_GET['records_per_page']) ? (int)$_GET['records_per_page'] : 10;

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Get search term and filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$payment_type_filter = isset($_GET['payment_type']) ? $_GET['payment_type'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Count total records
    $count_query = "SELECT COUNT(*) as total FROM payments p 
                    LEFT JOIN students s ON p.student_id = s.id";
    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(s.name LIKE :search 
                              OR p.reference_number LIKE :search 
                              OR p.payment_method LIKE :search
                              OR p.pay_type LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if (!empty($payment_type_filter)) {
        $where_conditions[] = "p.pay_type = :payment_type";
        $params[':payment_type'] = $payment_type_filter;
    }

    if (!empty($status_filter)) {
        $where_conditions[] = "p.payment_status = :status";
        $params[':status'] = $status_filter;
    }

    if (!empty($where_conditions)) {
        $count_query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    $count_stmt = $pdo->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $result = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_count = (int)$result['total'];

    // Main query
    $query = "SELECT p.*, s.name as student_name, s.student_id as student_code, p.pay_type 
              FROM payments p 
              LEFT JOIN students s ON p.student_id = s.id";
    
    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    
    $query .= " ORDER BY p.payment_date DESC LIMIT :offset, :records_per_page";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->bindValue(':records_per_page', (int)$records_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    $payments = [];
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បញ្ជីការបង់ប្រាក់</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .payment-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        .status-Completed { background-color: #d4edda; color: #155724; }
        .status-Pending { background-color: #fff3cd; color: #856404; }
        .status-Failed { background-color: #f8d7da; color: #721c24; }
        .status-Refunded { background-color: #e2e3e5; color: #383d41; }
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
                            <h2>បញ្ជីការបង់ប្រាក់</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">បញ្ជីការបង់ប្រាក់</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="export-payments.php" class="btn btn-success me-2" id="exportBtn">
                                <i class="fas fa-file-excel"></i> នាំចេញ Excel
                            </a>
                            <a href="add-payment.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> បង់ប្រាក់ថ្មី
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="ស្វែងរក..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="records_per_page" onchange="this.form.submit()">
                                    <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo $records_per_page == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo $records_per_page == 100 ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="payment_type" onchange="this.form.submit()">
                                    <option value="">ប្រភេទបង់ប្រាក់ទាំងអស់</option>
                                    <option value="Full" <?php echo $payment_type_filter === 'Full' ? 'selected' : ''; ?>>បង់ពេញ</option>
                                    <option value="Monthly" <?php echo $payment_type_filter === 'Monthly' ? 'selected' : ''; ?>>ជាខែ</option>
                                    <option value="Half" <?php echo $payment_type_filter === 'Half' ? 'selected' : ''; ?>>ពាក់កណ្តាល</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status" onchange="this.form.submit()">
                                    <option value="">ស្ថានភាពទាំងអស់</option>
                                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>បានបញ្ចប់</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>កំពុងរង់ចាំ</option>
                                    <option value="Failed" <?php echo $status_filter === 'Failed' ? 'selected' : ''; ?>>បរាជ័យ</option>
                                    <option value="Refunded" <?php echo $status_filter === 'Refunded' ? 'selected' : ''; ?>>បានសងប្រាក់</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ល.រ</th>
                                        <th>អត្តលេខសិស្ស</th>
                                        <th>ឈ្មោះសិស្ស</th>
                                        <th>កាលបរិច្ឆេទបង់ប្រាក់</th>
                                        <th>ចំនួនទឹកប្រាក់</th>
                                        <th>វិធីសាស្ត្របង់ប្រាក់</th>
                                        <th>ប្រភេទបង់ប្រាក់</th>
                                        <th>ស្ថានភាព</th>
                                        <th>ឆ្នាំសិក្សា</th>
                                        <th>សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($payments)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">មិនមានទិន្នន័យទេ</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($payments as $index => $payment): ?>
                                        <tr>
                                            <td><?php echo $offset + $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($payment['student_code']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['student_name']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                            <td>$<?php echo number_format($payment['payment_amount'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                            <td>
                                                <?php 
                                                    $payTypeLabels = [
                                                        'Full' => 'បង់ពេញ',
                                                        'Monthly' => 'ជាខែ',
                                                        'Half' => 'ពាក់កណ្តាល'
                                                    ];
                                                    $payType = $payment['pay_type'] ?? '';
                                                    echo htmlspecialchars($payTypeLabels[$payType] ?? $payType);
                                                ?>
                                            </td>
                                            <td>
                                                <span class="payment-status status-<?php echo $payment['payment_status']; ?>">
                                                    <?php echo htmlspecialchars($payment['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment['academic_year']); ?></td>
                                            <td class="text-center">
                                                <a href="view-payment.php?id=<?php echo $payment['id']; ?>" 
                                                   class="btn btn-info btn-sm" title="មើល">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-payment.php?id=<?php echo $payment['id']; ?>" 
                                                   class="btn btn-primary btn-sm" title="កែប្រែ">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-danger btn-sm delete-payment" 
                                                        data-id="<?php echo $payment['id']; ?>" title="លុប">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_count > $records_per_page): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php
                                $total_pages = ceil($total_count / $records_per_page);
                                $pagination_range = 2;
                                
                                // Previous page
                                if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&records_per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>&payment_type=<?php echo urlencode($payment_type_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif;

                                // Page numbers
                                for ($i = max(1, $page - $pagination_range); $i <= min($total_pages, $page + $pagination_range); $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&records_per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>&payment_type=<?php echo urlencode($payment_type_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor;

                                // Next page
                                if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&records_per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>&payment_type=<?php echo urlencode($payment_type_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">កំពុងដំណើរការ...</span>
                    </div>
                    <h5>កំពុងដំណើរការ...</h5>
                    <p class="mb-0">សូមរង់ចាំមួយភ្លែត</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Handle Excel export with loading
        document.getElementById('exportBtn').addEventListener('click', function(e) {
            e.preventDefault();
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
            loadingModal.show();
            
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = this.href;
            
            // Add search parameter if exists
            const urlParams = new URLSearchParams(window.location.search);
            const search = urlParams.get('search');
            const payment_type = urlParams.get('payment_type');
            const status = urlParams.get('status');
            
            if (search) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'search';
                input.value = search;
                form.appendChild(input);
            }
            
            if (payment_type) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payment_type';
                input.value = payment_type;
                form.appendChild(input);
            }
            
            if (status) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'status';
                input.value = status;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();

            // Hide modal after download starts
            setTimeout(() => {
                loadingModal.hide();
            }, 3000);
        });

        // Delete Payment
        $('.delete-payment').click(function() {
            const id = $(this).data('id');
            const row = $(this).closest('tr');
            const studentName = row.find('td:eq(2)').text();

            Swal.fire({
                title: 'តើអ្នកប្រាកដទេ?',
                text: `តើអ្នកពិតជាចង់លុបការបង់ប្រាក់របស់សិស្ស "${studentName}" នេះមែនទេ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'យល់ព្រម',
                cancelButtonText: 'បោះបង់',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'កំពុងដំណើរការ...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send delete request
                    $.ajax({
                        url: 'delete-payment.php',
                        method: 'POST',
                        data: { id: id },
                        success: function(response) {
                            Swal.close();
                            
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'ជោគជ័យ!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'បរាជ័យ!',
                                    text: response.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'បរាជ័យ!',
                                text: 'មានបញ្ហាក្នុងការភ្ជាប់ទៅកាន់ម៉ាស៊ីនមេ'
                            });
                        }
                    });
                }
            });
        });

        // Show success message if exists
        <?php if (isset($_SESSION['success_message'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'ជោគជ័យ!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                showConfirmButton: false,
                timer: 1500
            });
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        // Show error message if exists
        <?php if (isset($_SESSION['error_message'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'បរាជ័យ!',
                text: '<?php echo $_SESSION['error_message']; ?>',
                showConfirmButton: false,
                timer: 1500
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    });
    </script>
</body>
</html>
