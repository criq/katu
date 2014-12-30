CREATE TABLE `user_services` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`userId` int(11) unsigned NOT null,
	`serviceName` varchar(255) NOT null DEFAULT '',
	`serviceUserId` varchar(255) NOT null DEFAULT '',
	`serviceAccessToken` text NOT null,
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `serviceName` (`serviceName`),
	KEY `serviceUserId` (`serviceUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
