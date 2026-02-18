-- Create discount_codes table
CREATE TABLE IF NOT EXISTS `discount_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `valid_from` date NOT NULL,
  `valid_until` date NOT NULL,
  `usage_limit` int(11) DEFAULT NULL COMMENT 'NULL = unlimited',
  `times_used` int(11) DEFAULT 0,
  `min_order_amount` decimal(10,2) DEFAULT 0,
  `max_discount_amount` decimal(10,2) DEFAULT NULL COMMENT 'NULL = no limit',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_code` (`code`),
  KEY `status` (`status`),
  KEY `valid_until` (`valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample discount codes
INSERT INTO `discount_codes` (`code`, `discount_percentage`, `description`, `valid_from`, `valid_until`, `usage_limit`, `min_order_amount`, `status`) VALUES
('FIRSTSALE', 10.00, 'First Sale - 10% Off', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), NULL, 0, 'active'),
('WELCOME20', 20.00, 'Welcome Discount - 20% Off', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 100, 500, 'active'),
('SAVE15', 15.00, 'Save 15% on your order', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), NULL, 0, 'active');

-- Create discount_code_usage table to track who used which codes
CREATE TABLE IF NOT EXISTS `discount_code_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_code_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `discount_code_id` (`discount_code_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
