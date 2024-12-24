<?php
// Calculate base path based on the current script's location
$current_path = $_SERVER['SCRIPT_NAME'];
$base_path_segments = explode('/', $current_path);
$depth = count($base_path_segments) - 1;
$basePath = str_repeat('../', $depth - 2);
?>
<nav id="sidebar">
    <style>
        #sidebar {
            font-family: 'Battambang', cursive;
        }
        #sidebar .sidebar-header {
            font-family: 'Battambang', cursive;
        }
        #sidebar ul li a {
            font-family: 'Battambang', cursive;
        }
    </style>
    <div class="sidebar-header">
        <center><p style="font-size: 40px;">ក្លឹបកូដ</p></center>
    </div>
    <ul class="list-unstyled components">
        <li>
            <a href="<?php echo $basePath; ?>index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>ផ្ទាំងគ្រប់គ្រង</span>
            </a>
        </li>

        <!-- Student Management -->
        <li>
            <a href="#studentSubmenu" data-bs-toggle="collapse" aria-expanded="false">
                <i class="fas fa-user-graduate"></i>
                <span>គ្រប់គ្រងសិស្ស</span>
            </a>
            <ul class="collapse list-unstyled" id="studentSubmenu">
                <li>
                    <a href="<?php echo $basePath; ?>views/student/student-list.php">
                        <i class="fas fa-list"></i>
                        <span>បញ្ជីសិស្ស</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePath; ?>views/student/add-student.php">
                        <i class="fas fa-user-plus"></i>
                        <span>បន្ថែមសិស្ស</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Teacher Management -->
        <li>
            <a href="#teacherSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>គ្រប់គ្រងគ្រូបង្រៀន</span>
            </a>
            <ul class="collapse list-unstyled" id="teacherSubmenu">
                <li>
                    <a href="<?php echo $basePath; ?>views/teacher/teacher-list.php">
                        <i class="fas fa-list"></i>
                        <span>បញ្ជីគ្រូបង្រៀន</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePath; ?>views/teacher/add-teacher.php">
                        <i class="fas fa-user-plus"></i>
                        <span>បន្ថែមគ្រូបង្រៀន</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Class Management -->
        <li>
            <a href="#classSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                <i class="fas fa-chalkboard"></i>
                <span>គ្រប់គ្រងថ្នាក់រៀន</span>
            </a>
            <ul class="collapse list-unstyled" id="classSubmenu">
                <li>
                    <a href="<?php echo $basePath; ?>views/class/class-list.php">
                        <i class="fas fa-list"></i>
                        <span>បញ្ជីថ្នាក់រៀន</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePath; ?>views/class/add-class.php">
                        <i class="fas fa-plus"></i>
                        <span>បន្ថែមថ្នាក់រៀន</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Subject Management -->
        <li>
            <a href="#subjectSubmenu" data-bs-toggle="collapse" class="dropdown-toggle">
                <i class="fas fa-book"></i>
                <span>មុខវិជ្ជា</span>
            </a>
            <ul class="collapse list-unstyled" id="subjectSubmenu">
                <li>
                    <a href="<?php echo $basePath; ?>views/subject/subject-list.php">
                        <i class="fas fa-list"></i>
                        <span>បញ្ជីមុខវិជ្ជា</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $basePath; ?>views/subject/add-subject.php">
                        <i class="fas fa-plus"></i>
                        <span>បន្ថែមមុខវិជ្ជា</span>
                    </a>
                </li>
            </ul>
        </li>

         <!-- payment Management -->
         <li>
            <a href="#paymentSubmenu" data-bs-toggle="collapse" class="dropdown-toggle">
                <i class="fas fa-book"></i>
                <span>ការបង់ថ្លៃសិក្សា</span>
            </a>
            <ul class="collapse list-unstyled" id="paymentSubmenu">
                <li>
                    <a href="<?php echo $basePath; ?>views/payment/payment-list.php">
                        <i class="fas fa-list"></i>
                        <span>បញ្ជីការបង់ថ្លៃសិក្សា</span>
                    </a>
                </li>
               
            </ul>
        </li>

        <!-- Settings -->
        <li>
            <a href="<?php echo $basePath; ?>views/settings.php">
                <i class="fas fa-cog"></i>
                <span>ការកំណត់</span>
            </a>
        </li>
    </ul>
</nav>
