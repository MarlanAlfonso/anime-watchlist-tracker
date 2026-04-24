-- ============================================
-- ANIME WATCHLIST TRACKER - Database Setup
-- ============================================

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Anime Watchlist table (personal list)
CREATE TABLE IF NOT EXISTS `anime_watchlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `genre` VARCHAR(100) NOT NULL,
  `episodes` INT NOT NULL,
  `status` ENUM('Watching','Completed','Dropped','Plan to Watch') NOT NULL,
  `rating` DECIMAL(3,1) NOT NULL,
  `cover_image` VARCHAR(255) DEFAULT 'default_cover.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- General Anime table (public list on index.php)
CREATE TABLE IF NOT EXISTS `general_anime` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `genre` VARCHAR(100) NOT NULL,
  `episodes` INT NOT NULL,
  `status` ENUM('Ongoing','Completed','Upcoming') NOT NULL,
  `description` TEXT,
  `cover_image` VARCHAR(255) DEFAULT 'default_cover.png',
  `added_by` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Anime Hearts table
CREATE TABLE IF NOT EXISTS `anime_hearts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `anime_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_heart` (`anime_id`, `user_id`),
  FOREIGN KEY (`anime_id`) REFERENCES `general_anime`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Anime Ratings table
CREATE TABLE IF NOT EXISTS `anime_ratings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `anime_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `rating` DECIMAL(3,1) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_rating` (`anime_id`, `user_id`),
  FOREIGN KEY (`anime_id`) REFERENCES `general_anime`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;