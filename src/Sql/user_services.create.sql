CREATE TABLE `user_services` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`timeCreated` datetime NOT NULL,
	`userId` int(11) unsigned NOT NULL,
	`serviceName` varchar(255) NOT NULL DEFAULT '',
	`serviceUserId` varchar(255) NOT NULL DEFAULT '',
	`serviceAccessToken` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `serviceName` (`serviceName`),
	KEY `serviceUserId` (`serviceUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
