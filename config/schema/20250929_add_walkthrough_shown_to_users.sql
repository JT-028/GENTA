-- Migration: Add walkthrough_shown to users table
ALTER TABLE users ADD COLUMN walkthrough_shown TINYINT(1) NOT NULL DEFAULT 0;