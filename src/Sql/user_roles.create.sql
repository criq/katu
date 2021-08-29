CREATE TABLE `user_roles` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`timeCreated` datetime NOT NULL,
	`userId` int(11) unsigned NOT NULL,
	`roleId` int(11) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `roleId` (`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
