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


class Memberservice extends Baseservice
{
    private $err = [
        '9001' => '请求方式错误',
        '9002' => '请求接口不存在',
    ];
    
    /* private $member_id;
    
    private $resource_path;
    
    private $listRows = 6;
    
    private $httpType;
    
    private $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    private $default_user_avatar = '/tpl/default/app/static/images/user.png';
    
    private $authHeaders = ['multipart/form-data']; */
    
    public function __construct(Request $request)
    {
        //$origin=$request->header('origin'); //"http://sp.msvodx.com"
        //$allowDomain=['msvodx.com','meisicms.com'];
        /*header("Access-Control-Allow-Origin: *");
         header('Access-Control-Allow-Credentials: true');
         header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
         header('Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With'); */
        
        /* $returnData = check_app_login();
        if($returnData['statusCode']>1){
            die(json_encode($returnData));
        }
        
        $this->member_id = session('member_id');
        $this->resource_path = 'public' . DS . 'uploads' . DS;
        $this->httpType = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        
        header('Access-Control-Allow-Origin: *');
        if(!empty($request->header('Content-Type'))&&in_array(strtolower($request->header('Content-Type')), $this->authHeaders)){
            header('Access-Control-Allow-Credentials: true');
        }
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000'); */
        
        parent::__construct($request);
        
        $noAuthAct = [];
        
        if (!in_array(strtolower($request->action()), $noAuthAct)) {
            if ($request->isPost() && $request->isAjax()) {
                
            } else {
                $returnData = ['statusCode' => 9001, 'error' => $this->err['9001']];
                die(json_encode($returnData));
            }
        }
    }
    
    public function _empty()
    {
        $returnData = ['statusCode' => 9002, 'error' => $this->err['9002']];
        die(json_encode($returnData));
    } 
    
    
}