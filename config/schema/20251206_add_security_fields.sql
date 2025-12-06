-- Add password reset token fields to users table
ALTER TABLE `users` 
ADD COLUMN `password_reset_token` VARCHAR(255) NULL AFTER `walkthrough_shown`,
ADD COLUMN `password_reset_expires` DATETIME NULL AFTER `password_reset_token`,
ADD COLUMN `failed_login_attempts` INT DEFAULT 0 AFTER `password_reset_expires`,
ADD COLUMN `account_locked_until` DATETIME NULL AFTER `failed_login_attempts`,
ADD COLUMN `two_factor_secret` VARCHAR(255) NULL AFTER `account_locked_until`,
ADD COLUMN `two_factor_enabled` TINYINT(1) DEFAULT 0 AFTER `two_factor_secret`;

-- Add index for password reset token lookup
ALTER TABLE `users` ADD INDEX `idx_password_reset_token` (`password_reset_token`);
