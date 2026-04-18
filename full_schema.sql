CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` VARCHAR(255) PRIMARY KEY,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `content` TEXT,
  `tech_stack` TEXT,
  `url` VARCHAR(255),
  `thumbnail_url` VARCHAR(255),
  `gallery_images` TEXT,
  `demo_login` TEXT,
  `access_points` TEXT,
  `type` ENUM('web', 'app') DEFAULT 'web',
  `wa_message` TEXT,
  `seo_data` TEXT,
  `performance` TEXT,
  `inquiries_count` INT DEFAULT 0,
  `is_pinned` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Update projects table
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `performance_scores` TEXT;
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `code_snippet` TEXT;

-- New tables
CREATE TABLE IF NOT EXISTS `api_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider` VARCHAR(50),
    `endpoint` VARCHAR(255),
    `status` VARCHAR(20),
    `response_time` FLOAT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `newsletters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `testimonies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT,
    `client_name` VARCHAR(255),
    `content` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
);
