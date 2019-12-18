DROP TABLE IF EXISTS `ms_member_collection`;
CREATE TABLE `ms_member_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '用户id',
  `cid` int(11) DEFAULT '0' COMMENT '被关注id',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否互关：0不是，1是',
  `collection_time` int(10) DEFAULT '0' COMMENT '关注时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='用户关注表';