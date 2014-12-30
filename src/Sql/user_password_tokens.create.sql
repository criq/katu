CREATE TABLE `user_password_tokens` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`timeExpires` datetime NOT null,
	`timeUsed` datetime NOT null,
	`userId` int(11) unsigned NOT null,
	`token` char(20) NOT null DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
