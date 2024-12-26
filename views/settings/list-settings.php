<?php
// Start session and include required files
require_once "../../includes/config.php";
require_once "../../includes/auth.php";

// Ensure user is logged in and is admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

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

// Get search term
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Count total records
    $count_query = "SELECT COUNT(*) FROM users";
    if (!empty($search)) {
        $count_query .= " WHERE username LIKE :search OR email LIKE :search OR full_name LIKE :search";
    }
    $count_stmt = $pdo->prepare($count_query);
    if (!empty($search)) {
        $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_count = $count_stmt->fetchColumn();

    // Main query
    $query = "SELECT * FROM users";
    $params = [];

    // Add search if provided
    if (!empty($search)) {
        $query .= " WHERE username LIKE :search OR email LIKE :search OR full_name LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Add ordering and pagination
    $query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $records_per_page;
    $params[':offset'] = $offset;

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => &$value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ការកំណត់</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
                <div class="content-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>ការកំណត់</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item active">ការកំណត់</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">
                            <i class="fas fa-users"></i> អ្នកប្រើប្រាស់
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="school-tab" data-bs-toggle="tab" data-bs-target="#school" type="button" role="tab" aria-controls="school" aria-selected="false">
                            <i class="fas fa-school"></i> ព័ត៌មានសាលា
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab" aria-controls="academic" aria-selected="false">
                            <i class="fas fa-graduation-cap"></i> ឆ្នាំសិក្សា
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab" aria-controls="system" aria-selected="false">
                            <i class="fas fa-cogs"></i> ប្រព័ន្ធ
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="settingsTabContent">
                    <!-- Users Tab -->
                    <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <!-- Search and Filter Section -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <label for="recordsPerPage">បង្ហាញ:</label>
                                        <select id="recordsPerPage" class="form-select">
                                            <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                                            <option value="25" <?php echo $records_per_page == 25 ? 'selected' : ''; ?>>25</option>
                                            <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                                            <option value="100" <?php echo $records_per_page == 100 ? 'selected' : ''; ?>>100</option>
                                        </select>
                                    </div>
                                    <div class="col-md-9">
                                        <form action="" method="GET" class="float-end">
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="ស្វែងរកអ្នកប្រើប្រាស់..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-search"></i> ស្វែងរក
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users List -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">បញ្ជីអ្នកប្រើប្រាស់</h5>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-plus"></i> បន្ថែមអ្នកប្រើប្រាស់
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>លេខរៀង</th>
                                                <th>ឈ្មោះអ្នកប្រើប្រាស់</th>
                                                <th>ឈ្មោះពេញ</th>
                                                <th>អ៊ីមែល</th>
                                                <th>តួនាទី</th>
                                                <th>ស្ថានភាព</th>
                                                <th>ចូលប្រើចុងក្រោយ</th>
                                                <th>សកម្មភាព</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($users)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">មិនមានទិន្នន័យអ្នកប្រើប្រាស់ទេ</td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($users as $index => $user): ?>
                                                <tr>
                                                    <td><?php echo $offset + $index + 1; ?></td>
                                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td>
                                                        <span class="badge <?php 
                                                            echo $user['role'] == 'admin' ? 'bg-danger' : 
                                                                ($user['role'] == 'teacher' ? 'bg-primary' : 'bg-success'); 
                                                        ?>">
                                                            <?php 
                                                            echo $user['role'] == 'admin' ? 'អ្នកគ្រប់គ្រង' : 
                                                                ($user['role'] == 'teacher' ? 'គ្រូបង្រៀន' : 'សិស្ស'); 
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?php echo $user['status'] == 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?php echo $user['status'] == 'active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $user['last_login'] ? date('d-m-Y H:i', strtotime($user['last_login'])) : 'មិនធ្លាប់'; ?></td>
                                                    <td class="action-buttons">
                                                        <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" title="កែប្រែ">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($user['username'] !== 'admin'): ?>
                                                        <a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $user['id']; ?>)" title="លុប">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                        <?php endif; ?>
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
                                    <ul class="pagination justify-content-end">
                                        <?php
                                        $total_pages = ceil($total_count / $records_per_page);
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $start_page + 4);
                                        ?>

                                        <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1&records_per_page=<?php echo $records_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&records_per_page=<?php echo $records_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="fas fa-angle-left"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&records_per_page=<?php echo $records_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&records_per_page=<?php echo $records_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="fas fa-angle-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $total_pages; ?>&records_per_page=<?php echo $records_per_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- School Info Tab -->
                    <div class="tab-pane fade" id="school" role="tabpanel" aria-labelledby="school-tab">
                        <div class="card">
                            <div class="card-body">
                                <form id="schoolSettingsForm" method="POST" action="update-school-settings.php">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="schoolName" class="form-label">ឈ្មោះសាលា</label>
                                                <input type="text" class="form-control" id="schoolName" name="schoolName" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="schoolAddress" class="form-label">អាសយដ្ឋាន</label>
                                                <textarea class="form-control" id="schoolAddress" name="schoolAddress" rows="3"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="phoneNumber" class="form-label">លេខទូរស័ព្ទ</label>
                                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">អ៊ីមែល</label>
                                                <input type="email" class="form-control" id="email" name="email">
                                            </div>
                                            <div class="mb-3">
                                                <label for="website" class="form-label">គេហទំព័រ</label>
                                                <input type="url" class="form-control" id="website" name="website">
                                            </div>
                                            <div class="mb-3">
                                                <label for="principalName" class="form-label">ឈ្មោះនាយក</label>
                                                <input type="text" class="form-control" id="principalName" name="principalName">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Year Tab -->
                    <div class="tab-pane fade" id="academic" role="tabpanel" aria-labelledby="academic-tab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">ឆ្នាំសិក្សា</h5>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAcademicYearModal">
                                    <i class="fas fa-plus"></i> បន្ថែមឆ្នាំសិក្សា
                                </button>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ឆ្នាំសិក្សា</th>
                                            <th>កាលបរិច្ឆេទចាប់ផ្តើម</th>
                                            <th>កាលបរិច្ឆេទបញ្ចប់</th>
                                            <th>ស្ថានភាព</th>
                                            <th>សកម្មភាព</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Academic years will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings Tab -->
                    <div class="tab-pane fade" id="system" role="tabpanel" aria-labelledby="system-tab">
                        <div class="card">
                            <div class="card-body">
                                <form id="systemSettingsForm" method="POST" action="update-system-settings.php">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="timezone" class="form-label">ល្វែងម៉ោង</label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <option value="Asia/Phnom_Penh">Asia/Phnom Penh</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="dateFormat" class="form-label">ទ្រង់ទ្រាយកាលបរិច្ឆេទ</label>
                                                <select class="form-select" id="dateFormat" name="dateFormat">
                                                    <option value="d-m-Y">DD-MM-YYYY</option>
                                                    <option value="Y-m-d">YYYY-MM-DD</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="language" class="form-label">ភាសា</label>
                                                <select class="form-select" id="language" name="language">
                                                    <option value="km">ខ្មែរ</option>
                                                    <option value="en">English</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="maintenanceMode" name="maintenanceMode">
                                                    <label class="form-check-label" for="maintenanceMode">បើកដំណើរការថែទាំ</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">បន្ថែមអ្នកប្រើប្រាស់ថ្មី</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addUserForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">ឈ្មោះអ្នកប្រើប្រាស់</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">ឈ្មោះពេញ</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">អ៊ីមែល</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">ពាក្យសម្ងាត់</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">តួនាទី</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">ជ្រើសរើសតួនាទី</option>
                                <option value="admin">អ្នកគ្រប់គ្រង</option>
                                <option value="teacher">គ្រូបង្រៀន</option>
                                <option value="student">សិស្ស</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">ស្ថានភាព</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active">សកម្ម</option>
                                <option value="inactive">អសកម្ម</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                        <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Delete confirmation
        function confirmDelete(id) {
            Swal.fire({
                title: 'តើអ្នកពិតជាចង់លុបអ្នកប្រើប្រាស់នេះមែនទេ?',
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
                    window.location.href = 'delete-user.php?id=' + id;
                }
            });
        }

        // Add User Form Submission
        $(document).ready(function() {
            $('#addUserForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'add-user-ajax.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            toastr.success('អ្នកប្រើប្រាស់ត្រូវបានបន្ថែមដោយជោគជ័យ');
                            
                            // Close modal
                            $('#addUserModal').modal('hide');
                            
                            // Reset form
                            $('#addUserForm')[0].reset();
                            
                            // Reload page to show new user
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            // Show error message
                            toastr.error(response.message || 'មានបញ្ហាក្នុងការបន្ថែមអ្នកប្រើប្រាស់');
                        }
                    },
                    error: function() {
                        toastr.error('មានបញ្ហាក្នុងការភ្ជាប់ទៅម៉ាស៊ីនមេ');
                    }
                });
            });
        });

        // Records per page change
        document.getElementById('recordsPerPage').addEventListener('change', function() {
            window.location.href = '?records_per_page=' + this.value + '<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>';
        });

        // Show success/error messages
        <?php if (isset($_SESSION['success_message'])): ?>
        toastr.success('<?php echo $_SESSION['success_message']; ?>');
        <?php unset($_SESSION['success_message']); endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        toastr.error('<?php echo $_SESSION['error_message']; ?>');
        <?php unset($_SESSION['error_message']); endif; ?>
    </script>
</body>
</html>
