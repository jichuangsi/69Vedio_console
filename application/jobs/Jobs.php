<?php
namespace app\jobs;

use think\Queue;

class Jobs {
    
    static public function testRedis(){
        $redis = new \Redis();
        $link= $redis->connect('127.0.0.1',6379);
        
        echo "Connection to server successfully";
        echo "server is running:".$redis->ping();
        
        $redis -> select(2);
        $redis -> set('weather','sun');
        $redis -> set('test','good');
        var_dump($redis -> get('test'));
        var_dump($redis -> get('weather'));
        
        exit;
    }
    
    static public function actionWithFtpUploadJob($data){
        
        $param['handler']  = 'app\jobs\handler\FtpUploadJobHandler';
        
        $param['queue']  	  = "ftpUploadJobQueue";
        
        $param['data']       	  = [ 'ts' => time(), 'bizId' => uniqid() , 'data' => $data ] ;
        
        return self::actionWithQueuePush($param);
    }
    
    static private function actionWithQueuePush($param){
        $isPushed = Queue::push( $param['handler'] , $param['data'] , $param['queue'] );
        //$isPushed = Queue::later( 10, $jobHandlerClassName , $jobData , $jobQueueName );
        
        if( $isPushed !== false ){
            return true;
        }else{
            return false;
        }
    }
}