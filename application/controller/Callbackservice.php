<?php
/**
 * 服务回调相关api接口
 * LastDate:    2019/12/09
 */

namespace app\controller;

use think\Controller;
use think\Exception;
use think\Response;
use think\Request;
use think\Db;


class Callbackservice extends Controller
{
    private $err = [
        '3001' => 'request method error',
        '3002' => 'api is not existed',
        '3003' => 'parameter of post is missing in notify url of preview',
        '3004' => 'resource id is missing in notify url of preview',
        '3005' => 'resource path is missing in notify url of preview',
        '3006' => 'resource is not existed',
        '3007' => 'update preview of resource fail',
    ];
    
    private $msg = [
        'preivew_notify_url' => 'update preview of resource successfully',
    ];
    
    public function __construct(Request $request)
    {        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000');
        
        $noAuthAct = ['preivewnotifyurl'];
        
         if (!in_array(strtolower($request->action()), $noAuthAct)) {
             if ($request->isPost() && $request->isAjax()) {
             
             } else {
                 $returnData = ['statusCode' => 3001, 'error' => $this->err['3001']];
                die(json_encode($returnData));
             }
         }
    }
    
    public function _empty()
    {
        $returnData = ['statusCode' => 3002, 'error' => $this->err['3002']];
        die(json_encode($returnData));
    }    
    
    /**
     * 视频预览异步通知接口
     */
    public function preivewnotifyurl(Request $request) {
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $p = $request->param('p/s', '');
        if(!$p){
            die(json_encode(['resultCode' => 3003,'error' => $this->err['3003']]));
        }
        
        $post =  json_decode($p);
        $vid = $post->id;
        $preview = $post->path;
        
        if(!$vid){
            die(json_encode(['resultCode' => 3004,'error' => $this->err['3004']]));
        }
        
        if(!$preview){
            die(json_encode(['resultCode' => 3005,'error' => $this->err['3005']]));
        }
        
        $user = Db::name('video')->field('user_id')->where(['id'=>$vid])->find();
        if($user&&$user["user_id"]){
            $s_preview = str_replace($user["user_id"].DS, '', str_replace('\\', DS, str_replace('/', DS, $preview)));
            $ret = Db::name('video')->where(['id'=>$vid])->update(['preview'=>$s_preview]);
            
            if($ret){
                die(json_encode(['resultCode' => 0,'message' => $this->msg['preivew_notify_url'], 'data' => $ret]));
            }else{
                die(json_encode(['resultCode' => 3007,'error' => $this->err['3007'],'data'=>$ret]));
            }
        }else{
            die(json_encode(['resultCode' => 3006,'error' => $this->err['3006']]));
        }        
    }
}