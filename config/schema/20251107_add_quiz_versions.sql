-- Migration: Add quiz_versions table and quiz_version_id to student_quiz
-- Run this SQL in a safe migration tool or via your deployment process.

ALTER TABLE `student_quiz`
  ADD COLUMN `quiz_version_id` INT NULL AFTER `subject_id`;

CREATE TABLE `quiz_versions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `quiz_id` INT NOT NULL,
  `version_number` INT NOT NULL DEFAULT 1,
  `question_ids` JSON NOT NULL,
  `metadata` JSON DEFAULT NULL,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_quiz_versions_quiz_id` (`quiz_id`),
  CONSTRAINT `fk_quiz_versions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: adjust the referenced `quiz` table name if different in your schema.
