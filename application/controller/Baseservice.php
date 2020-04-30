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
    
    protected $default_user_name = '未知用户';
    
    protected $default_offical_name = '69官方';
    
    
    public function __construct(Request $request)
    {        
        
        $returnData = check_app_login();
        if($returnData['resultCode']>1){
//        die(json_encode($returnData));
        }
        
        $this->member_id =session('member_id');
        //更新已过期的会员状态
        $user = Db::name('member')->field('out_time,is_permanent,id,username,money,gid')->where(['id'=>$this->member_id])->select();
        if($user[0]['out_time']<=time()&&$user[0]['is_permanent']!=1&&$user[0]['gid']==2){
        	Db::name('member')->where(['id'=>$this->member_id])->update(['gid'=>1]);
        }
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
    /**
     * @describe 数组生成正则表达式
     * @param array $words
     * @return string
     */
    protected function generateRegularExpression($words)
    {
        $regular = implode('|', array_map('preg_quote', $words));
        return "/$regular/i";
    }
    /**
     * @describe 字符串 生成正则表达式
     * @param array $words
     * @return string
     */
    protected function generateRegularExpressionString($string){
          $str_arr[0]=$string;
          $str_new_arr=  array_map('preg_quote', $str_arr);
          return $str_new_arr[0];
    }
    /**
     * 检查敏感词
     * @param $banned
     * @param $string
     * @return bool|string
     */
    protected function check_words($banned,$string)
    {    $match_banned=array();
        //循环查出所有敏感词

        $new_banned=strtolower($banned);
        $i=0;
        do{
            $matches=null;
            if (!empty($new_banned) && preg_match($new_banned, $string, $matches)) {
                $isempyt=empty($matches[0]);
                if(!$isempyt){
                    $match_banned = array_merge($match_banned, $matches);
                    $matches_str=strtolower($this->generateRegularExpressionString($matches[0]));
                    $new_banned=str_replace("|".$matches_str."|","|",$new_banned);
                    $new_banned=str_replace("/".$matches_str."|","/",$new_banned);
                    $new_banned=str_replace("|".$matches_str."/","/",$new_banned);
                }
            }
            $i++;
            if($i>20){
                $isempyt=true;
                break;
            }
        }while(count($matches)>0 && !$isempyt);
 
        //查出敏感词
        if($match_banned){
            return $match_banned;
        }
        //没有查出敏感词
        return array();
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