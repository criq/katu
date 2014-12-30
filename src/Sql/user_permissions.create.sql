CREATE TABLE `user_permissions` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`userId` int(11) unsigned NOT null,
	`permission` varchar(255) NOT null DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `permission` (`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
