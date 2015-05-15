CREATE TABLE `access_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timeCreated` datetime NOT NULL,
  `timeExpires` datetime NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  `token` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `timeExpires` (`timeExpires`),
  KEY `userId` (`userId`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
