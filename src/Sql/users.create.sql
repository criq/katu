CREATE TABLE `users` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`emailAddressId` int(11) unsigned NOT null,
	`name` varchar(255) NOT null DEFAULT '',
	`password` text NOT null,
	PRIMARY KEY (`id`),
	KEY `emailAddressId` (`emailAddressId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
