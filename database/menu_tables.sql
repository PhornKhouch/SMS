-- Menu table
CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    url VARCHAR(255),
    parent_id INT DEFAULT NULL,
    order_index INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES menus(id) ON DELETE CASCADE
);

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Role permissions table
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    menu_id INT NOT NULL,
    can_view BOOLEAN DEFAULT FALSE,
    can_add BOOLEAN DEFAULT FALSE,
    can_edit BOOLEAN DEFAULT FALSE,
    can_delete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);

-- User roles table
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Insert default menus
INSERT INTO menus (name, icon, url, parent_id, order_index) VALUES
('ផ្ទាំងគ្រប់គ្រង', 'fas fa-tachometer-alt', 'index.php', NULL, 1),
('គ្រប់គ្រងសិស្ស', 'fas fa-user-graduate', '#', NULL, 2),
('តារាងព័ត៌មានសិស្ស', 'fas fa-table', 'views/student/student-list.php', 2, 1),
('បង្កើតសិស្ស', 'fas fa-plus-circle', 'views/student/add-student.php', 2, 2),
('គ្រប់គ្រងគ្រូបង្រៀន', 'fas fa-chalkboard-teacher', '#', NULL, 3),
('តារាងព័ត៌មានគ្រូបង្រៀន', 'fas fa-table', 'views/teacher/teacher-list.php', 5, 1),
('បង្កើតគ្រូបង្រៀន', 'fas fa-plus-circle', 'views/teacher/add-teacher.php', 5, 2),
('គ្រប់គ្រងថ្នាក់រៀន', 'fas fa-chalkboard', '#', NULL, 4),
('តារាងព័ត៌មានថ្នាក់រៀន', 'fas fa-table', 'views/class/class-list.php', 8, 1),
('បង្កើតថ្នាក់រៀន', 'fas fa-plus-circle', 'views/class/add-class.php', 8, 2),
('មុខវិជ្ជា', 'fas fa-book', '#', NULL, 5),
('តារាងព័ត៌មានមុខវិជ្ជា', 'fas fa-table', 'views/subject/subject-list.php', 11, 1),
('បង្កើតមុខវិជ្ជា', 'fas fa-plus-circle', 'views/subject/add-subject.php', 11, 2),
('ការបង់ថ្លៃសិក្សា', 'fas fa-money-bill', '#', NULL, 6),
('តារាងព័ត៌មានការបង់ថ្លៃសិក្សា', 'fas fa-table', 'views/payment/payment-list.php', 14, 1),
('របាយការណ៍', 'fas fa-chart-bar', '#', NULL, 7),
('របាយការណ៍សិស្ស', 'fas fa-file-alt', 'views/Report/RPTStudent.php', 16, 1),
('របាយការណ៍ការបង់ថ្លៃសិក្សា', 'fas fa-file-alt', 'views/Report/RPTPayment.php', 16, 2),
('ការកំណត់', 'fas fa-cog', 'views/settings/list-settings.php', NULL, 8);

-- Insert default roles
INSERT INTO roles (name, description) VALUES
('Admin', 'Full system access'),
('Teacher', 'Teacher access with limited permissions'),
('Staff', 'Staff access with payment management');

-- Insert default permissions for Admin role (full access)
INSERT INTO role_permissions (role_id, menu_id, can_view, can_add, can_edit, can_delete)
SELECT 1, id, TRUE, TRUE, TRUE, TRUE FROM menus;
