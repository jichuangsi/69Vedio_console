insert into ms_admin_config values(null,'base','视频提成设置','video_royalty',80,'用户发布需要购买的视频可以获得多少金币');
insert into ms_admin_config values(null,'base','视频代理商提成设置','video_royalty_agent',10,'用户购买视频的消费代理商获得的提成');
insert into ms_admin_config values(null,'base','充值vip提成设置','vip_royalty',80,'用户充值会员代理商获得的提成');
insert into ms_admin_config values(null,'base','提现手续费','service_harge',10,'用户提现收取的手续费');
insert into ms_admin_config values(null,'video','每日免费观看次数(2)','look_at_num_mobile2',10,'推荐2人以上每日免费观看次数');
insert into ms_admin_config values(null,'video','每日免费观看次数(3)','look_at_num_mobile3',15,'推荐5人以上每日免费观看次数');
insert into ms_admin_config values(null,'video','每日免费观看次数(4)','look_at_num_mobile4',25,'推荐10人以上每日免费观看次数');
insert into ms_admin_config values(null,'video','每日免费观看次数(5)','look_at_num_mobile5',50,'推荐20人以上每日免费观看次数');

alter table ms_gold_log add `agent_uid` int(8) unsigned NOT NULL DEFAULT 0 COMMENT '代理id';
alter table ms_gold_log add `rid` int(8) unsigned NOT NULL DEFAULT 0 COMMENT '资源id';