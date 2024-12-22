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

// Get search term
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Test database connection
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }

    // Simple query to test if table exists and has data
    $test_query = "SELECT COUNT(*) FROM teachers";
    $test_result = $pdo->query($test_query);
    if (!$test_result) {
        throw new Exception("Error executing query: " . print_r($pdo->errorInfo(), true));
    }
    $total_count = $test_result->fetchColumn();
    
    // Store the count for debugging
    $_SESSION['debug_info']['total_count'] = $total_count;

    // Main query without pagination first
    $all_teachers = $pdo->query("SELECT * FROM teachers")->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['debug_info']['all_teachers_count'] = count($all_teachers);

    // Now do the paginated query
    $query = "SELECT * FROM teachers";
    $params = [];

    // Add search if provided
    if (!empty($search)) {
        $query .= " WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search";
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
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Store query information for debugging
    $_SESSION['debug_info']['query'] = $query;
    $_SESSION['debug_info']['params'] = $params;
    $_SESSION['debug_info']['teacher_count'] = count($teachers);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    $teachers = [];
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បញ្ជីគ្រូបង្រៀន</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .profile-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                            <h2>បញ្ជីគ្រូបង្រៀន</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item active">បញ្ជីគ្រូបង្រៀន</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="add-teacher.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> បន្ថែមគ្រូបង្រៀន
                        </a>
                    </div>
                </div>

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
                                        <input type="text" class="form-control" placeholder="ស្វែងរកគ្រូបង្រៀន..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i> ស្វែងរក
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>លេខរៀង</th>
                                        <th>រូបថត</th>
                                        <th>ឈ្មោះ</th>
                                        <th>ភេទ</th>
                                        <th>មុខវិជ្ជា</th>
                                        <th>អ៊ីមែល</th>
                                        <th>លេខទូរស័ព្ទ</th>
                                        <th>ស្ថានភាព</th>
                                        <th>សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($teachers)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <?php 
                                            // Display any error message
                                            if (isset($_SESSION['error_message'])) {
                                                echo '<div class="alert alert-danger">';
                                                echo $_SESSION['error_message'];
                                                echo '</div>';
                                                unset($_SESSION['error_message']);
                                            }

                                            // Display debug information
                                            if (isset($_SESSION['debug_info'])) {
                                                echo '<div class="alert alert-info">';
                                                echo '<pre>';
                                                print_r($_SESSION['debug_info']);
                                                echo '</pre>';
                                                echo '</div>';
                                                unset($_SESSION['debug_info']);
                                            }

                                            echo "មិនមានទិន្នន័យគ្រូបង្រៀនទេ";
                                            ?>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($teachers as $index => $teacher): ?>
                                        <tr>
                                            <td><?php echo $offset + $index + 1; ?></td>
                                            <td>
                                                <img src="<?php echo $basePath; ?>uploads/teachers/<?php echo !empty($teacher['photo']) ? htmlspecialchars($teacher['photo']) : 'default.png'; ?>" 
                                                     class="profile-image" 
                                                     alt="Teacher Photo">
                                            </td>
                                            <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['gender']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['subject']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                            <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $teacher['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $teacher['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <a href="view-teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-info" title="មើល">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-sm btn-primary" title="កែប្រែ">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $teacher['id']; ?>)" title="លុប">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&records_per_page=<?php echo $records_per_page; ?>">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&records_per_page=<?php echo $records_per_page; ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min(ceil($total_count / $records_per_page), $start_page + 4);
                                $start_page = max(1, $end_page - 4);

                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&records_per_page=<?php echo $records_per_page; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <?php if ($page < ceil($total_count / $records_per_page)): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&records_per_page=<?php echo $records_per_page; ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ceil($total_count / $records_per_page); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&records_per_page=<?php echo $records_per_page; ?>">
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

            <!-- Footer -->
            <?php include $basePath . 'includes/footer.php'; ?>
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
                title: 'តើអ្នកពិតជាចង់លុបគ្រូបង្រៀននេះមែនទេ?',
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
                    window.location.href = 'delete-teacher.php?id=' + id;
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
            window.location.href = '?records_per_page=' + this.value + '<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>';
        });
    </script>
</body>
</html>
