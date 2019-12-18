<?php
/**
 * 视频相关api接口
 * LastDate:    2019/12/09
 */

namespace app\controller;

use think\Controller;
use think\Exception;
use think\Response;
use think\Request;
use think\Db;
use think\Db\Query;


class Baseservice extends Controller
{    
    
    protected $member_id;
    
    protected $resource_path;
    
    protected $listRows = 6;
    
    protected $httpType;
    
    protected $authHeaders = ['multipart/form-data'];
    
    protected $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    protected $default_user_avatar = '/tpl/default/app/static/images/user.png';
    
    public function __construct(Request $request)
    {        
        
        $returnData = check_app_login();
        if($returnData['resultCode']>1){
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
        header('Access-Control-Max-Age: 1728000');        
        
    }
    
    public function _empty()
    {
        
    }    
    
    protected function getFullResourcePath($path='', $uid=''){
        if(!$path) return null;
        
        if(stripos($path,'http') > -1) return $path;
        
        if($uid){
            $fullPath = $this->httpType.$_SERVER['HTTP_HOST']."/uploads/$uid/".str_replace('\\','/',$path);
        }else{
            if($this->member_id){
                $fullPath = $this->httpType.$_SERVER['HTTP_HOST']."/uploads/$this->member_id/".str_replace('\\','/',$path);
            }else{
                $fullPath = $this->httpType.$_SERVER['HTTP_HOST']."/uploads/".str_replace('\\','/',$path);
            }
        }        
        
        return $fullPath;
    }
    
    protected function getDefaultUserAvater($offical = false){
        if($offical){
            return $this->httpType.$_SERVER['HTTP_HOST'].$this->default_app_avatar;
        }else{
            return $this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
        }
        
    }
    
}