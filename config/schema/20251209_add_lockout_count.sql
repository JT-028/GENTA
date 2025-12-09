-- Add lockout_count field to track progressive account lockouts
-- 0 = no lockouts, 1 = first lockout (15 min), 2 = second lockout (30 min), 3+ = permanent lock
ALTER TABLE `users` 
ADD COLUMN `lockout_count` INT DEFAULT 0 AFTER `account_locked_until`;

-- Add comment to the column
ALTER TABLE `users` 
MODIFY COLUMN `lockout_count` INT DEFAULT 0 COMMENT '0=none, 1=15min, 2=30min, 3+=permanent';
