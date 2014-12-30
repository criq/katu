CREATE TABLE `role_permissions` (
	`id` int(11) unsigned NOT null AUTO_INCREMENT,
	`timeCreated` datetime NOT null,
	`roleId` int(11) unsigned NOT null,
	`permission` varchar(255) NOT null DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `roleId` (`roleId`),
	KEY `permission` (`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
