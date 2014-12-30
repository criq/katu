CREATE TABLE `files` (
  `id` int(11) unsigned NOT null AUTO_INCREMENT,
  `timeCreated` datetime NOT null,
  `creatorId` int(11) unsigned NOT null,
  `path` text NOT null,
  `name` text NOT null,
  `type` varchar(255) NOT null DEFAULT '',
  `size` int(10) unsigned NOT null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
