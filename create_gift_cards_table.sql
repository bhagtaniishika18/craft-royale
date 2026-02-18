-- Gift Cards Table
CREATE TABLE IF NOT EXISTS `gift_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_number` varchar(20) NOT NULL UNIQUE,
  `pin` varchar(10) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `design` varchar(50) NOT NULL DEFAULT 'default',
  `to_name` varchar(255) NOT NULL,
  `to_email` varchar(255) DEFAULT NULL,
  `to_phone` varchar(20) DEFAULT NULL,
  `from_name` varchar(255) NOT NULL,
  `from_phone` varchar(20) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','active','used','expired','cancelled') DEFAULT 'pending',
  `valid_till` date NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `used_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `valid_till` (`valid_till`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gift Card Designs Table (for admin to manage designs)
CREATE TABLE IF NOT EXISTS `gift_card_designs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `design_name` varchar(100) NOT NULL,
  `design_key` varchar(50) NOT NULL UNIQUE,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `background_color` varchar(7) DEFAULT '#ffffff',
  `background_image` varchar(255) DEFAULT NULL,
  `text_color` varchar(7) DEFAULT '#000000',
  `icon` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gift Card Transactions (for tracking usage)
CREATE TABLE IF NOT EXISTS `gift_card_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gift_card_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount_used` decimal(10,2) NOT NULL,
  `remaining_balance` decimal(10,2) NOT NULL,
  `transaction_type` enum('purchase','usage','refund') DEFAULT 'usage',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gift_card_id` (`gift_card_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
