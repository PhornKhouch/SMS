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
    // Base query
    $query = "SELECT c.*, t.name as teacher_name 
              FROM classes c 
              LEFT JOIN teachers t ON c.teacher_id = t.id";
    $params = [];

    // Add search if provided
    if (!empty($search)) {
        $query .= " WHERE c.class_name LIKE :search 
                   OR c.class_id LIKE :search 
                   OR c.grade LIKE :search 
                   OR t.name LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Count total records
    $count_query = "SELECT COUNT(*) FROM (" . $query . ") as count_table";
    $stmt = $pdo->prepare($count_query);
    foreach ($params as $key => &$value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total_count = $stmt->fetchColumn();

    // Add ordering and pagination
    $query .= " ORDER BY c.id DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $records_per_page;
    $params[':offset'] = $offset;

    // Get records
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => &$value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    $classes = [];
    $total_count = 0;
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បញ្ជីថ្នាក់រៀន</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
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
            <!-- Main Content -->
            <div class="container-fluid">
                <div class="content-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2>បញ្ជីថ្នាក់រៀន</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item active">បញ្ជីថ្នាក់រៀន</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="add-class.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> បន្ថែមថ្នាក់រៀន
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
                                        <input type="text" class="form-control" placeholder="ស្វែងរកថ្នាក់រៀន..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i> ស្វែងរក
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classes List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>លេខរៀង</th>
                                        <th>អត្តលេខថ្នាក់</th>
                                        <th>ឈ្មោះថ្នាក់</th>
                                        <th>កម្រិត</th>
                                        <th>ផ្នែក</th>
                                        <th>គ្រូបង្រៀន</th>
                                        <th>ចំនួនសិស្ស</th>
                                        <th>បន្ទប់</th>
                                        <th>ស្ថានភាព</th>
                                        <th>សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($classes)): ?>
                                    <tr>
                                        <td colspan="10" class="text-center">មិនមានទិន្នន័យថ្នាក់រៀនទេ</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($classes as $index => $class): ?>
                                        <tr>
                                            <td><?php echo $offset + $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($class['class_id']); ?></td>
                                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($class['grade']); ?></td>
                                            <td><?php echo htmlspecialchars($class['section']); ?></td>
                                            <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($class['capacity']); ?></td>
                                            <td><?php echo htmlspecialchars($class['room_number']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $class['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $class['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <a href="view-class.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-info" title="មើល">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-class.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-primary" title="កែប្រែ">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $class['id']; ?>)" title="លុប">
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
                title: 'តើអ្នកពិតជាចង់លុបថ្នាក់រៀននេះមែនទេ?',
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
                    window.location.href = 'delete-class.php?id=' + id;
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
