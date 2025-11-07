-- Add teacher_id to students to record which user (teacher) added/owns the student
-- Add teacher_id to students to record which user (teacher) added/owns the student
-- Use IF NOT EXISTS to make this idempotent on MySQL 8+
ALTER TABLE `students`
  ADD COLUMN IF NOT EXISTS `teacher_id` INT NULL AFTER `id`;

-- Optional: add foreign key to users table if your DB supports it
-- Use a safe add (will error if FK exists). If you prefer guarded FK creation
-- we can create migration scripts instead.
ALTER TABLE `students`
  ADD CONSTRAINT IF NOT EXISTS `fk_students_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Add an index on teacher_id for faster lookups by owner if it does not already exist.
-- Use information_schema check and prepared statement to avoid errors when re-running.
SET @idx_exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND INDEX_NAME = 'idx_students_teacher_id'
);
SET @sql_stmt := IF(@idx_exists = 0, 'ALTER TABLE `students` ADD INDEX `idx_students_teacher_id` (`teacher_id`);', 'SELECT "idx_exists";');
PREPARE stmt FROM @sql_stmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ensure student_code is unique at the database level as well (add if missing)
SET @ux_exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students' AND INDEX_NAME = 'ux_students_student_code'
);
SET @sql_stmt2 := IF(@ux_exists = 0, 'ALTER TABLE `students` ADD UNIQUE INDEX `ux_students_student_code` (`student_code`);', 'SELECT "ux_exists";');
PREPARE stmt2 FROM @sql_stmt2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
