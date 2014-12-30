CREATE TABLE `user_roles` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`userId` int(11) unsigned NOT null,
	`roleId` int(11) unsigned NOT null,
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `roleId` (`roleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
