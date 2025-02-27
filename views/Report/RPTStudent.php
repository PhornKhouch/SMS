<?php
session_start();
require_once "../../includes/config.php";

// Define base path for links
$basePath = "../../";

// Get filters
$class_filter = isset($_GET['class']) ? $_GET['class'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build where clause
$where_conditions = [];
$params = [];

if (!empty($class_filter)) {
    $where_conditions[] = "s.class = :class";
    $params[':class'] = $class_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "s.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($gender_filter)) {
    $where_conditions[] = "s.gender = :gender";
    $params[':gender'] = $gender_filter;
}

if (!empty($date_from) && !empty($date_to)) {
    $where_conditions[] = "s.date_of_birth BETWEEN :date_from AND :date_to";
    $params[':date_from'] = $date_from;
    $params[':date_to'] = $date_to;
}

$where = '';
if (!empty($where_conditions)) {
    $where = "WHERE " . implode(" AND ", $where_conditions);
}

// Fetch all subjects for the filter
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $pdo->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch students with filters
$query = "SELECT s.*, sub.subject_name as class_name 
          FROM students s 
          LEFT JOIN subjects sub ON s.class = sub.id 
          $where 
          ORDER BY s.student_id";
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_students = count($students);
$total_active = 0;
$total_inactive = 0;
$total_male = 0;
$total_female = 0;

foreach ($students as $student) {
    if ($student['status'] == 'Active') $total_active++;
    if ($student['status'] == 'Inactive') $total_inactive++;
    if ($student['gender'] == 'Male') $total_male++;
    if ($student['gender'] == 'Female') $total_female++;
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>របាយការណ៍សិស្ស - ក្លឹបកូដ</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
        }
        .stats-card {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            color: white;
        }
        .stats-card h3 {
            font-size: 2rem;
            margin: 0;
        }
        .stats-card p {
            margin: 0;
            opacity: 0.8;
        }
        .bg-primary-gradient {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }
        .bg-success-gradient {
            background: linear-gradient(45deg, #28a745, #1e7e34);
        }
        .bg-danger-gradient {
            background: linear-gradient(45deg, #dc3545, #bd2130);
        }
        .bg-info-gradient {
            background: linear-gradient(45deg, #17a2b8, #117a8b);
        }
        .table th {
            background-color: #198754;
            color: white;
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
                            <h2>របាយការណ៍សិស្ស</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="../../index.php">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item">របាយការណ៍</li>
                                    <li class="breadcrumb-item active">របាយការណ៍សិស្ស</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="class" class="form-label">ថ្នាក់</label>
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
                                <label for="status" class="form-label">ស្ថានភាព</label>
                                <select name="status" class="form-select">
                                    <option value="">ទាំងអស់</option>
                                    <option value="Active" <?php echo $status_filter === 'Active' ? 'selected' : ''; ?>>សកម្ម</option>
                                    <option value="Inactive" <?php echo $status_filter === 'Inactive' ? 'selected' : ''; ?>>អសកម្ម</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="gender" class="form-label">ភេទ</label>
                                <select name="gender" class="form-select">
                                    <option value="">ទាំងអស់</option>
                                    <option value="Male" <?php echo $gender_filter === 'Male' ? 'selected' : ''; ?>>ប្រុស</option>
                                    <option value="Female" <?php echo $gender_filter === 'Female' ? 'selected' : ''; ?>>ស្រី</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">ថ្ងៃខែឆ្នាំកំណើតចាប់ពី</label>
                                <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">ដល់</label>
                                <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card bg-primary-gradient">
                            <h3><?php echo $total_students; ?></h3>
                            <p>សរុបសិស្ស</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-success-gradient">
                            <h3><?php echo $total_active; ?></h3>
                            <p>សិស្សសកម្ម</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-danger-gradient">
                            <h3><?php echo $total_inactive; ?></h3>
                            <p>សិស្សអសកម្ម</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-info-gradient">
                            <h3><?php echo $total_male; ?>/<?php echo $total_female; ?></h3>
                            <p>សិស្សប្រុស/ស្រី</p>
                        </div>
                    </div>
                </div>

                <!-- Student Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="studentTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>អត្តលេខសិស្ស</th>
                                        <th>ឈ្មោះ</th>
                                        <th>ថ្នាក់</th>
                                        <th>ភេទ</th>
                                        <th>ថ្ងៃខែឆ្នាំកំណើត</th>
                                        <th>អាសយដ្ឋាន</th>
                                        <th>ស្ថានភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                                        <td><?php echo $student['gender'] == 'Male' ? 'ប្រុស' : ($student['gender'] == 'Female' ? 'ស្រី' : 'ផ្សេងៗ'); ?></td>
                                        <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                                        <td><?php echo htmlspecialchars($student['address']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $student['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                                <?php echo $student['status'] == 'Active' ? 'សកម្ម' : 'អសកម្ម'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#studentTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success me-2',
                        title: 'របាយការណ៍សិស្ស',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger me-2',
                        title: 'របាយការណ៍សិស្ស',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> បោះពុម្ព',
                        className: 'btn btn-primary',
                        title: 'របាយការណ៍សិស្ស',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                pageLength: 10,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/km.json'
                }
            });
        });
    </script>
</body>
</html>