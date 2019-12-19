alter table `ms_member` add `introduce` varchar(255) COLLATE utf8_unicode_ci DEFAULT null COMMENT '用户简介';  
alter table `ms_member` add `birthday` int(11) COLLATE utf8_unicode_ci DEFAULT null COMMENT '生日';  
alter table `ms_member` add `region` int(10) COLLATE utf8_unicode_ci DEFAULT 0 COMMENT '地区'; 