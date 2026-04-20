-- Edibear admin: test@gmail.com / test1234  (admin-area login)
--
-- This file is SQL for MySQL/MariaDB — do not paste into Bash.
--
-- Run from a machine that has the file, e.g.:
--   mysql -h 127.0.0.1 -P 3306 -u edibear-user -p edibear < sql/add_test_admin_user.sql
--
-- In the DB container, copy the file in first, then:
--   mysql -u edibear-user -p'YOUR_PASSWORD' edibear < /tmp/add_test_admin_user.sql
--
-- One-liner from Bash (heredoc; quotes protect the $ characters in the bcrypt hash):

INSERT INTO `user_table` (`id`, `first_name`, `last_name`, `login_email`, `password`, `mobile_number`, `register_timestamp`, `delete_status`) VALUES (6, 'Test', 'Admin', 'test@gmail.com', 'pass$2y$10$L8zmqpx/1aqB0u1e.fwD2e7Ivv9NKeOHPsyjYw261iWmqCZZdHGhq', '', CURRENT_TIMESTAMP, 0);
