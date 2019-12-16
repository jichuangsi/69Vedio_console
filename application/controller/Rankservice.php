<?php
/**
 * 排行相关api接口
 * LastDate:    2019/12/09
 */

namespace app\controller;

use think\Controller;
use think\Exception;
use think\Response;
use think\Request;
use think\Db;
use think\Db\Query;

class Rankservice extends Controller
{
    private $err = [
        '7001' => '请求方式错误',
        '7002' => '请求接口不存在',
    ];
    
    private $member_id;
    
    //private $resource_path;
    
    private $listRows = 6;
    
    private $httpType;
    
    private $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    private $default_user_avatar = '/tpl/default/app/static/images/user.png';
    
    //private $authHeaders = ['multipart/form-data'];
    
    public function __construct(Request $request)
    {
        //$origin=$request->header('origin'); //"http://sp.msvodx.com"
        //$allowDomain=['msvodx.com','meisicms.com'];
        /*header("Access-Control-Allow-Origin: *");
         header('Access-Control-Allow-Credentials: true');
         header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
         header('Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With'); */
        
        $returnData = check_app_login();
        if($returnData['statusCode']>1){
            die(json_encode($returnData));
        }
        
        $this->member_id = session('member_id');
        //$this->resource_path = 'public' . DS . 'uploads' . DS;
        $this->httpType = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        
        header('Access-Control-Allow-Origin: *');
        /* if(!empty($request->header('Content-Type'))&&in_array(strtolower($request->header('Content-Type')), $this->authHeaders)){
            header('Access-Control-Allow-Credentials: true');
        } */
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000');
        
        $noAuthAct = ['popularrank','inviterank','uploadrand'];
        
        if (!in_array(strtolower($request->action()), $noAuthAct)) {
            if ($request->isPost() && $request->isAjax()) {
                
            } else {
                $returnData = ['statusCode' => 7001, 'error' => $this->err['6001']];
                die(json_encode($returnData));
            }
        }
    }
    
    public function _empty()
    {
        $returnData = ['statusCode' => 6002, 'error' => $this->err['7002']];
        die(json_encode($returnData));
    } 
    
    /**
     * 人气排行
     * @param Request $request
     * @return mixed
     */
    public function popularrank(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $limit1 = $request->post('limit1')?$request->post('limit1'):4;
        $limit2 = $request->post('limit2')?$request->post('limit2'):1;
        $limit3 = $request->post('limit3')?$request->post('limit3'):1;
        
        $goodmost = Db::name('video')->field('user_id, sum(good) as goods, count(id) as vids')->group('user_id')->order('goods desc, vids desc')->limit($limit1)->select();
                
        foreach($goodmost as &$v){
            if($v['user_id']===0){
                $v['username'] = '69官方';
                $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_app_avatar;
            }else{
                $user = Db::name('member')->field('id,username,headimgurl')->where(['id'=>$v['user_id']])->select();
                if($user){                    
                    $v['username'] = $user[0]['username'];
                    $v['headimgurl'] = $user[0]['headimgurl'];
                }else{
                    $v['username'] = '未知用户';
                    $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
                }
            }
        }
        
        $invitemost = Db::name('member')->field('pid, count(id) as uids')->where('pid','GT', 0)->group('pid')->order('uids desc')->limit($limit2)->select();
        
        foreach($invitemost as &$v){
            $user = Db::name('member')->field('id,username,headimgurl')->where(['id'=>$v['pid']])->select();
            if($user){
                $v['username'] = $user[0]['username'];
                $v['headimgurl'] = $user[0]['headimgurl']?$user[0]['headimgurl']:$this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
            }else{
                $v['username'] = '未知用户';
                $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
            }
        }
        
        $uploademost = Db::name('video')->field('user_id, count(id) as vids')->group('user_id')->order('vids desc')->limit($limit3)->select();
        
        foreach($uploademost as &$v){
            if($v['user_id']===0){
                $v['username'] = '69官方';
                $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_app_avatar;
            }else{
                $user = Db::name('member')->field('id,username,headimgurl')->where(['id'=>$v['user_id']])->select();
                if($user){
                    $v['username'] = $user[0]['username'];
                    $v['headimgurl'] = $user[0]['headimgurl'];
                }else{
                    $v['username'] = '未知用户';
                    $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
                }
            }
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取人气排行成功",'data' => ['good'=>$goodmost,'invite'=>$invitemost,'upload'=>$uploademost]]));
    }
    
    /**
     * 邀请大神
     * @param Request $request
     * @return mixed
     */
    public function inviterank(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $returnData = $members = array();
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        $invitemost = Db::name('member')->field('pid, count(id) as uids')->where('pid','GT', 0)->group('pid')->order('uids desc')
                                        ->paginate(['page'=>$page, 'list_rows'=>$rows]);
        
        $members = $invitemost->items();
        $currentPage = $invitemost->currentPage();
        $total = $invitemost->total();
        
        $returnData['currentPage'] = $currentPage;
        $returnData['total'] = $total;
        $returnData['members'] = array();
        foreach($members as &$v){
            $user = Db::name('member')->field('id,username,headimgurl')->where(['id'=>$v['pid']])->select();
            if($user){
                $v['username'] = $user[0]['username'];
                $v['headimgurl'] = $user[0]['headimgurl']?$user[0]['headimgurl']:$this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
            }else{
                $v['username'] = '未知用户';
                $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
            }
            array_push($returnData['members'], $v);
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取邀请大神成功",'data' => $returnData]));
    }
    
    /**
     * 上传大神
     * @param Request $request
     * @return mixed
     */
    public function uploadrand(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $returnData = $members = array();
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        $uploademost = Db::name('video')->field('user_id, count(id) as vids')->group('user_id')->order('vids desc')
                                        ->paginate(['page'=>$page, 'list_rows'=>$rows]);
        
        $members = $uploademost->items();
        $currentPage = $uploademost->currentPage();
        $total = $uploademost->total();
                   
        $returnData['currentPage'] = $currentPage;
        $returnData['total'] = $total;
        $returnData['members'] = array();
        foreach($members as &$v){
            if($v['user_id']===0){
                $v['username'] = '69官方';
                $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_app_avatar;
            }else{
                $user = Db::name('member')->field('id,username,headimgurl')->where(['id'=>$v['user_id']])->select();
                if($user){
                    $v['username'] = $user[0]['username'];
                    $v['headimgurl'] = $user[0]['headimgurl'];
                }else{
                    $v['username'] = '未知用户';
                    $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
                }
            }
            array_push($returnData['members'], $v);
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取上传大神成功",'data' => $returnData]));
    }
}