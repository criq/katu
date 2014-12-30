CREATE TABLE `email_addresses` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`emailAddress` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT null DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `emailAddress` (`emailAddress`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
