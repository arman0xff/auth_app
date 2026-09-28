CREATE TABLE `post_comments` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`post_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
	`text` VARCHAR(255) NOT NULL,
	`created_at` DATETIME NOT NULL DEFAULT (NOW()),
    `deleted_at` DATETIME NULL,
	PRIMARY KEY (`id`),
    KEY `idx_post_id` (`post_id`),
    KEY `idx_parent_id` (`parent_id`),
	CONSTRAINT `comments_post_id_fk` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `comments_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `comments_comment_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `post_comments` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
)
COLLATE='utf8mb4_0900_ai_ci';