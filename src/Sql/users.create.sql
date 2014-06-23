CREATE TABLE `users` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`timeCreated` datetime NOT NULL,
	`emailAddressId` int(11) unsigned NOT NULL,
	`name` varchar(255) NOT NULL DEFAULT '',
	`password` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `emailAddressId` (`emailAddressId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
