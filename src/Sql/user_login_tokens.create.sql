CREATE TABLE `user_login_tokens` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`timeCreated` datetime NOT NULL,
	`timeExpires` datetime NOT NULL,
	`timeUsed` datetime NOT NULL,
	`userId` int(11) unsigned NOT NULL,
	`token` char(20) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `userId` (`userId`),
	KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
