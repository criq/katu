CREATE TABLE `settings` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`timeEdited` datetime NOT null,
	`creatorId` int(11) unsigned NOT null,
	`name` varchar(255) NOT null DEFAULT '',
	`description` text NOT null,
	`value` longtext NOT null,
	`isSystem` enum('0','1') NOT null DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
