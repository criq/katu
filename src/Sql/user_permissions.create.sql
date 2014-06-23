CREATE TABLE `user_permissions` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`timeCreated` datetime NOT NULL,
	`userId` int(11) unsigned NOT NULL,
	`permission` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `permission` (`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
