-- Add email verification fields to users table
ALTER TABLE `users` 
ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `token`,
ADD COLUMN `verification_token` VARCHAR(255) NULL AFTER `email_verified`,
ADD COLUMN `verification_token_expires` DATETIME NULL AFTER `verification_token`;

-- Add index for verification token lookups
ALTER TABLE `users` ADD INDEX `idx_verification_token` (`verification_token`);
