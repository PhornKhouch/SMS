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

    // Count total records
    $count_query = "SELECT COUNT(*) FROM subjects";
    if (!empty($search)) {
        $count_query .= " WHERE subject_name LIKE :search OR subject_code LIKE :search";
    }
    $count_stmt = $pdo->prepare($count_query);
    if (!empty($search)) {
        $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_count = $count_stmt->fetchColumn();

    // Main query
    $query = "SELECT s.*, t.name as teacher_name 
              FROM subjects s 
              LEFT JOIN teachers t ON s.teacher_id = t.id";
    $params = [];

    // Add search if provided
    if (!empty($search)) {
        $query .= " WHERE s.subject_name LIKE :search OR s.subject_code LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Add ordering and pagination
    $query .= " ORDER BY s.id DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $records_per_page;
    $params[':offset'] = $offset;

    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => &$value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    $subjects = [];
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បញ្ជីមុខវិជ្ជា</title>
    <link href="https://fonts.googleapis.com/css2?family=Battambang:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
    <style>
        body {
            font-family: 'Battambang', cursive;
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
                            <h2>បញ្ជីមុខវិជ្ជា</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>">ទំព័រដើម</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">បញ្ជីមុខវិជ្ជា</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="add-subject.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> បន្ថែមមុខវិជ្ជា
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Search and Records per page -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <form class="d-flex">
                                    <input type="text" name="search" class="form-control me-2" placeholder="ស្វែងរក..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">ស្វែងរក</button>
                                </form>
                            </div>
                            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                <label class="me-2">ចំនួនជួរក្នុងមួយទំព័រ:</label>
                                <select class="form-select d-inline-block w-auto" onchange="this.form.submit()" name="records_per_page">
                                    <option value="10" <?php echo $records_per_page == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo $records_per_page == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo $records_per_page == 50 ? 'selected' : ''; ?>>50</option>
                                    <option value="100" <?php echo $records_per_page == 100 ? 'selected' : ''; ?>>100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjects Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>លេខរៀង</th>
                                        <th>កូដមុខវិជ្ជា</th>
                                        <th>ឈ្មោះមុខវិជ្ជា</th>
                                        <th>គ្រូបង្រៀន</th>
                                        <th>ឥណទាន</th>
                                        <th>ពិពណ៌នា</th>
                                        <th>សកម្មភាព</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($subjects)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">មិនមានទិន្នន័យ</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($subjects as $index => $subject): ?>
                                            <tr>
                                                <td><?php echo $offset + $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($subject['teacher_name'] ?? 'មិនបានកំណត់'); ?></td>
                                                <td><?php echo htmlspecialchars($subject['credits']); ?></td>
                                                <td><?php echo htmlspecialchars($subject['description']); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="actionDropdown<?php echo $subject['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                            សកម្មភាព
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="actionDropdown<?php echo $subject['id']; ?>">
                                                            <li><button class="dropdown-item view-subject" data-id="<?php echo $subject['id']; ?>">
                                                                <i class="fas fa-eye"></i> មើល
                                                            </button></li>
                                                            <li><button class="dropdown-item view-lessons" data-id="<?php echo $subject['id']; ?>" data-subject="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                                                <i class="fas fa-book"></i> មើលមេរៀន
                                                            </button></li>
                                                            <li><a class="dropdown-item" href="edit-subject.php?id=<?php echo $subject['id']; ?>">
                                                                <i class="fas fa-edit"></i> កែប្រែ
                                                            </a></li>
                                                            <li><button class="dropdown-item delete-subject" data-id="<?php echo $subject['id']; ?>">
                                                                <i class="fas fa-trash"></i> លុប
                                                            </button></li>
                                                        </ul>
                                                    </div>
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
                                    
                                    // Previous page link
                                    if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&records_per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif;

                                    // Numbered page links
                                    for ($i = max(1, $page - $pagination_range); $i <= min($total_pages, $page + $pagination_range); $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&records_per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor;

                                    // Next page link
                                    if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&records_per_page=<?php echo $records_per_page; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
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

    <!-- Modal for View Subject -->
    <div class="modal fade" id="viewSubjectModal" tabindex="-1" aria-labelledby="viewSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSubjectModalLabel">ព័ត៌មានលម្អិតមុខវិជ្ជា</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>លេខកូដមុខវិជ្ជា៖</strong> <span id="subject-code"></span></p>
                            <p><strong>ឈ្មោះមុខវិជ្ជា៖</strong> <span id="subject-name"></span></p>
                            <p><strong>ឥណទាន៖</strong> <span id="subject-credits"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>គ្រូបង្រៀន៖</strong> <span id="subject-teacher"></span></p>
                            <p><strong>ការពិពណ៌នា៖</strong> <span id="subject-description"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បិទ</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for View Lessons -->
    <div class="modal fade" id="viewLessonsModal" tabindex="-1" aria-labelledby="viewLessonsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewLessonsModalLabel">មេរៀនទាំងអស់</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="lessons-list">
                        <!-- Lessons will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Upload Lesson -->
    <div class="modal fade" id="uploadLessonModal" tabindex="-1" aria-labelledby="uploadLessonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadLessonModalLabel">បន្ថែមមេរៀនថ្មី</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadLessonForm" enctype="multipart/form-data">
                        <input type="hidden" name="subject_id" id="upload_subject_id">
                        <div class="mb-3">
                            <label for="lesson_name" class="form-label">ឈ្មោះមេរៀន</label>
                            <input type="text" class="form-control" id="lesson_name" name="lesson_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="lesson_file" class="form-label">ជ្រើសរើសឯកសារ PDF</label>
                            <input type="file" class="form-control" id="lesson_file" name="lesson_file" accept=".pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary">បន្ថែម</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF Viewer Modal -->
    <div class="modal fade" id="pdfViewerModal" tabindex="-1" aria-labelledby="pdfViewerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pdfViewerModalLabel">មើលមេរៀន PDF</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <iframe id="pdfViewer" src="" style="width: 100%; height: 100%; border: none;"></iframe>
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
        // Check system status when page loads
        $.ajax({
            url: 'check-upload.php',
            type: 'GET',
            success: function(response) {
                if (!response.success) {
                    console.error('System check failed:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('System check error:', error);
            }
        });

        // Check for success message
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

        // Check for error message
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

        // View Subject
        $('.view-subject').click(function() {
            const id = $(this).data('id');
            
            // Show loading
            Swal.fire({
                title: 'សូមរង់ចាំ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch subject details
            $.ajax({
                url: 'view-subject.php',
                method: 'GET',
                data: { id: id },
                success: function(response) {
                    Swal.close();
                    
                    if (response.success) {
                        const subject = response.subject;
                        
                        // Populate modal with subject details
                        $('#subject-code').text(subject.subject_code);
                        $('#subject-name').text(subject.subject_name);
                        $('#subject-credits').text(subject.credits);
                        $('#subject-teacher').text(subject.teacher_name || 'មិនមានគ្រូបង្រៀន');
                        $('#subject-description').text(subject.description || 'មិនមានការពិពណ៌នា');
                        
                        // Show modal
                        $('#viewSubjectModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'មានបញ្ហា',
                            text: response.message || 'មានបញ្ហាក្នុងការទាញយកព័ត៌មាន',
                            confirmButtonText: 'យល់ព្រម'
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'មានបញ្ហា',
                        text: 'មានបញ្ហាក្នុងការទាញយកព័ត៌មាន',
                        confirmButtonText: 'យល់ព្រម'
                    });
                }
            });
        });

        // Delete Subject
        $('.delete-subject').click(function() {
            const id = $(this).data('id');
            const subjectName = $(this).closest('tr').find('td:nth-child(3)').text();

            Swal.fire({
                title: 'តើអ្នកប្រាកដទេ?',
                text: `តើអ្នកពិតជាចង់លុបមុខវិជ្ជា "${subjectName}" នេះមែនទេ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'យល់ព្រម',
                cancelButtonText: 'បោះបង់',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'កំពុងដំណើរការ...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX request
                    $.ajax({
                        url: 'delete-subject.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
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
                                    // Reload the page or remove the row
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
                        error: function(xhr, status, error) {
                            Swal.close();
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

        // View Lessons
        $('.view-lessons').click(function() {
            const subjectId = $(this).data('id');
            
            // Load lessons for this subject
            $.ajax({
                url: 'get-lessons.php',
                type: 'GET',
                data: { subject_id: subjectId },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        let html = '<div class="list-group">';
                        if (data.lessons.length > 0) {
                            data.lessons.forEach(lesson => {
                                html += `
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">${lesson.lesson_name}</h6>
                                            <small class="text-muted">បង្កើតនៅ: ${lesson.created_at}</small>
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary view-pdf" data-file="../../${lesson.file_path}">
                                                <i class="fas fa-eye"></i> មើល
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-lesson" data-id="${lesson.id}">
                                                <i class="fas fa-trash"></i> លុប
                                            </button>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html += '<div class="text-center p-3">មិនមានមេរៀននៅឡើយទេ</div>';
                        }
                        html += '</div>';
                        html += `
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="openUploadModal(${subjectId})">
                                    <i class="fas fa-plus"></i> បន្ថែមមេរៀនថ្មី
                                </button>
                            </div>
                        `;
                        $('.lessons-list').html(html);
                        $('#viewLessonsModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'បរាជ័យ!',
                            text: data.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'បរាជ័យ!',
                        text: 'មានបញ្ហាក្នុងការទាញយកមេរៀន'
                    });
                }
            });
        });

        // View PDF in Modal
        $(document).on('click', '.view-pdf', function(e) {
            e.preventDefault();
            const pdfFile = $(this).data('file');
            // Remove any leading dots and slashes
            const cleanPath = pdfFile.replace(/^[./\\]+/, '');
            const viewerUrl = 'view-pdf.php?file=' + encodeURIComponent(cleanPath);
            $('#pdfViewer').attr('src', viewerUrl);
            $('#viewLessonsModal').modal('hide');
            $('#pdfViewerModal').modal('show');
        });

        // When PDF viewer modal is hidden, show lessons modal again
        $('#pdfViewerModal').on('hidden.bs.modal', function () {
            $('#viewLessonsModal').modal('show');
            $('#pdfViewer').attr('src', ''); // Clear the PDF source
        });

        // Function to open upload modal
        window.openUploadModal = function(subjectId) {
            $('#upload_subject_id').val(subjectId);
            $('#viewLessonsModal').modal('hide');
            $('#uploadLessonModal').modal('show');
        };

        // Handle lesson upload
        $('#uploadLessonForm').submit(function(e) {
            e.preventDefault();
            
            // Show loading indicator
            Swal.fire({
                title: 'កំពុងផ្ទុកឡើង...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData(this);
            
            $.ajax({
                url: 'upload-lesson.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'ជោគជ័យ!',
                                text: 'មេរៀនត្រូវបានបន្ថែមដោយជោគជ័យ'
                            }).then(() => {
                                $('#uploadLessonForm')[0].reset();
                                $('#uploadLessonModal').modal('hide');
                                // Refresh lessons list
                                $('.view-lessons[data-id="' + formData.get('subject_id') + '"]').click();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'បរាជ័យ!',
                                text: data.message || 'មានបញ្ហាក្នុងការបន្ថែមមេរៀន'
                            });
                        }
                    } catch (e) {
                        console.error('Upload error:', e);
                        Swal.fire({
                            icon: 'error',
                            title: 'បរាជ័យ!',
                            text: 'មានបញ្ហាក្នុងការបន្ថែមមេរៀន'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'បរាជ័យ!',
                        text: 'មានបញ្ហាក្នុងការបន្ថែមមេរៀន'
                    });
                }
            });
        });

        // Delete lesson
        $(document).on('click', '.delete-lesson', function() {
            const lessonId = $(this).data('id');
            
            Swal.fire({
                title: 'តើអ្នកប្រាកដទេ?',
                text: 'មេរៀននេះនឹងត្រូវលុបចោល',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'យល់ព្រម',
                cancelButtonText: 'បោះបង់'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete-lesson.php',
                        type: 'POST',
                        data: { id: lessonId },
                        success: function(response) {
                            const data = JSON.parse(response);
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'ជោគជ័យ!',
                                    text: 'មេរៀនត្រូវបានលុបដោយជោគជ័យ'
                                }).then(() => {
                                    // Refresh lessons list
                                    $('.view-lessons:first').click();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'បរាជ័យ!',
                                    text: data.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'បរាជ័យ!',
                                text: 'មានបញ្ហាក្នុងការលុបមេរៀន'
                            });
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
