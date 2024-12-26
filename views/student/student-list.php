<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// Fetch students with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE name LIKE :search OR student_id LIKE :search OR class LIKE :search";
}

// Count total records for pagination
$count_query = "SELECT COUNT(*) FROM students $where";
$stmt = $pdo->prepare($count_query);
if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->execute();
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// Fetch students
$query = "SELECT * FROM students $where ORDER BY name LIMIT :offset, :records_per_page";
$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bindParam(':search', $search_param);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បញ្ជីសិស្ស - ក្លឹបកូដ</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
                            <h2>បញ្ជីសិស្ស</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../../index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item">សិស្ស</li>
                                    <li class="breadcrumb-item active">បញ្ជីសិស្ស</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="export-students.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" class="btn btn-success me-2" id="exportBtn">
                                <i class="fas fa-file-excel"></i> នាំចេញ Excel
                            </a>
                            <a href="add-student.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> បន្ថែមសិស្សថ្មី
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <span class="me-2">បង្ហាញ</span>
                                <select class="form-select form-select-sm w-auto" id="recordsPerPage">
                                    <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>១០</option>
                                    <option value="25" <?php echo $records_per_page == 25 ? 'selected' : ''; ?>>២៥</option>
                                    <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>៥០</option>
                                </select>
                                <span class="ms-2">ជួរ</span>
                            </div>
                            <div class="search-box">
                                <form action="" method="GET" class="d-flex">
                                    <input type="text" name="search" class="form-control" placeholder="ស្វែងរកសិស្ស..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary ms-2">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>អត្តលេខសិស្ស</th>
                                        <th>រូបថត</th>
                                        <th>ឈ្មោះ</th>
                                        <th>ថ្នាក់</th>
                                        <th>ភេទ</th>
                                        <th>ថ្ងៃខែឆ្នាំកំណើត</th>
                                        <th>លេខទូរស័ព្ទ</th>
                                        <th>ស្ថានភាព</th>
                                        <th>សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr class="student-row" data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>">
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td>
                                            <img src="<?php echo !empty($student['photo']) ? $basePath . 'uploads/students/' . $student['photo'] : $basePath . 'assets/images/default-avatar.png'; ?>" 
                                                 class="rounded-circle" 
                                                 width="40" 
                                                 height="40" 
                                                 alt="រូបថតសិស្ស">
                                        </td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class']); ?></td>
                                        <td><?php echo $student['gender'] == 'Male' ? 'ប្រុស' : ($student['gender'] == 'Female' ? 'ស្រី' : 'ផ្សេងៗ'); ?></td>
                                        <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $student['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo $student['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view-student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info" title="មើល">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-student.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary" title="កែប្រែ">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $student['id']; ?>)" title="លុប">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                កំពុងបង្ហាញ <?php echo $offset + 1; ?> ដល់ <?php echo min($offset + $records_per_page, $total_records); ?> នៃ <?php echo $total_records; ?> ជួរ
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0">
                                    <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="studentDetailsModalLabel">ព័ត៌មានសិស្ស</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="" id="studentPhoto" class="img-fluid rounded-circle" alt="រូបថតសិស្ស">
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th>អត្តលេខសិស្ស</th>
                                        <td id="studentId"></td>
                                    </tr>
                                    <tr>
                                        <th>ឈ្មោះ</th>
                                        <td id="studentName"></td>
                                    </tr>
                                    <tr>
                                        <th>ថ្នាក់</th>
                                        <td id="studentClass"></td>
                                    </tr>
                                    <tr>
                                        <th>ភេទ</th>
                                        <td id="studentGender"></td>
                                    </tr>
                                    <tr>
                                        <th>ថ្ងៃខែឆ្នាំកំណើត</th>
                                        <td id="studentDob"></td>
                                    </tr>
                                    <tr>
                                        <th>លេខទូរស័ព្ទ</th>
                                        <td id="studentPhone"></td>
                                    </tr>
                                    <tr>
                                        <th>អាសយដ្ឋាន</th>
                                        <td id="studentAddress"></td>
                                    </tr>
                                    <tr>
                                        <th>ស្ថានភាព</th>
                                        <td id="studentStatus"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បិទ</button>
                </div>
            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Make table rows clickable
        $(document).ready(function() {
            $('.student-row').dblclick(function() {
                const studentId = $(this).data('student-id');
                fetchStudentDetails(studentId);
            });

            function fetchStudentDetails(studentId) {
                $.ajax({
                    url: 'get-student-details.php',
                    data: { student_id: studentId },
                    method: 'GET',
                    success: function(student) {
                        $('#studentPhoto').attr('src', student.photo ? 
                            '<?php echo $basePath; ?>uploads/students/' + student.photo : 
                            '<?php echo $basePath; ?>assets/images/default-avatar.png'
                        );
                        $('#studentId').text(student.student_id);
                        $('#studentName').text(student.name);
                        $('#studentClass').text(student.class);
                        $('#studentGender').text(student.gender === 'Male' ? 'ប្រុស' : 
                            (student.gender === 'Female' ? 'ស្រី' : 'ផ្សេងៗ'));
                        $('#studentDob').text(student.date_of_birth);
                        $('#studentPhone').text(student.phone);
                        $('#studentAddress').text(student.address);
                        $('#studentStatus').text(student.status === 'Active' ? 'សកម្ម' : 'អសកម្ម');
                        
                        $('#studentDetailsModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        toastr.error('មានបញ្ហាក្នុងការទាញយកព័ត៌មាន');
                        console.error('Error:', error);
                    }
                });
            }
        });

        // Delete confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: 'តើអ្នកពិតជាចង់លុបសិស្សនេះមែនទេ?',
                text: "អ្នកនឹងមិនអាចត្រឡប់វាមកវិញបានទេ!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'យល់ព្រម លុប!',
                cancelButtonText: 'បោះបង់',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete-student.php?id=' + id;
                }
            });
        }

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

        // Records per page change
        document.getElementById('recordsPerPage').addEventListener('change', function() {
            window.location.href = '?records_per_page=' + this.value;
        });

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
            if (search) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'search';
                input.value = search;
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();

            // Hide modal after download starts
            setTimeout(() => {
                loadingModal.hide();
            }, 3000);
        });
    </script>
</body>
</html>
