ALTER TABLE payments
ADD COLUMN pay_type ENUM('Full', 'Installment') NOT NULL DEFAULT 'Full'
AFTER payment_status;
