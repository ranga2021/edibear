-- Password reset tokens for tourists (run once).
ALTER TABLE `tourists`
  ADD COLUMN `password_reset_token` varchar(64) DEFAULT NULL,
  ADD COLUMN `password_reset_expires` datetime DEFAULT NULL;
