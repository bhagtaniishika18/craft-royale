-- ============================================
-- SIMPLE VERSION - Run this if the main version gives errors
-- ============================================

-- Step 1: Create subcategories table
CREATE TABLE IF NOT EXISTS `subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `subcategory_name` varchar(255) NOT NULL,
  `subcategory_slug` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 2: Check if products table exists
-- If it doesn't exist, create it with all columns
-- If it exists, we'll add missing columns separately

-- First, let's see what your current products table structure is
-- Run this query first to see your current structure:
-- DESCRIBE products;

-- ============================================
-- OPTION A: If products table doesn't exist yet, run this:
-- ============================================
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL DEFAULT '',
  `sku` varchar(100) DEFAULT NULL,
  `mrp` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL,
  `discount_percent` int(11) DEFAULT 0,
  `description` text,
  `short_description` text,
  `image` varchar(255) NOT NULL,
  `images` text DEFAULT NULL COMMENT 'JSON array of additional images',
  `stock` int(11) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `country_of_origin` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `subcategory_id` (`subcategory_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- OPTION B: If products table already exists, run these ALTER statements one by one
-- Comment out the ones that give errors (meaning the column already exists)
-- ============================================

-- Add subcategory_id (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `subcategory_id` int(11) DEFAULT NULL AFTER `category_id`;

-- Add slug (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `slug` varchar(255) NOT NULL DEFAULT '' AFTER `name`;

-- Add sku (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `sku` varchar(100) DEFAULT NULL AFTER `slug`;

-- Add mrp (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `mrp` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `sku`;

-- Add discount_percent (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `discount_percent` int(11) DEFAULT 0 AFTER `price`;

-- Add short_description (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `short_description` text AFTER `description`;

-- Add images (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `images` text DEFAULT NULL COMMENT 'JSON array of additional images' AFTER `image`;

-- Add stock (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `stock` int(11) DEFAULT 0 AFTER `images`;

-- Add status (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `status` enum('active','inactive','out_of_stock') DEFAULT 'active' AFTER `stock`;

-- Add country_of_origin (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `country_of_origin` varchar(100) DEFAULT NULL AFTER `status`;

-- Add color (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `color` varchar(50) DEFAULT NULL AFTER `country_of_origin`;

-- Add size (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `size` varchar(50) DEFAULT NULL AFTER `color`;

-- Add material (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `material` varchar(100) DEFAULT NULL AFTER `size`;

-- Add weight (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `weight` varchar(50) DEFAULT NULL AFTER `material`;

-- Add updated_at (run only if it doesn't exist)
-- ALTER TABLE `products` ADD COLUMN `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Rename product_name to name (if product_name exists)
-- ALTER TABLE `products` CHANGE COLUMN `product_name` `name` varchar(255) NOT NULL;

-- ============================================
-- Step 3: Add indexes (run these if they don't exist)
-- ============================================
-- ALTER TABLE `products` ADD INDEX `category_id` (`category_id`);
-- ALTER TABLE `products` ADD INDEX `subcategory_id` (`subcategory_id`);
-- ALTER TABLE `products` ADD INDEX `status` (`status`);

-- ============================================
-- Step 4: Add foreign keys (run these if categories and subcategories tables exist)
-- ============================================
-- ALTER TABLE `products` ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE;
-- ALTER TABLE `products` ADD CONSTRAINT `fk_products_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories`(`id`) ON DELETE SET NULL;










