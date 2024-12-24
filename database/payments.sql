CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    payment_date DATE NOT NULL,
    payment_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash', 'Bank Transfer', 'ABA', 'Wing', 'True Money') NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Completed',
    academic_year VARCHAR(20) NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    payment_for ENUM('Tuition Fee', 'Registration Fee', 'Other Fee') NOT NULL,
    reference_number VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
