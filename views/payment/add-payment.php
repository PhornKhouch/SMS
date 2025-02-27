<?php
session_start();
require_once "../../includes/config.php";
require_once "../../includes/telegram_helper.php";

// Define base path for links
$basePath = "../../";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Common data for all payments
        $student_id = filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_NUMBER_INT);
        $payment_method = htmlspecialchars($_POST['payment_method'] ?? '', ENT_QUOTES, 'UTF-8');
        $payment_status = htmlspecialchars($_POST['payment_status'] ?? '', ENT_QUOTES, 'UTF-8');
        $pay_type = htmlspecialchars($_POST['pay_type'] ?? '', ENT_QUOTES, 'UTF-8');
        $academic_year = htmlspecialchars($_POST['academic_year'] ?? '', ENT_QUOTES, 'UTF-8');
        $semester = htmlspecialchars($_POST['semester'] ?? '', ENT_QUOTES, 'UTF-8');
        $payment_for = htmlspecialchars($_POST['payment_for'] ?? '', ENT_QUOTES, 'UTF-8');
        $reference_number = htmlspecialchars($_POST['reference_number'] ?? '', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $created_by = $_SESSION['user_id'] ?? null;

        // Validate required fields
        if (empty($student_id) || empty($payment_method) || empty($payment_status) || 
            empty($academic_year) || empty($semester) || empty($payment_for) || empty($pay_type)) {
            throw new Exception("សូមបំពេញគ្រប់ប្រអប់ដែលចាំបាច់");
        }

        // Prepare the insert query
        $query = "INSERT INTO payments (
            student_id, payment_date, payment_amount, payment_method, 
            payment_status, academic_year, semester, payment_for, 
            reference_number, description, created_by, pay_type
        ) VALUES (
            :student_id, :payment_date, :payment_amount, :payment_method,
            :payment_status, :academic_year, :semester, :payment_for,
            :reference_number, :description, :created_by, :pay_type
        )";
        $stmt = $pdo->prepare($query);

        if ($pay_type === 'Monthly' && isset($_POST['payments'])) {
            // Handle multiple monthly payments
            foreach ($_POST['payments'] as $payment) {
                $payment_date = htmlspecialchars($payment['date'] ?? '', ENT_QUOTES, 'UTF-8');
                $payment_amount = filter_var($payment['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                if (empty($payment_date) || empty($payment_amount)) {
                    throw new Exception("សូមបំពេញកាលបរិច្ឆេទ និងចំនួនទឹកប្រាក់សម្រាប់ការបង់ប្រាក់នីមួយៗ");
                }

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
                    ':created_by' => $created_by,
                    ':pay_type' => $pay_type
                ]);
            }
        } else {
            // Handle single payment
            $payment_date = htmlspecialchars($_POST['payment_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $payment_amount = filter_input(INPUT_POST, 'payment_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            if (empty($payment_date) || empty($payment_amount)) {
                throw new Exception("សូមបំពេញកាលបរិច្ឆេទ និងចំនួនទឹកប្រាក់");
            }

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
                ':created_by' => $created_by,
                ':pay_type' => $pay_type
            ]);
        }

        // Get student name for the Telegram message
        $studentStmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
        $studentStmt->execute([$student_id]);
        $studentName = $studentStmt->fetchColumn();

        // Format and send Telegram message
        if ($pay_type === 'Monthly' && isset($_POST['payments'])) {
            $telegramMessage = '';
            foreach ($_POST['payments'] as $payment) {
                $payment_date = htmlspecialchars($payment['date'] ?? '', ENT_QUOTES, 'UTF-8');
                $payment_amount = filter_var($payment['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $telegramMessage .= formatPaymentMessage(
                    $studentName,
                    $payment_amount,
                    $payment_date,
                    $payment_method,
                    $payment_status,
                    $pay_type
                ) . "\n";
            }
        } else {
            $payment_date = htmlspecialchars($_POST['payment_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $payment_amount = filter_input(INPUT_POST, 'payment_amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $telegramMessage = formatPaymentMessage(
                $studentName,
                $payment_amount,
                $payment_date,
                $payment_method,
                $payment_status,
                $pay_type
            );
        }
        sendTelegramMessage($telegramMessage);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success_message'] = "ការបង់ប្រាក់ត្រូវបានបន្ថែមដោយជោគជ័យ";
        header("Location: payment-list.php");
        exit();

    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = "មានបញ្ហាក្នុងការបន្ថែមការបង់ប្រាក់៖ " . $e->getMessage();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Fetch students for dropdown
try {
    $stmt = $pdo->query("SELECT id, name as student_name, student_id as student_code FROM students ORDER BY name");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "មានបញ្ហាក្នុងការទាញយកបញ្ជីសិស្ស៖ " . $e->getMessage();
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បន្ថែមការបង់ប្រាក់</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
                        <h2>បន្ថែមការបង់ប្រាក់</h2>
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
                                        <select class="form-select select2" id="student_id" name="student_id" required>
                                            <option value="">ជ្រើសរើសសិស្ស</option>
                                            <?php foreach ($students as $student): ?>
                                                <option value="<?php echo $student['id']; ?>">
                                                    <?php echo htmlspecialchars($student['student_name'] . ' (' . $student['student_code'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_date" class="form-label">កាលបរិច្ឆេទបង់ប្រាក់ *</label>
                                        <input type="date" class="form-control" name="payment_date" id="payment_date" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_amount" class="form-label">ចំនួនទឹកប្រាក់ *</label>
                                        <input type="number" step="0.01" class="form-control" name="payment_amount" id="payment_amount" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">វិធីសាស្រ្តបង់ប្រាក់ *</label>
                                        <select class="form-select" name="payment_method" id="payment_method" required>
                                            <option value="">ជ្រើសរើសវិធីសាស្រ្តបង់ប្រាក់</option>
                                            <option value="Cash">សាច់ប្រាក់</option>
                                            <option value="ABA">ABA</option>
                                            <option value="ACLEDA">ACLEDA</option>
                                            <option value="Wing">Wing</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="pay_type" class="form-label">ប្រភេទនៃការបង់ប្រាក់ *</label>
                                        <select class="form-select" name="pay_type" id="pay_type" required>
                                            <option value="">ជ្រើសរើសប្រភេទនៃការបង់ប្រាក់</option>
                                            <option value="Full">បង់ពេញ</option>
                                            <option value="Monthly">ជាខែ</option>
                                            <option value="Half">ពាក់កណ្តាល</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_status" class="form-label">ស្ថានភាពបង់ប្រាក់ *</label>
                                        <select class="form-select" name="payment_status" id="payment_status" required>
                                            <option value="">ជ្រើសរើសស្ថានភាព</option>
                                            <option value="Completed">បានបញ្ចប់</option>
                                            <option value="Pending">កំពុងរង់ចាំ</option>
                                            <option value="Failed">បរាជ័យ</option>
                                            <option value="Refunded">បានបង្វិល</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="academic_year" class="form-label">ឆ្នាំសិក្សា *</label>
                                        <input type="text" class="form-control" name="academic_year" id="academic_year" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="semester" class="form-label">ឆមាស *</label>
                                        <select class="form-select" name="semester" id="semester" required>
                                            <option value="">ជ្រើសរើសឆមាស</option>
                                            <option value="1">ឆមាសទី១</option>
                                            <option value="2">ឆមាសទី២</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_for" class="form-label">បង់ប្រាក់សម្រាប់ *</label>
                                        <input type="text" class="form-control" name="payment_for" id="payment_for" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reference_number" class="form-label">លេខយោង</label>
                                        <input type="text" class="form-control" name="reference_number" id="reference_number">
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">ការពិពណ៌នា</label>
                                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
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
 <!-- Monthly Payment Modal -->
 <div class="modal" id="monthlyPaymentModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">កំណត់ការបង់ប្រាក់ប្រចាំខែ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="numberOfMonths" class="form-label">ចំនួនខែ *</label>
                                        <input type="number" class="form-control" id="numberOfMonths" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="startDate" class="form-label">កាលបរិច្ឆេទចាប់ផ្តើម *</label>
                                        <input type="date" class="form-control" id="startDate" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="monthlyAmount" class="form-label">ចំនួនទឹកប្រាក់ក្នុងមួយខែ *</label>
                                        <input type="number" step="0.01" class="form-control" id="monthlyAmount" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary" id="generateMonthlyPayments">
                                    <i class="fas fa-sync"></i> បង្កើតការបង់ប្រាក់
                                </button>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-bordered" id="monthlyPaymentsTable">
                                    <thead>
                                        <tr>
                                            <th>ល.រ</th>
                                            <th>កាលបរិច្ឆេទបង់ប្រាក់</th>
                                            <th>ចំនួនទឹកប្រាក់</th>
                                            <th>សកម្មភាព</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បិទ</button>
                            <button type="button" class="btn btn-primary" id="saveMonthlyPayments">
                                <i class="fas fa-save"></i> រក្សាទុក
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden container for payment data -->
            <div id="paymentRowsContainer" class="d-none"></div>
            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'ស្វែងរកសិស្ស...',
            language: {
                noResults: function() {
                    return "រកមិនឃើញសិស្ស";
                },
                searching: function() {
                    return "កំពុងស្វែងរក...";
                }
            }
        });

        // Monthly payment handling
        const payTypeSelect = document.getElementById('pay_type');
        if (payTypeSelect) {
            payTypeSelect.addEventListener('change', function() {
                if (this.value === 'Monthly') {
                    // Show the monthly payment modal
                    const modal = new bootstrap.Modal(document.getElementById('monthlyPaymentModal'), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                } else {
                    // Clear any existing payment rows
                    const container = document.getElementById('paymentRowsContainer');
                    if (container) {
                        container.innerHTML = '';
                    }
                }
            });
        }

        // Function to add payment row to the main form
        function addPaymentToMainForm(date, amount) {
            const paymentRow = `
                <tr>
                    <td>${$('#paymentTable tbody tr').length + 1}</td>
                    <td>
                        <input type="date" name="payment_date[]" class="form-control" value="${date}" required>
                    </td>
                    <td>
                        <input type="number" name="payment_amount[]" class="form-control" value="${amount}" step="0.01" required>
                    </td>
                    <td>
                        <select name="payment_method[]" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="ABA">ABA</option>
                            <option value="ACLEDA">ACLEDA</option>
                            <option value="Wing">Wing</option>
                        </select>
                    </td>
                    <td>
                        <select name="payment_status[]" class="form-select" required>
                            <option value="Paid">បានបង់</option>
                            <option value="Pending">មិនទាន់បង់</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#paymentTable tbody').append(paymentRow);
            updateRowNumbers();
        }

        // Generate monthly payments
        $('#generateMonthlyPayments').click(function() {
            const numberOfMonths = parseInt($('#numberOfMonths').val());
            const startDate = new Date($('#startDate').val());
            const monthlyAmount = parseFloat($('#monthlyAmount').val());

            if (!numberOfMonths || !startDate || !monthlyAmount) {
                alert('សូមបញ្ចូលព័ត៌មានឱ្យបានគ្រប់');
                return;
            }

            const tbody = $('#monthlyPaymentsTable tbody');
            tbody.empty();

            for (let i = 0; i < numberOfMonths; i++) {
                const paymentDate = new Date(startDate);
                paymentDate.setMonth(paymentDate.getMonth() + i);
                
                const row = `
                    <tr>
                        <td>${i + 1}</td>
                        <td>
                            <input type="date" class="form-control payment-date" 
                                   value="${paymentDate.toISOString().split('T')[0]}" required>
                        </td>
                        <td>
                            <input type="number" class="form-control payment-amount" 
                                   value="${monthlyAmount.toFixed(2)}" step="0.01" required>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            }
        });

        // Remove payment row
        $(document).on('click', '.remove-row, .delete-row', function() {
            $(this).closest('tr').remove();
            updateRowNumbers();
        });

        // Update row numbers after removal
        function updateRowNumbers() {
            $('#paymentTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
            $('#monthlyPaymentsTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Save monthly payments
        $('#saveMonthlyPayments').click(function() {
            const payments = [];
            let isValid = true;

            $('#monthlyPaymentsTable tbody tr').each(function() {
                const date = $(this).find('.payment-date').val();
                const amount = $(this).find('.payment-amount').val();

                if (!date || !amount) {
                    isValid = false;
                    return false;
                }

                payments.push({
                    date: date,
                    amount: parseFloat(amount)
                });
            });

            if (!isValid) {
                alert('សូមបញ្ចូលព័ត៌មានឱ្យបានគ្រប់សម្រាប់ការបង់ប្រាក់នីមួយៗ');
                return;
            }

            // Add each payment to the main form
            payments.forEach(payment => {
                addPaymentToMainForm(payment.date, payment.amount);
            });

            // Close the modal
            $('#monthlyPaymentModal').modal('hide');
            
            // Update the total amount
            calculateTotal();
        });

        // Calculate total amount
        function calculateTotal() {
            let total = 0;
            $('input[name="payment_amount[]"]').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#totalAmount').text(total.toFixed(2));
        }

        // Recalculate total when amounts change
        $(document).on('change', 'input[name="payment_amount[]"]', calculateTotal);
    });
    </script>
</body>
</html>
