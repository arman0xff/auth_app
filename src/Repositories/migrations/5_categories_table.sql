CREATE TABLE IF NOT EXISTS `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` char(24) DEFAULT NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `posts` ADD COLUMN `category_id` INT UNSIGNED NULL AFTER `status`, 
    ADD CONSTRAINT `fk_posts_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE ON DELETE CASCADE;

DELIMITER //
CREATE TRIGGER handle_category_deleting_trigger 
	AFTER DELETE ON `categories` FOR EACH ROW
	BEGIN
		UPDATE `posts` SET `category_id` = (SELECT `id` FROM `categories` LIMIT 1) WHERE `category_id` = OLD.id;
	END //
	
CREATE TRIGGER handle_category_deleting_trigger_2 
BEFORE DELETE ON `categories` FOR EACH ROW
BEGIN
	IF (SELECT COUNT(*) FROM `categories` LIMIT 1) <= 1 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'The table must contain at least one category';
	END IF;
END //
DELIMITER ;