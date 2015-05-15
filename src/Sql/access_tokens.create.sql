CREATE TABLE `access_tokens` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timeCreated` datetime NOT NULL,
  `timeExpires` datetime NOT NULL,
  `userId` int(11) unsigned NOT NULL,
  `accessToken` varchar(255) COLLATE utf8_czech_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `accessToken` (`accessToken`),
  KEY `timeExpires` (`timeExpires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
