<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

return [
    'connector'  => 'Redis',		    // Redis ����
    'expire'     => 60,				// ����Ĺ���ʱ�䣬Ĭ��Ϊ60��; ��Ҫ���ã�������Ϊ null
    'default'    => 'default',		// Ĭ�ϵĶ�������
    'host'       => '127.0.0.1',	    // redis ����ip
    'port'       => 6379,			// redis �˿�
    'password'   => '',				// redis ����
    'select'     => 0,				// ʹ����һ�� db��Ĭ��Ϊ db0
    'timeout'    => 0,				// redis���ӵĳ�ʱʱ��
    'persistent' => false,			// �Ƿ��ǳ�����
    
    //    'connector' => 'Database',   // ���ݿ�����
    //    'expire'    => 60,           // ����Ĺ���ʱ�䣬Ĭ��Ϊ60��; ��Ҫ���ã�������Ϊ null
    //    'default'   => 'default',    // Ĭ�ϵĶ�������
    //    'table'     => 'jobs',       // �洢��Ϣ�ı���������ǰ׺
    //    'dsn'       => [],
    
    //    'connector'   => 'Topthink',	// ThinkPHP�ڲ��Ķ���֪ͨ����ƽ̨ �����Ĳ�������
    //    'token'       => '',
    //    'project_id'  => '',
    //    'protocol'    => 'https',
    //    'host'        => 'qns.topthink.com',
    //    'port'        => 443,
    //    'api_version' => 1,
    //    'max_retries' => 3,
    //    'default'     => 'default',
    
    //    'connector'   => 'Sync',		// Sync ��������������ʵ��������ȡ����Ϣ���У���ԭΪͬ��ִ��
];
