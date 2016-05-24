CREATE TABLE `settings` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`timeCreated` datetime NOT NULL,
	`timeEdited` datetime NOT NULL,
	`creatorId` int(11) unsigned NOT NULL,
	`name` varchar(255) NOT NULL DEFAULT '',
	`description` text NOT NULL,
	`value` longtext NOT NULL,
	`isSystem` enum('0','1') NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
