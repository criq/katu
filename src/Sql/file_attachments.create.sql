CREATE TABLE `file_attachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timeCreated` datetime NOT NULL,
  `creatorId` int(11) unsigned NOT NULL,
  `objectModel` varchar(255) NOT NULL DEFAULT '',
  `objectId` int(11) unsigned NOT NULL,
  `fileId` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objectModel` (`objectModel`,`objectId`,`fileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
