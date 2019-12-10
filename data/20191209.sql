DROP TABLE IF EXISTS `ms_devices`;
CREATE TABLE `ms_devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sw` varchar(50) NOT NULL COMMENT '设备屏幕宽度',
  `sh` varchar(50) NOT NULL COMMENT '设备屏幕高度',
  `sp` double(11,10) NOT NULL COMMENT '设备像素比',
  `gv` varchar(255) NOT NULL COMMENT '设备显卡版本',
  `gr` varchar(255) NOT NULL COMMENT '设备显卡渲染器',
  `du` varchar(255) default '' COMMENT '设备uuid',
  `code` varchar(10) default '' COMMENT '邀请码',
  `puid` int(11) default 0 COMMENT '邀请人',
  `uid` int(11) default 0 COMMENT '被邀请人',
  `scan_time` int(10) NOT NULL COMMENT '被邀请人扫码时间',
  `download_time` int(10) NOT NULL COMMENT '被邀请人下载时间',
  `register_time` int(10) NOT NULL COMMENT '被邀请人注册时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;