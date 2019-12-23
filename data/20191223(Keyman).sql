alter table ms_member add `try_and_see` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '剩余试看次数';

DROP TABLE IF EXISTS `ms_video_try_log`;
CREATE TABLE `ms_video_try_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` int(11) NOT NULL COMMENT '视频id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户Id',
  `user_ip` varchar(15) DEFAULT '0' COMMENT '用户ip',
  `try_time` int(10) NOT NULL COMMENT '观看时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='视频试看日志表';