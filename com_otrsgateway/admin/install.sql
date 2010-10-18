DROP TABLE IF EXISTS `#__otrsgateway_attachments`;
 
CREATE TABLE `#__otrsgateway_attachments` (
  `id` VARCHAR(23) NOT NULL,
  `filename` VARCHAR(50) NOT NULL,
  `token` VARCHAR(50) NOT NULL,
  `realname` VARCHAR(255) NOT NULL,
  `username` VARCHAR(150) NOT NULL,
  `uploaded` TIMESTAMP,
  `content_type` VARCHAR(50) NOT NULL DEFAULT 'application/octet-stream',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
 

