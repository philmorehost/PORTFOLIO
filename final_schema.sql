-- Final Schema for Portfolio 1.0

-- Admin Profile Table
CREATE TABLE IF NOT EXISTS `admin_profile` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100),
    `full_name` VARCHAR(100),
    `bio` TEXT,
    `whatsapp_number` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API Management Table
CREATE TABLE IF NOT EXISTS `api_settings` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `provider` ENUM('deepseek', 'gemini', 'manual') DEFAULT 'manual',
    `deepseek_key` TEXT,
    `gemini_key` TEXT,
    `psi_key` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `deepseek_base_url` VARCHAR(255) DEFAULT 'https://api.deepseek.com'
);

-- Project Table (Enhanced)
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255),
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `category` ENUM('WEB', 'APP') DEFAULT 'WEB',
    `description` TEXT,
    `screenshot_path` VARCHAR(255),
    `demo_link` VARCHAR(255),
    `tech_stack` JSON, -- Smart-Badge Tech Stack
    `seo_tags` JSON,
    `wa_custom_message` TEXT,
    `performance_scores` JSON, -- Tech Pulse Card
    `code_snippet` TEXT, -- The Vault
    `is_pinned` TINYINT(1) DEFAULT 0,
    `inquiries_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- API Logs for Usage Monitor
CREATE TABLE IF NOT EXISTS `api_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `provider` VARCHAR(50),
    `endpoint` VARCHAR(255),
    `status` VARCHAR(20),
    `response_time` FLOAT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Newsletter Quick-Capture
CREATE TABLE IF NOT EXISTS `newsletters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
