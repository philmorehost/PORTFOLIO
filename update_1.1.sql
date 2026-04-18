-- Update for Portfolio 1.1
ALTER TABLE `admin_profile` ADD COLUMN IF NOT EXISTS `role` INT DEFAULT 0;
ALTER TABLE `admin_profile` ADD COLUMN IF NOT EXISTS `legacy_notes` TEXT;
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `gallery` JSON;
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `demo_access` JSON;
ALTER TABLE `projects` ADD COLUMN IF NOT EXISTS `is_pinned` TINYINT(1) DEFAULT 0;
