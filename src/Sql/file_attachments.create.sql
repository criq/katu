CREATE TABLE `file_attachments` (
  `id` int(11) unsigned NOT null AUTO_INCREMENT,
  `timeCreated` datetime NOT null,
  `creatorId` int(11) unsigned NOT null,
  `objectModel` varchar(255) NOT null DEFAULT '',
  `objectId` int(11) unsigned NOT null,
  `fileId` int(11) unsigned NOT null,
  PRIMARY KEY (`id`),
  KEY `objectModel` (`objectModel`,`objectId`,`fileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
