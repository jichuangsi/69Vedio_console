DROP TABLE IF EXISTS `ms_member_location`;
CREATE TABLE `ms_member_location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `latitude` decimal(15,11) unsigned NOT NULL DEFAULT '0.00' COMMENT '经度',
  `longitude` decimal(15,11) unsigned NOT NULL DEFAULT '0.00' COMMENT '维度',
  `address` text default '' COMMENT '详细地址',
  `uid` int(11) default 0 COMMENT '用户id',
  `add_time` int(10) NOT NULL COMMENT '初始时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户地位信息';