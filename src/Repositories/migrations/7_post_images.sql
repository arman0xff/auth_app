CREATE TABLE `post_images` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(50) NOT NULL,
    CONSTRAINT `post_images_post_fk` FOREIGN KEY (`post_id`) 
        REFERENCES `posts` (`id`) ON DELETE CASCADE
);

ALTER TABLE `users` CHANGE COLUMN `image_id` `profile_image_name` CHAR(50) NULL DEFAULT NULL AFTER `bio`;