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
