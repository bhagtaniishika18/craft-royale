-- Add subcategory_slug column to subcategories table if it doesn't exist
-- Run this SQL query in your database

-- Check if column exists and add it if it doesn't
ALTER TABLE `subcategories` 
ADD COLUMN IF NOT EXISTS `subcategory_slug` varchar(255) DEFAULT NULL AFTER `subcategory_name`;

-- If the above doesn't work (MySQL version doesn't support IF NOT EXISTS for ALTER TABLE),
-- use this instead:

-- First check if column exists manually, then run:
ALTER TABLE `subcategories` 
ADD COLUMN `subcategory_slug` varchar(255) DEFAULT NULL AFTER `subcategory_name`;

-- Update existing subcategories to have slugs
UPDATE `subcategories` 
SET `subcategory_slug` = LOWER(REPLACE(REPLACE(REPLACE(`subcategory_name`, ' ', '-'), '/', '-'), '&', 'and'))
WHERE `subcategory_slug` IS NULL OR `subcategory_slug` = '';










