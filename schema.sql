-- 
-- Structure for table `users`
-- 

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `loginToken` varchar(100) NOT NULL,
  `authToken` varchar(100) NOT NULL,
  `timeZone` varchar(20) DEFAULT 'UTC',
  `timeOffset` int(5) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- Structure for table `accounts`
-- 

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- Structure for join table `roles`
-- 

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,  
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` ENUM('blocked', 'invited', 'user', 'mod', 'admin', 'superadmin'),
  `hasJoined` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX account_user (account_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- Structure for table `rooms`
-- 

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX account (account_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 
-- Structure for table `messages`
-- 

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` DATETIME NOT NULL,
  `created_micro` BIGINT UNSIGNED NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  INDEX room (room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
