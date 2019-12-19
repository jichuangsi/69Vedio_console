<?php
/**
 * 用户相关api接口
 * LastDate:    2019/12/09
 */

namespace app\controller;

use think\Controller;
use think\Exception;
use think\Response;
use think\Request;
use think\Db;


class Userservice extends Controller
{
    private $err = [
        '5001' => '请求方式错误',
        '5002' => '请求接口不存在',
        '5003' => '新用户注册失败',
        '5004' => '设备初始化失败',
        '5005' => '用户未登录',
    ];
    
    /* protected $middleware = ['CrossDomain'];
    
    protected $beforeActionList = [
        'test'   =>  ['only'=>'register'],
    ]; */
    
    public function __construct(Request $request)
    {
        //$origin=$request->header('origin'); //"http://sp.msvodx.com"
        //$allowDomain=['msvodx.com','meisicms.com'];
        /*header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With'); */
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000');
        
        $noAuthAct = ['register','initDevice'];
        
         if (!in_array(strtolower($request->action()), $noAuthAct)) {
             if ($request->isPost() && $request->isAjax()) {
             
             } else {
                 $returnData = ['statusCode' => 5001, 'error' => $this->err['5001']];
                die(json_encode($returnData));
             }
         }
    }
    
    public function _empty()
    {
        $returnData = ['statusCode' => 5002, 'error' => $this->err['5002']];
        die(json_encode($returnData));
    }    
    
    /**
     * 新用户注册，旧用户信息获取
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $screen_width = $request->post('sw');
        $screen_heigth = $request->post('sh');
        $screen_pixelratio = $request->post('sp');
        $gpu_version = $request->post('gv');
        $gpu_renderer = $request->post('gr');
        $device_uuid = $request->post('du');
        
        if(session('member_id')&&session('member_info')){
            die(json_encode(['resultCode' => 0, 'message' => "通过session获取用户成功", 'data' => session('member_info')]));
        }
        
        $uid = Db::name('devices')->field('uid')->where(['du'=>$device_uuid])->limit(1)->order("register_time DESC")->select();
        
        if($uid&&$uid[0]['uid']){            
            $this->fetchMember($uid[0]['uid']);
            die(json_encode(['resultCode' => 0, 'message' => "通过设备uuid获取用户成功", 'data' => session('member_info')]));
        }
        
        unset($map);
        $map['sw'] = $screen_width;
        $map['sh'] = $screen_heigth;
        $map['sp'] = $screen_pixelratio;
        $map['gv'] = $gpu_version;
        $map['gr'] = $gpu_renderer;
        $map['du'] = array('eq', '');
        $map['uid'] = array('eq', 0);
        $user = Db::name('devices')->field('uid,code,puid')->where($map)->limit(1)->order("download_time DESC")->select();
        
        /* if($user&&$user[0]['uid']){            
            $this->fetchMember($user[0]['uid']);
            die(json_encode(['resultCode' => 0, 'message' => "通过设备参数获取用户成功", 'data' => session('member_info')]));
        } */
        
        unset($uid);
        $uid = $this->createMember($request, $user&&$user[0]['puid']?$user[0]['puid']:'');
        
        if(!$uid){
            die(json_encode(['resultCode' => 5003, 'message' => $this->err['5003']]));
        }
        
        if($user&&$uid&&$user[0]['puid']&&$user[0]['code']){            
            $update = [
                'uid'=>intval($uid),
                'du'=>$device_uuid,
                'register_time'=>time()
            ];        
            $ret = Db::name('devices')->where(['puid'=>$user[0]['puid'],'code'=>$user[0]['code']])->update($update);  
            
            Db::name('member')->where(['id'=>$user[0]['puid'],'is_agent'=>0])->update(['is_agent'=>1]);
        }else if(!$user&&$uid){
            $this->createDevice($request, $uid);
        }
        
        die(json_encode(['resultCode' => 0, 'message' => '新用户注册成功', 'data' => session('member_info')]));
    }
    
    public function initDevice(Request $request){
        $screen_width = $request->post('sw');
        $screen_heigth = $request->post('sh');
        $screen_pixelratio = $request->post('sp');
        $gpu_version = $request->post('gv');
        $gpu_renderer = $request->post('gr');
        $puid = $request->post('uid');
        $code = $request->post('code');
        
        unset($devicedata);
        $devicedata['sw'] = $screen_width;
        $devicedata['sh'] = $screen_heigth;
        $devicedata['sp'] = $screen_pixelratio;
        $devicedata['gv'] = $gpu_version;
        $devicedata['gr'] = $gpu_renderer;
        $devicedata['puid'] = $puid;
        $devicedata['code'] = $code;        
        //$ret = Db::name('devices')->where($devicedata)->count();        
        $devicedata['scan_time'] = time();
        
        $did = Db::name('devices')->insertGetId($devicedata);
        
        if($did){
            die(json_encode(['resultCode' => 0, 'data'=>['did'=>$did], 'message' => '设备初始化成功']));
        }else{
            die(json_encode(['resultCode' => 5004, 'error' => $this->err['5004']]));
        }
        
    }
    
    public function getUser(){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        if(session('member_info')){
            die(json_encode(['resultCode' => 0, 'data' => session('member_info'), 'message' => '获取用户成功']));
        }else{
            die(json_encode(['resultCode' => 5005, 'error' => $this->err['5005']]));
        }
        
    }
    
    private function createDevice(Request $request, $uid){
        unset($devicedata);
        $devicedata['sw'] = $request->post('sw');
        $devicedata['sh'] = $request->post('sh');
        $devicedata['sp'] = $request->post('sp');
        $devicedata['gv'] = $request->post('gv');
        $devicedata['gr'] = $request->post('gr');
        $devicedata['du'] = $request->post('du');
        $devicedata['uid'] = $uid;
        $devicedata['register_time'] = time();
        
        $did = Db::name('devices')->insertGetId($devicedata);
        
        return $did;
    }
    
    private function createMember(Request $request, $puid=''){
        unset($userdata);
        $userdata['nickname']=$userdata['username']='用户'.time().mt_rand(100,200);
        $userdata['add_time']=time();
        $userdata['last_ip']=$request->ip();
        if($puid) $userdata['pid']=$puid;
        
        $uid=Db::name('member')->insertGetId($userdata);
        
        if($uid){
            $sessionUserInfo = [
                'userid' => $uid,
                'username' => $userdata['username'],
            ];
            session('member_id', $uid);
            session('member_info', $sessionUserInfo);
        }
        
        return $uid;
    }
    
    private function fetchMember($uid=''){
        
        if(!$uid) return;
        
        $user = Db::name('member')->field('id,username')->where(['id'=>$uid])->select();
        
        if($user){
            $sessionUserInfo = [
                'userid' => $uid,
                'username' => $user[0]['username'],
            ];
            session('member_id', $uid);
            session('member_info', $sessionUserInfo);
        }
        
    }
}