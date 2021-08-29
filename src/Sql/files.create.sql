CREATE TABLE `files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timeCreated` datetime NOT NULL,
  `creatorId` int(11) unsigned NOT NULL,
  `path` text NOT NULL,
  `name` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
