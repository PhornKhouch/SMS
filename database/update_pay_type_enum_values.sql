-- Update the pay_type column to include all necessary values
ALTER TABLE payments 
MODIFY COLUMN pay_type ENUM('Full', 'Monthly', 'Half') NOT NULL DEFAULT 'Full';
