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
                    <!-- Payment Line Chart -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                                <h6 class="m-0 font-weight-bold text-white">ស្ថិតិការបង់ប្រាក់ប្រចាំខែ</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentLineChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Status Pie Chart -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3" style="background: linear-gradient(45deg, #1cc88a, #13855c);">
                                <h6 class="m-0 font-weight-bold text-white">ស្ថានភាពការបង់ប្រាក់</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Type Bar Chart -->
                <div class="row mb-4">
                    <div class="col-xl-12">
                        <div class="card shadow">
                            <div class="card-header py-3" style="background: linear-gradient(45deg, #36b9cc, #258391);">
                                <h6 class="m-0 font-weight-bold text-white">ការបង់ប្រាក់តាមប្រភេទ</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                // Get payment statistics for charts
                // Last 6 months data for line chart
                $months = array();
                $monthly_paid = array();
                $monthly_pending = array();
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = date('Y-m', strtotime("-$i months"));
                    $months[] = date('M Y', strtotime("-$i months"));
                    
                    $paid_query = "SELECT COALESCE(SUM(payment_amount), 0) as total 
                                 FROM payments 
                                 WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? 
                                 AND payment_status = 'Paid'";
                    $stmt = $pdo->prepare($paid_query);
                    $stmt->execute([$month]);
                    $monthly_paid[] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $pending_query = "SELECT COALESCE(SUM(payment_amount), 0) as total 
                                    FROM payments 
                                    WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? 
                                    AND payment_status = 'Pending'";
                    $stmt = $pdo->prepare($pending_query);
                    $stmt->execute([$month]);
                    $monthly_pending[] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                }

                // Get total payments by status for pie chart
                $status_query = "SELECT payment_status, COUNT(*) as count, SUM(payment_amount) as total
                               FROM payments 
                               GROUP BY payment_status";
                $stmt = $pdo->prepare($status_query);
                $stmt->execute();
                $status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get payments by type for bar chart
                $type_query = "SELECT pay_type, payment_status, COUNT(*) as count, SUM(payment_amount) as total
                             FROM payments 
                             GROUP BY pay_type, payment_status";
                $stmt = $pdo->prepare($type_query);
                $stmt->execute();
                $type_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Get student registration statistics by month
                $reg_months = array();
                $reg_counts = array();
                
                for ($i = 5; $i >= 0; $i--) {
                    $month = date('Y-m', strtotime("-$i months"));
                    $reg_months[] = date('M Y', strtotime("-$i months"));
                    
                    $reg_query = "SELECT COUNT(*) as total 
                                FROM students 
                                WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";
                    $stmt = $pdo->prepare($reg_query);
                    $stmt->execute([$month]);
                    $reg_counts[] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                }
                ?>
                <!-- Student Registration Chart -->
                <div class="row mb-4">
                    <div class="col-xl-12">
                        <div class="card shadow">
                            <div class="card-header py-3" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                                <h6 class="m-0 font-weight-bold text-white">ស្ថិតិសិស្សចុះឈ្មោះប្រចាំខែ</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="registrationChart"></canvas>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Set default font for all charts
        Chart.defaults.font.family = 'Battambang';
        Chart.defaults.font.size = 14;

        // Line Chart
        new Chart(document.getElementById('paymentLineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'បានបង់',
                    data: <?php echo json_encode($monthly_paid); ?>,
                    borderColor: 'rgba(28, 200, 138, 1)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'មិនទាន់បង់',
                    data: <?php echo json_encode($monthly_pending); ?>,
                    borderColor: 'rgba(246, 194, 62, 1)',
                    backgroundColor: 'rgba(246, 194, 62, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'ស្ថិតិការបង់ប្រាក់ប្រចាំខែ',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'ចំនួនទឹកប្រាក់ (៛)',
                            font: {
                                family: 'Battambang'
                            }
                        },
                        ticks: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    }
                }
            }
        });

        // Pie Chart
        new Chart(document.getElementById('paymentPieChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?php 
                    $pie_labels = array_map(function($item) { 
                        return $item['payment_status'] == 'Completed' ? 'បានបង់' : 'មិនទាន់បង់'; 
                    }, $status_data);
                    echo json_encode($pie_labels); 
                ?>,
                datasets: [{
                    data: <?php 
                        $pie_data = array_map(function($item) { 
                            return $item['total']; 
                        }, $status_data);
                        echo json_encode($pie_data); 
                    ?>,
                    backgroundColor: [
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(246, 194, 62, 0.8)'
                    ],
                    borderColor: [
                        'rgba(28, 200, 138, 1)',
                        'rgba(246, 194, 62, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'ស្ថានភាពការបង់ប្រាក់សរុប',
                        font: {
                            size: 16,
                            weight: 'bold',
                            family: 'Battambang'
                        }
                    }
                }
            }
        });

        // Bar Chart
        new Chart(document.getElementById('paymentBarChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['ប្រចាំខែ', 'ពាក់កណ្តាលឆ្នាំ'],
                datasets: [{
                    label: 'បានបង់',
                    data: <?php 
                        $paid_by_type = array_reduce($type_data, function($carry, $item) {
                            if ($item['payment_status'] == 'Paid') {
                                $carry[$item['pay_type']] = $item['total'];
                            }
                            return $carry;
                        }, ['Monthly' => 0, 'Half' => 0]);
                        echo json_encode(array_values($paid_by_type));
                    ?>,
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: 'rgba(28, 200, 138, 1)',
                    borderWidth: 1
                }, {
                    label: 'មិនទាន់បង់',
                    data: <?php 
                        $pending_by_type = array_reduce($type_data, function($carry, $item) {
                            if ($item['payment_status'] == 'Pending') {
                                $carry[$item['pay_type']] = $item['total'];
                            }
                            return $carry;
                        }, ['Monthly' => 0, 'Half' => 0]);
                        echo json_encode(array_values($pending_by_type));
                    ?>,
                    backgroundColor: 'rgba(246, 194, 62, 0.8)',
                    borderColor: 'rgba(246, 194, 62, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'ការបង់ប្រាក់តាមប្រភេទ',
                        font: {
                            size: 16,
                            weight: 'bold',
                            family: 'Battambang'
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'ចំនួនទឹកប្រាក់ (៛)',
                            font: {
                                family: 'Battambang'
                            }
                        },
                        ticks: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    }
                }
            }
        });

        // Registration Chart
        new Chart(document.getElementById('registrationChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($reg_months); ?>,
                datasets: [{
                    label: 'សិស្សចុះឈ្មោះ',
                    data: <?php echo json_encode($reg_counts); ?>,
                    borderColor: 'rgba(78, 115, 223, 1)',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'ស្ថិតិសិស្សចុះឈ្មោះប្រចាំខែ',
                        font: {
                            size: 16,
                            weight: 'bold',
                            family: 'Battambang'
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'ចំនួនសិស្ស',
                            font: {
                                family: 'Battambang'
                            }
                        },
                        ticks: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Battambang'
                            }
                        }
                    }
                }
            }
        });
    </script>
</html>
