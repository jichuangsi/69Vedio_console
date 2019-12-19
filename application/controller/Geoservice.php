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

class Geoservice extends Baseservice
{
    private $err = [
        '8001' => '请求方式错误',
        '8002' => '请求接口不存在',
        '8003' => '定位信息异常',
        '8004' => '定位信息超时',
        '8005' => '获取详细地址信息异常',
        '8006' => '保存定位信息异常',
        '8007' => '获取当前用户定位信息异常',
    ];
    
    /* private $member_id;
    
    private $resource_path;
    
    private $listRows = 6;
    
    private $httpType;
    
    private $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    private $default_user_avatar = '/tpl/default/app/static/images/user.png';
    
    private $authHeaders = ['multipart/form-data']; */
    
    private $locateTimeout = 5;    
    
    private $distanceScope = 1000;
    
    private $geoApi = "http://api.map.baidu.com/reverse_geocoding/v3/";
    
    private $locate_api_ak = 'oYQKklZlxM27yqhh8slhuMBQwSD2xzS6';
    
    private $locate_api_output = 'json';
    
    private $locate_api_coordtype = 'wgs84ll';
    
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
        
        $noAuthAct = ['locate','getlocation','nearbymember'];
        
        if (!in_array(strtolower($request->action()), $noAuthAct)) {
            if ($request->isPost() && $request->isAjax()) {
                
            } else {
                $returnData = ['statusCode' => 8001, 'error' => $this->err['8001']];
                die(json_encode($returnData));
            }
        }
    }
    
    public function _empty()
    {
        $returnData = ['statusCode' => 8002, 'error' => $this->err['8002']];
        die(json_encode($returnData));
    } 
    
    /**
     * 用户定位
     * @param Request $request
     * @return mixed
     */
    public function locate(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        /* dump($request->post('latitude'));
        dump($request->post('longitude'));
        dump($request->post('accuracy'));
        dump($request->post('timestamp')); */
        
        $latitude = $request->post('latitude');
        $longitude = $request->post('longitude');
        $accuracy = $request->post('accuracy');
        $timestamp = $request->post('timestamp');
        
        if(!$latitude||!$longitude||!$timestamp){
            die(json_encode(['resultCode' => 8003,'error' => $this->err['8003']]));
        }
        
        if((time()*1000)-(intval($timestamp))>($this->locateTimeout*60*100)){
            die(json_encode(['resultCode' => 8004,'error' => $this->err['8004']]));
        }
        
        //SN 认证
        /* 
        $location = $latitude . "," . $longitude;
        $sk = 'Yr3mfureujU5HiqCLrggKwibCEVvN6Xv';
        $uri = '/reverse_geocoding/v3/';
        //$uri = '/geocoder/v2/';
        $querystring_arrays = array (
            'location' => $location,
            'output' => $output,
            'coordtype' => $coordtype,
            'ak' => $ak
        );
        
        $sn = $this->caculateAKSN($ak, $sk, $uri, $querystring_arrays);
        //dump($sn);
        
        $geoApi = "http://api.map.baidu.com/reverse_geocoding/v3/?location=$location&output=$output&coordtype=$coordtype&ak=$ak&sn=$sn"; */
        
        //IP 认证
        $data = [
            'ak'=>$this->locate_api_ak,
            'output'=>$this->locate_api_output,
            'coordtype'=>$this->locate_api_coordtype,
            'location'=>$latitude . "," . $longitude
        ];
        
        $result = json_decode(curl($this->geoApi, $data));
        
        if($result&&$result->status===0){
            //dump($result);
            //dump($result->result->formatted_address);
            
            unset($data);
            $data['latitude'] = $latitude;
            $data['longitude'] = $longitude;
            $address = '';
            if($result->result&&$result->result->addressComponent){
                if($result->result->addressComponent->province) $address .= $result->result->addressComponent->province;//省份
                if($result->result->addressComponent->city) $address .= $result->result->addressComponent->city;//市
                if($result->result->addressComponent->district) $address .= $result->result->addressComponent->district;//区                
                if($result->result->addressComponent->town) $address .= $result->result->addressComponent->town;//县
                if($result->result->addressComponent->street) $address .= $result->result->addressComponent->street;//街
            }
            $data['address'] = $address;
            
            unset($where);
            $where['uid'] = $this->member_id;            
            $count = Db::name('member_location')->where($where)->count('id');
            $ret = '';
            if($count){
                $data['update_time']  = time();
                $ret = Db::name('member_location')->where($where)->update($data);
            }else{
                $data['add_time'] = $data['update_time']  = time();
                $data['uid'] = $this->member_id;
                $ret = Db::name('member_location')->insertGetId($data);
            }
            
            if($ret){
                die(json_encode(['resultCode' => 0,'message' => "获取定位信息成功",'data' => $address]));
            }else{
                die(json_encode(['resultCode' => 8006,'error' => $this->err['8006']]));
            }
        }
        
        die(json_encode(['resultCode' => 8005,'error' => $this->err['8005']]));
    }
    
    /**
     * 获取定位信息
     * @param Request $request
     * @return mixed
     */
    public function getlocation(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        unset($where);
        $where['uid'] = $this->member_id;
        $locations = Db::name('member_location')->field('latitude,longitude,address')->where($where)->select();
        
        die(json_encode(['resultCode' => 0,'message' => "获取定位信息成功",'data' => $locations]));
    }
    
    /**
     * 获取附近的人列表
     * @param Request $request
     * @return mixed
     */
    public function nearbymember(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $returnData = array();
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        $from = ($page-1)*$rows;        
        
        unset($where);
        $where['uid'] = $this->member_id;
        $locations = Db::name('member_location')->field('latitude,longitude')->where($where)->select();
        
        if($locations&&$locations[0]&&$locations[0]['latitude']&&$locations[0]["longitude"]){            
            $sql = "SELECT ta.uid, ta.address, ACOS(SIN((? * 3.1415) / 180 ) *SIN((ta.latitude * 3.1415) / 180 ) 
                    + COS((? * 3.1415) / 180 ) * COS((ta.latitude * 3.1415) / 180 ) * COS((? * 3.1415) / 180
                    - (ta.longitude * 3.1415) / 180 ) ) * 6371000 as distance FROM ms_member_location ta where ta.uid<>? having distance < ? ORDER BY distance limit ?,?";
            
            $members = Db::query($sql,[$locations[0]['latitude'],$locations[0]['latitude'],$locations[0]["longitude"],$this->member_id,$this->distanceScope,$from,$rows]);
            //echo Db::name('member_location')->getLastSql();
            //dump($members);
            
            foreach($members as &$v){
                $user = Db::name('member')->field('id,username,nickname,headimgurl')->where(['id'=>$v['uid']])->select();
                if($user){
                    $v['username'] = $user[0]['username']?$user[0]['username']:$this->default_user_name;
                    $v['nickname'] = $user[0]['nickname']?$user[0]['nickname']:$v['username'];
                    $v['headimgurl'] = $user[0]['headimgurl']?$this->getFullResourcePath($user[0]['headimgurl'], $user[0]['id']):$this->getDefaultUserAvater();
                }else{
                    $v['username'] = $v['nickname'] = $this->default_user_name;
                    $v['headimgurl'] = $this->getDefaultUserAvater();
                }
                
                //检查是否关注,0为已关注，1为互关，null为没关注
                unset($map);
                $map['uid'] = $this->member_id;
                $map['cid'] = $v['uid'];                
                $cuser = Db::name('member_collection')->field('id,status')->where($map)->select();
                if($cuser&&$cuser[0]){
                    $v['concerned'] = $cuser[0]['status'];
                }else{
                    $v['concerned'] = null;
                }
                
                array_push($returnData, $v);
            }
            
            die(json_encode(['resultCode' => 0,'message' => "获取附近的人列表成功",'data' => $returnData]));
        }
        
    
        die(json_encode(['resultCode' => 8007,'error' => $this->err['8007']]));
    }
    
    private function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
    {
        if ($method === 'POST'){
            ksort($querystring_arrays);
        }
        $querystring = http_build_query($querystring_arrays);
        //echo urlencode($url.'?'.$querystring.$sk);
        return md5(urlencode($url.'?'.$querystring.$sk));
    }
    
}