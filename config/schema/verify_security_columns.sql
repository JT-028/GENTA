-- Verify and add security columns if they don't exist
-- Run this on production database to ensure password reset functionality works

-- Check if columns exist and add them if missing
SET @dbname = DATABASE();

-- Add password_reset_token column if it doesn't exist
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA=@dbname
     AND TABLE_NAME='users'
     AND COLUMN_NAME='password_reset_token') > 0,
    'SELECT ''password_reset_token already exists'' AS status',
    'ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(255) NULL AFTER password'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add password_reset_expires column if it doesn't exist
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA=@dbname
     AND TABLE_NAME='users'
     AND COLUMN_NAME='password_reset_expires') > 0,
    'SELECT ''password_reset_expires already exists'' AS status',
    'ALTER TABLE users ADD COLUMN password_reset_expires DATETIME NULL AFTER password_reset_token'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add failed_login_attempts column if it doesn't exist
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA=@dbname
     AND TABLE_NAME='users'
     AND COLUMN_NAME='failed_login_attempts') > 0,
    'SELECT ''failed_login_attempts already exists'' AS status',
    'ALTER TABLE users ADD COLUMN failed_login_attempts INT DEFAULT 0 AFTER password_reset_expires'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add account_locked_until column if it doesn't exist
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA=@dbname
     AND TABLE_NAME='users'
     AND COLUMN_NAME='account_locked_until') > 0,
    'SELECT ''account_locked_until already exists'' AS status',
    'ALTER TABLE users ADD COLUMN account_locked_until DATETIME NULL AFTER failed_login_attempts'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add two_factor_secret column if it doesn't exist
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA=@dbname
     AND TABLE_NAME='users'
     AND COLUMN_NAME='two_factor_secret') > 0,
    'SELECT ''two_factor_secret already exists'' AS status',
    'ALTER TABLE users ADD COLUMN two_factor_secret VARCHAR(255) NULL AFTER account_locked_until'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add two_factor_enabled column if it doesn't exist
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA=@dbname
     AND TABLE_NAME='users'
     AND COLUMN_NAME='two_factor_enabled') > 0,
    'SELECT ''two_factor_enabled already exists'' AS status',
    'ALTER TABLE users ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0 AFTER two_factor_secret'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index on password_reset_token for faster lookups
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA=@dbname
     AND TABLE_NAME='users'
     AND INDEX_NAME='idx_password_reset_token') > 0,
    'SELECT ''Index idx_password_reset_token already exists'' AS status',
    'CREATE INDEX idx_password_reset_token ON users(password_reset_token)'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verify all columns exist
SELECT 
    'Verification Complete' AS status,
    COUNT(CASE WHEN COLUMN_NAME = 'password_reset_token' THEN 1 END) AS has_reset_token,
    COUNT(CASE WHEN COLUMN_NAME = 'password_reset_expires' THEN 1 END) AS has_reset_expires,
    COUNT(CASE WHEN COLUMN_NAME = 'failed_login_attempts' THEN 1 END) AS has_failed_attempts,
    COUNT(CASE WHEN COLUMN_NAME = 'account_locked_until' THEN 1 END) AS has_locked_until,
    COUNT(CASE WHEN COLUMN_NAME = 'two_factor_secret' THEN 1 END) AS has_2fa_secret,
    COUNT(CASE WHEN COLUMN_NAME = 'two_factor_enabled' THEN 1 END) AS has_2fa_enabled
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA=@dbname AND TABLE_NAME='users'
AND COLUMN_NAME IN (
    'password_reset_token',
    'password_reset_expires', 
    'failed_login_attempts',
    'account_locked_until',
    'two_factor_secret',
    'two_factor_enabled'
);
