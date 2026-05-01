-- Widen columns so full emails, "username = email" sign-up, and long country names work.
-- Run once on production (phpMyAdmin, mysql CLI, or Dokploy migration).
ALTER TABLE `tourists`
  MODIFY `username` VARCHAR(191) NOT NULL,
  MODIFY `email` VARCHAR(191) NOT NULL,
  MODIFY `country` VARCHAR(100) NOT NULL;
