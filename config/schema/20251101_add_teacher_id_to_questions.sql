-- Add teacher_id to questions to record which user (teacher) created/owns the question
-- Add teacher_id to questions to record which user (teacher) created/owns the question
ALTER TABLE `questions`
  ADD COLUMN IF NOT EXISTS `teacher_id` INT NULL AFTER `id`;

-- Optional: add foreign key to users table
ALTER TABLE `questions`
  ADD CONSTRAINT IF NOT EXISTS `fk_questions_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Add an index on teacher_id for faster lookups by owner if it does not already exist
SET @idx_exists_q := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'questions' AND INDEX_NAME = 'idx_questions_teacher_id'
);
SET @sql_q := IF(@idx_exists_q = 0, 'ALTER TABLE `questions` ADD INDEX `idx_questions_teacher_id` (`teacher_id`);', 'SELECT "idx_exists_q";');
PREPARE stmt_q FROM @sql_q;
EXECUTE stmt_q;
DEALLOCATE PREPARE stmt_q;
