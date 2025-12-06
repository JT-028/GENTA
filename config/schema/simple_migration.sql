-- Simple migration to add security columns to users table
-- Run this in phpMyAdmin on your Cloudways production database

-- Add password reset columns
ALTER TABLE `users` 
ADD COLUMN `password_reset_token` VARCHAR(255) NULL AFTER `password`,
ADD COLUMN `password_reset_expires` DATETIME NULL AFTER `password_reset_token`;

-- Add failed login tracking columns  
ALTER TABLE `users`
ADD COLUMN `failed_login_attempts` INT DEFAULT 0 AFTER `password_reset_expires`,
ADD COLUMN `account_locked_until` DATETIME NULL AFTER `failed_login_attempts`;

-- Add 2FA columns
ALTER TABLE `users`
ADD COLUMN `two_factor_secret` VARCHAR(255) NULL AFTER `account_locked_until`,
ADD COLUMN `two_factor_enabled` TINYINT(1) DEFAULT 0 AFTER `two_factor_secret`;

-- Add index for faster token lookups
CREATE INDEX `idx_password_reset_token` ON `users`(`password_reset_token`);

-- Verify columns were added
SELECT 'Migration completed successfully!' AS status;
SHOW COLUMNS FROM `users` WHERE `Field` IN (
    'password_reset_token', 
    'password_reset_expires',
    'failed_login_attempts',
    'account_locked_until',
    'two_factor_secret',
    'two_factor_enabled'
);
