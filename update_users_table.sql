-- SQL script to update users table for registration
-- Run this in your MySQL/phpMyAdmin if the mobile_no column doesn't exist

-- Add mobile_no column if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS mobile_no VARCHAR(20) AFTER last_name;

-- If the above doesn't work (older MySQL versions), use this instead:
-- ALTER TABLE users ADD COLUMN mobile_no VARCHAR(20) AFTER last_name;

-- Verify the table structure
-- DESCRIBE users;


