-- Run once on an existing database to enforce unique usernames.
ALTER TABLE `users`
  ADD UNIQUE KEY `users_username_unique` (`username`);
