ALTER TABLE payments
MODIFY COLUMN pay_type ENUM('Full', 'Monthly', 'Half') NOT NULL DEFAULT 'Full';
