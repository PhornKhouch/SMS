-- First, update the ENUM values to match the form options
ALTER TABLE payments 
MODIFY COLUMN pay_type ENUM('Full', 'Monthly', 'Half') NOT NULL DEFAULT 'Full';

-- Update any existing records with old values to use new values
UPDATE payments SET pay_type = 'Full' WHERE pay_type NOT IN ('Full', 'Monthly', 'Half');
