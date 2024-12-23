-- Create subjects table
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `credits` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_code` (`subject_code`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `subjects_teacher_fk` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `subjects` (`subject_code`, `subject_name`, `credits`, `description`) VALUES
('MATH101', 'គណិតវិទ្យាទូទៅ', 3, 'មុខវិជ្ជាគណិតវិទ្យាមូលដ្ឋាន'),
('PHY101', 'រូបវិទ្យាទូទៅ', 4, 'មុខវិជ្ជារូបវិទ្យាមូលដ្ឋាន'),
('CHEM101', 'គីមីវិទ្យាទូទៅ', 4, 'មុខវិជ្ជាគីមីវិទ្យាមូលដ្ឋាន'),
('BIO101', 'ជីវវិទ្យាទូទៅ', 4, 'មុខវិជ្ជាជីវវិទ្យាមូលដ្ឋាន'),
('ENG101', 'ភាសាអង់គ្លេសទូទៅ', 3, 'មុខវិជ្ជាភាសាអង់គ្លេសមូលដ្ឋាន'),
('KH101', 'ភាសាខ្មែរទូទៅ', 3, 'មុខវិជ្ជាភាសាខ្មែរមូលដ្ឋាន'),
('CS101', 'កុំព្យូទ័រមូលដ្ឋាន', 3, 'មុខវិជ្ជាកុំព្យូទ័រមូលដ្ឋាន'),
('ART101', 'សិល្បៈទូទៅ', 2, 'មុខវិជ្ជាសិល្បៈមូលដ្ឋាន'),
('MUS101', 'តន្ត្រីទូទៅ', 2, 'មុខវិជ្ជាតន្ត្រីមូលដ្ឋាន'),
('PE101', 'អប់រំកាយទូទៅ', 2, 'មុខវិជ្ជាអប់រំកាយមូលដ្ឋាន');

-- Create trigger for updated_at
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `subjects_updated_at` 
BEFORE UPDATE ON `subjects`
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END;//
DELIMITER ;
