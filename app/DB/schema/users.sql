-- ----------------------------
-- Table structure for users
-- ----------------------------
-- DROP TABLE IF EXISTS `users`;
CREATE TABLE `user` (
  `id`              int(11)       NOT NULL AUTO_INCREMENT
, `email`           VARCHAR(255)
, `uuid`            VARCHAR(255)
, `created_at`      TIMESTAMP
, `updated_at`      TIMESTAMP
, PRIMARY KEY (`id`)
) ENGINE=MyISAM;