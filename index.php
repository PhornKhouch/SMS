<?php
session_start();
include 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club-Code</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navigation -->
            <?php include 'includes/topnav.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid" style="font-family: 'Battambang', sans-serif;">
                <div class="content-header">
                    <h2>ផ្ទាំងគ្រប់គ្រង</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">ទំព័រដើម</a></li>
                            <li class="breadcrumb-item active">ផ្ទាំងគ្រប់គ្រង</li>
                        </ol>
                    </nav>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            សិស្សសរុប</div>
                                        <?php
                                            $stmt = $pdo->query("SELECT COUNT(*) as total FROM students");
                                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                            echo '<div class="h5 mb-0 font-weight-bold text-white">' . $result['total'] . '</div>';
                                        ?>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2" style="background: linear-gradient(45deg, #1cc88a, #13855c);">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            គ្រូសរុប</div>
                                        
                                        <?php
                                            $stmt = $pdo->query("SELECT COUNT(*) as total FROM teachers");
                                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                            echo '<div class="h5 mb-0 font-weight-bold text-white">' . $result['total'] . '</div>';
                                        ?>
                                        
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2" style="background: linear-gradient(45deg, #36b9cc, #258391);">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            ថ្នាក់សរុប</div>
                                        <div class="h5 mb-0 font-weight-bold text-white" id="totalClasses">០</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-school fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2" style="background: linear-gradient(45deg, #f6c23e, #dda20a);">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            សិស្សមិនទាន់បង់ប្រាក់ប្រចាំខែ
                                        </div>
                                        <?php
                                            $select="SELECT COUNT(*) as count FROM `payments` WHERE pay_type='Monthly' and payment_status='Pending'";
                                            $res=$conn->query($select);
                                            $count=$res->fetch_assoc()['count'];
                                        ?>
                                        <div class="h5 mb-0 font-weight-bold text-white" id="pendingAssignments"><?php echo  $count?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clipboard-list fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                                <h6 class="m-0 font-weight-bold text-white">សកម្មភាពថ្មីៗ</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>សកម្មភាព</th>
                                                <th>កាលបរិច្ឆេទ</th>
                                                <th>ស្ថានភាព</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentActivities">
                                            <!-- Activities will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Events -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3" style="background: linear-gradient(45deg, #1cc88a, #13855c);">
                                <h6 class="m-0 font-weight-bold text-white">ព្រឹត្តិការណ៍ខាងមុខ</h6>
                            </div>
                            <div class="card-body">
                                <div id="upcomingEvents">
                                    <!-- Events will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'includes/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
