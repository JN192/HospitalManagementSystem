-- Run once on an existing database; fresh imports already get this index from hms_db.sql.
ALTER TABLE `users`
  ADD UNIQUE KEY `users_username_unique` (`username`);
