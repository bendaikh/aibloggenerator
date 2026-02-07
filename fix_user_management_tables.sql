-- Fix User Management Tables Migration Script
-- Run this on production database if migrations are failing

-- IMPORTANT: This will drop the tables and all their data
-- Only run if you haven't started using these tables yet

SET FOREIGN_KEY_CHECKS=0;

-- Drop tables if they exist (in reverse order of dependencies)
DROP TABLE IF EXISTS `role_permission`;
DROP TABLE IF EXISTS `permissions`;

-- Only drop the role_id column if it was partially created
-- Check first with: SHOW COLUMNS FROM `users` LIKE 'role_id';
-- ALTER TABLE `users` DROP FOREIGN KEY IF EXISTS `users_role_id_foreign`;
-- ALTER TABLE `users` DROP COLUMN IF EXISTS `role_id`;

-- Don't drop roles table if it has data you want to keep
-- DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS=1;

-- After running this script:
-- 1. Delete the failed migration entries from the migrations table:
--    DELETE FROM `migrations` WHERE `migration` LIKE '%role%' OR `migration` LIKE '%permission%';
-- 2. Run: php artisan migrate
