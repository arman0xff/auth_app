CREATE TABLE IF NOT EXISTS `categories` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `name` char(24) DEFAULT NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `posts` ADD COLUMN `category_id` TINYINT UNSIGNED NULL AFTER `status`, 
    ADD CONSTRAINT `fk_posts_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;