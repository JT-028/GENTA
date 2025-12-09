-- Add lockout_count column to users table for progressive lockout system
-- Run this on Cloudways database if the column doesn't exist yet

-- Check if column exists and add it if not
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS lockout_count INT DEFAULT 0 NOT NULL 
COMMENT 'Number of times account has been locked (0=never, 1=first 15min, 2=second 30min, 3+=permanent)';

-- Update existing records to have default value
UPDATE users SET lockout_count = 0 WHERE lockout_count IS NULL;

-- Verify the column was added
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_DEFAULT, 
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' 
AND COLUMN_NAME = 'lockout_count';
