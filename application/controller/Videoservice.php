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

use app\common\FFMpegUtil;
use app\jobs\Jobs;

class Videoservice extends Baseservice
{
    private $err = [
        '6001' => '请求方式错误',
        '6002' => '请求接口不存在',
        '6003' => '视频上传异常',
        '6004' => '视频上传失败',
        '6005' => '视频校验错误',
        '6006' => '视频保存失败',
        '6007' => '缺少参数',
        '6008' => '视频不存在',
        '6009' => '用户不存在',
        '6010' => '视频点赞失败',
        '6011' => '取消点赞失败'
    ];
    
    private $video_length = 15;
    
    private $video_cover_size = 1048576*5;
    
    private $video_cover_ext = 'jpg,png,gif,jpeg';
    
    private $video_default_size = 1048576*600;
    
    private $video_default_ext = 'mp4,mov';
    
    /* private $member_id;
    
    private $resource_path;
    
    private $listRows = 6;
    
    private $httpType;
    
    private $authHeaders = ['multipart/form-data'];
    
    private $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    private $default_user_avatar = '/tpl/default/app/static/images/user.png'; */
    
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
        
        $noAuthAct = ['upload','myvideos','latestvideos','payvideos','playmostvideos','likemostvideos','commentmostvideos','gettags','getclasses','homevideo','videocollection',
            'concernvideos','cancelcollection','videosearch','mybuyvideos','ftp','preview'
        ]; 
        
        if (!in_array(strtolower($request->action()), $noAuthAct)) {
            if ($request->isPost() && $request->isAjax()) {
                
            } else {
                $returnData = ['statusCode' => 6001, 'error' => $this->err['6001']];
                die(json_encode($returnData));
            }
        }
    }
    
    public function _empty()
    {
        $returnData = ['statusCode' => 6002, 'error' => $this->err['6002']];
        die(json_encode($returnData));
    }    
    
    /**
     * ftp上传例子
     * @param Request $request
     */
    private function ftp(Request $request){
        
        $s = $request->param('s/s','');
        
        $ret = ftp_upload($s);
        
        if($ret){
            die(json_encode(['resultCode' => $ret['code'],'message' => $ret['msg']]));
        }else{
            die(json_encode(['resultCode' => -1,'message' => '未知错误']));
        }        
    }
    
    /**
     * 远程预览服务例子
     * @param Request $request
     */
    private function preview(Request $request){
        $s = $request->param('s/s','');
        
        $p = str_replace(str_replace(DS, '/', ROOT_PATH) . 'public/uploads', config('app_key'), $s);
        
        $data = ['p'=>$p];
        $result = json_decode(curl("http://192.168.31.108:73/api/Job/actionWithVideoPreviewJob", $data));
        
        dump($result);
    }
    
    /*
     * 首页推荐视频
     */
    public function homevideo(Request $request){
		if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
//      die(json_encode(['resultCode' => 0,'message' => '获取首页推荐视频成功','data' => '1231231']));
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($param);
        $uid=$this->member_id;
//      $param['where'] = ['v.title'=>['like','%ff%']];
        $param['where'] = ['v.status'=>1,'v.recommend'=>1];
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'add_time desc';
        $videos=$this->fetchVideos($param);
        if(!empty($videos['videos'])){
        	foreach($videos['videos'] as $k=>$val){
				$videos['videos'][$k]['isgood']=DB::name('video_good_log')->where(['user_id'=>$uid,'video_id'=>$val['id']])->count();        		
//      		$videos['videos'][$k]['url']='require("'.$val['url'].'")';
//      		$videos['videos'][$k]['headimgurl']='require("'.$val['headimgurl'].'")';
//      		$videos['videos'][$k]['thumbnail']='require("'.$val['thumbnail'].'")';
        	}
        }else{
        	$videos['videos']=array();
        }
        die(json_encode(['resultCode' => 0,'message' => '获取推荐视频成功','data' => $videos]));
    }
    
    /**
     *视频搜索 
     */
    public function videosearch(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $keyword=$request->post('k');
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($param);
        $taglist=Db::name('tag')->field('id')->where(['type' => 1,'status' => 1,'name'=>['like',"%$keyword%"]])->select();
		
		$param['taglist']=$taglist;
        $param['where'] =['v.title' => ['like',"%$keyword%"]];
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'add_time desc';
        $videos=$this->fetchVideos($param);
        if(!empty($videos['videos'])){
        	foreach($videos['videos'] as $k=>$val){
				$videos['videos'][$k]['isgood']=DB::name('video_good_log')->where(['user_id'=>$this->member_id,'video_id'=>$val['id']])->count();        		
        	}
        }else{
        	$videos['videos']=array();
        }
        
//      $videos['taglist']=$taglist;
        $videos['keyword']=$keyword;
        die(json_encode(['resultCode' => 0,'message' => '获取搜索视频成功','data' => $videos]));
    }
    
    /*
     * 点赞视频
     */
    public function videocollection(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid=$this->member_id;//$request->post('uid');   //用户id
        $vid=$request->post('vid');   //视频id
        if(empty($uid) || empty($vid)){
        	die(json_encode(['resultCode' => 6007, 'error' => $this->err['6007']]));
        }
        $vcount=Db::name('video')->where(['id'=>$vid])->count(); //检查视频是否存在
        if($vcount<=0){
        	die(json_encode(['resultCode' => 6008, 'error' => $this->err['6008']]));
        }
        $ucount=Db::name('member')->where('id',$uid)->count();  //检查用户是否存在
        if($ucount<=0){
        	die(json_encode(['resultCode' => 6009, 'error' => $this->err['6009']]));
        }
        $vcount=DB::name('video_good_log')->where(['user_id'=>$uid,'video_id'=>$vid])->count();
        if($vcount>0){
        	die(json_encode(['resultCode'=>0,'message'=>'已点赞','data'=>$vcount]));
        }
        $time=time();
        $vcdata=[
        	'user_id'=>$uid,
        	'video_id'=>$vid,
        	'add_time'=>$time
        ];
        //添加点赞记录并返回记录id
         $ret = Db::transaction(function() use($vcdata, $vid){
            	Db::name('video_good_log')->insertGetId($vcdata);
   				DB::query("update ms_video set good=good+1 where id=$vid");
         });
        if(!$ret){
        	die(json_encode(['resultCode' => 0,'message' => '点赞视频成功','data' => $ret]));
        }else{
        	die(json_encode(['resultCode'=>6010,'error'=>$this->err['6010']]));
        }
       
    }
    /*
     * 取消视频点赞
     */
    public function cancelcollection(Request $request){
		if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid=$this->member_id;//$request->post('uid');   //用户id
        $vid=$request->post('vid');   //视频id
        if(empty($uid) || empty($vid)){
        	die(json_encode(['resultCode' => 6007, 'error' => $this->err['6007']]));
        }
        $vcount=Db::name('video')->where(['id'=>$vid])->count(); //检查视频是否存在
        if($vcount<=0){
        	die(json_encode(['resultCode' => 6008, 'error' => $this->err['6008']]));
        }
        $ucount=Db::name('member')->where('id',$uid)->count();  //检查用户是否存在
        if($ucount<=0){
        	die(json_encode(['resultCode' => 6009, 'error' => $this->err['6009']]));
        }
        $time=time();
        $vcdata=[
        	'user_id'=>$uid,
        	'video_id'=>$vid,
        ];
        $ret = Db::transaction(function() use($vcdata, $vid,$uid){
                	Db::query("delete from ms_video_good_log where user_id=$uid and video_id=$vid");
   					DB::query("update ms_video set good=good-1 where id=$vid");
        });
        if(!$ret){
        	die(json_encode(['resultCode' => 0,'message' => '取消点赞成功','data' => $ret]));
        }else{
        	die(json_encode(['resultCode'=>6011,'error'=>$this->err['6011']]));
        }
       
	}
    /**
     * App视频上传
     * @param Request $request
     */
    public function upload(Request $request){
        $title = $request->post('title');
        $accept = $request->post('accept');
        $gold = $request->post('gold');
        $tags = $request->post('tags');
        $class = $request->post('class');
        $img = $request->file('fileimg');
        $video = $request->file('filevideo'); 
        
        unset($videoData);
        $videoData['title'] = removeXss($title);
        $videoData['accept'] = $accept;
        $rule = [
            //'title|视频标题' => 'require|min:8|max:20',
            'accept'=>'accepted'
        ];
        $message = [
            'title.require' => "标题不能为空",
            'title.min'  => '标题必须在8~20之间',  
            'title.max'  => '标题必须在8~20之间',
            'accept.accepted' => '请点击同意协议后再上传'
        ];
        $validateResult = $this->validate($videoData, $rule, $message);
        if ($validateResult !== true) {
            die(json_encode(['resultCode' => 6005,'error' => $validateResult]));
        }
        
        if(!$img||!$video) {
            die(json_encode(['resultCode' => 6003,'error' => $this->err['6003']]));
        }
        
        $errInfo = array();
        $imgInfo = array();
        $videoInfo = array();
        if($this->member_id){
            $movePath = ROOT_PATH . $this->resource_path . $this->member_id;
        } else{
            $movePath = ROOT_PATH . $this->resource_path;
        }
        if($img){
            unset($info);
            $info = $img->validate(['size'=>$this->video_cover_size,'ext'=>$this->video_cover_ext])->move($movePath);
            if($info){
                $imgInfo['ext'] = $info->getExtension();
                $imgInfo['saveName'] = $info->getSaveName();
                $imgInfo['fileName'] = $info->getFilename();
                $imgInfo['pathName'] = $info->getPathname();
            }else{
                $errInfo['imgErr'] = $img->getError();
            }
        }        
        
        if($video){
            unset($info);
            $info = $video->validate(['size'=>$this->video_default_size,'ext'=>$this->video_default_ext])->move($movePath);//需要同时更改php.ini的post_max_size = 30M 
            if($info){
                $videoInfo['ext'] = $info->getExtension();
                $videoInfo['saveName'] = $info->getSaveName();
                $videoInfo['fileName'] = $info->getFilename();
                $videoInfo['pathName'] = $info->getPathname();
                //生成预览
                /* if($gold                                        //需要金币
                        &&get_config('look_at_on')              //需要开放试看
                        &&get_config('look_at_measurement')=='2'//试看以秒为单位
                        &&intval(get_config('look_at_num'))<$this->video_length){//少于15秒试看
                    $preview = FFMpegUtil::gen_video_preview($movePath.DS.$videoInfo['saveName']);
                    $videoInfo['preview'] = str_replace($movePath.DS, '', $preview);
                } */
                
            }else{
                $errInfo['videoErr'] = $video->getError();
            }
        }
        
        if(!empty($errInfo)){
            die(json_encode(['resultCode' => 6004,'error' => $errInfo]));
        }
        
        unset($videoData['accept']);
        if($gold) $videoData['gold'] = $gold;
        if($tags) $videoData['tag'] = $tags;//implode(",", $tags);
        else $videoData['tag'] = 0;
        if($class) $videoData['class'] = $class;
        else $videoData['class'] = 0;
        $videoData['url'] = $videoInfo['saveName'];
        $videoData['preview'] = isset($videoInfo['preview'])?$videoInfo['preview']:null;
        $videoData['thumbnail'] = $imgInfo['saveName'];
        $videoData['add_time'] = time();
        $videoData['update_time'] = time();
        $videoData['user_id'] = $this->member_id;
        $videoData['is_check'] =  (get_config('resource_examine_on')  == 1) ?  0 : 1;        
        
        $vid = Db::name('video')->insertGetId($videoData);
        
        if($vid){
            
            //视频图片上传ftp
            /* if(file_exists($imgInfo['pathName'])){
                Jobs::actionWithFtpUploadJob(['path'=>$imgInfo['pathName']]);
            }
            if(file_exists($videoInfo['pathName'])){
                $preview = false;
                //生成预览
                if($gold                                        //需要金币
                     &&get_config('look_at_on')              //需要开放试看
                     &&get_config('look_at_measurement')=='2'//试看以秒为单位
                     &&intval(get_config('look_at_num'))<$this->video_length){//少于15秒试看
                    $preview = true;
                 }
                 Jobs::actionWithFtpUploadJob(['path'=>$videoInfo['pathName'],'preview'=>$preview]);
            } */
            //视频预览
            if($gold                                        //需要金币
                &&get_config('look_at_on')              //需要开放试看
                &&get_config('look_at_measurement')=='2'//试看以秒为单位
                &&intval(get_config('look_at_num'))<$this->video_length){//少于15秒试看                                        
                    $data = [
                        'p'=>str_replace(ROOT_PATH.'public'.DS.'uploads'.DS, '', $videoInfo['pathName']),
                        'v'=>$vid,
                        's'=>get_config('look_at_num'),
                        'callbackurl'=>$this->httpType.$_SERVER['HTTP_HOST'].'/Callbackservice/preivewnotifyurl',//回调url
                        'callbackmethod'=>'POST'//回调方法，暂时只有POST和GET
                    ];
                    $result = json_decode(curl(config('common_service.url').config('common_service.preview_api'), $data, 'POST'));
                    //dump($result);
            }            
            
            die(json_encode(['resultCode' => 0,'message' => "视频上传成功",'data' => ['vid'=>$vid]]));
        }else{
            die(json_encode(['resultCode' => 6006,'error' => $this->err['6006']]));
        }        
    }
    
    /**
     *我购买的视频 
     */
    public function mybuyvideos(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        $uid=$this->member_id;
        
        unset($join);
        $join['table'] = 'video_watch_log vwl';
        $join['on'] = 'vwl.video_id = v.id';
        $join['type'] = 'RIGHT';
        
        unset($param);
        $param['where'] = ['vwl.user_id'=>$uid];
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'vwl.view_time desc';
        $param['join']  = $join;
        
        $videos=$this->fetchVideos($param);
        
        if(!empty($videos['videos'])){
        	foreach($videos['videos'] as $k=>$val){
				$videos['videos'][$k]['isgood']=DB::name('video_good_log')->where(['user_id'=>$this->member_id,'video_id'=>$val['id']])->count();        		
        	}
        }else{
        	$videos['videos']=array();
        }
        die(json_encode(['resultCode' => 0,'message' => '获取我的购买视频成功','data' => $videos]));
    }
    
    /**
          * 我的视频
     * @param Request $request
     */
    public function myvideos(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        $uid  = $request->post('uid')?$request->post('uid'):$this->member_id;
        unset($param);
        $param['where'] = ['user_id'=>$uid];
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'add_time desc';
        if($uid==$this->member_id){
        	$param['ismy']=1;
        }
        $videos=$this->fetchVideos($param);
        if(!empty($videos['videos'])){
        	foreach($videos['videos'] as $k=>$val){
				$videos['videos'][$k]['isgood']=DB::name('video_good_log')->where(['user_id'=>$this->member_id,'video_id'=>$val['id']])->count();        		
        	}
        }else{
        	$videos['videos']=array();
        }
        die(json_encode(['resultCode' => 0,'message' => "获取我的上传成功",'data' => $videos]));
        
        /* $videos = Db::name('video')
                    ->field('id, title, url, thumbnail, add_time, good, click, tag, status, hint, is_check')
                    ->where(['user_id'=>$this->member_id])->order('add_time desc')
                    ->paginate(['page'=>$page, 'list_rows'=>$rows]);
        $returnData = array();
        $returnData['currentPage'] = $videos->currentPage();
        $returnData['total'] = $videos->total();
        $returnData['videos'] = array();
        foreach($videos->items() as &$v){
            $v['url'] = $this->httpType.$_SERVER['HTTP_HOST']."/".str_replace('\\','/',$this->resource_path).str_replace('\\','/',$v['url']);
            $v['thumbnail'] = $this->httpType.$_SERVER['HTTP_HOST']."/".str_replace('\\','/',$this->resource_path).str_replace('\\','/',$v['thumbnail']);
            array_push($returnData['videos'], $v);
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取我的上传成功",'data' => $returnData])); */
    }
    
    /**
         * 最新上传
     * @param Request $request
     */
    public function latestvideos(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($param);
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'add_time desc';
        
        die(json_encode(['resultCode' => 0,'message' => "获取最新上传成功",'data' => $this->fetchVideos($param)]));
        
    }
    
    /**
         * 金币专区
     * @param Request $request
     */
    public function payvideos(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($param);
        $param['where'] = ['gold'=>['>',0]];
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'add_time desc';
        
        die(json_encode(['resultCode' => 0,'message' => "获取金币专区成功",'data' => $this->fetchVideos($param)]));
    }
    
    /**
        * 最多播放
     * @param Request $request
     */
    public function playmostvideos(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($param);
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'click desc, add_time desc';
        
        die(json_encode(['resultCode' => 0,'message' => "获取最多播放成功",'data' => $this->fetchVideos($param)]));
        
    }
    
    /**
        * 最多评论
     * @param Request $request
     */
    public function commentmostvideos(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;  
        
        unset($param);        
        $param['order'] = 'add_time desc';
        
        $comments = Db::name('comment')->field('resources_id, count(id) as num')->group('resources_id')->order('num desc')->select();
        //dump($comments);
        if(!empty($comments)){
            $total = count($comments);
            $vids = array();
            foreach($comments as $v){
                array_push($vids, $v['resources_id']);
            }
            $param['where'] = ['v.id'=>['IN', $vids]];
            $param['pager'] = array('page'=>$page, 'rows'=>$rows, 'simple'=>$total);
        }else{
            $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取最多评论成功",'data' => $this->fetchVideos($param)]));
    }
    
    /**
        * 最多点赞
     * @param Request $request
     */
    public function likemostvideos(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($param);
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'good desc, add_time desc';
        
        die(json_encode(['resultCode' => 0,'message' => "获取最多点赞成功",'data' => $this->fetchVideos($param)]));
        
    }
    
    /**
     * 关注人视频
     * @param Request $request
     */
    public function concernvideos(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($where);
        $where['uid'] = $this->member_id;
        
        $cusers = Db::name('member_collection')->field('cid')->where($where)->group('cid')->select();
        
        $returnData = array();
        if($cusers){
            $cids = array();
            foreach($cusers as $v){
                array_push($cids, $v['cid']);
            }
            $param['where'] = ['v.user_id'=>['IN', $cids]];
            $param['pager'] = array('page'=>$page, 'rows'=>$rows);
            $param['order'] = 'add_time desc';
            
            $returnData = $this->fetchVideos($param);
        }
        if(!$returnData){
        	$returnData['videos']=array();
        }
        die(json_encode(['resultCode' => 0,'message' => "获取关注人视频成功",'data' => $returnData]));
    }
    
    /**
     * 获取标签
     * @param Request $request
     * @return mixed
     */
    public function gettags(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $resourceType = $request->post('rt/s', '1');
        $tid = $request->post('tid/d', '');
        
        unset($where);
        if($tid) $where['id'] = $tid;
        $where['type'] = $resourceType;
        $where['status'] = 1;
        $tags = Db::name('tag')->field('id as tid, name')->where($where)->order('sort asc')->select();
        
        die(json_encode(['resultCode' => 0,'message' => "获取标签成功",'data' => $tags]));
    }
    
    /**
     * 获取分类
     * @param Request $request
     * @return mixed
     */
    public function getclasses(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $resourceType = $request->post('rt/s', '1');
        $pid = $request->post('cid/d', 0);
        
        unset($where);
        $where['pid'] = $pid;
        $where['type'] = $resourceType;
        $where['status'] = 1;
        $classlist = Db::name('class')->field('id as cid, name')->where($where)->order('sort asc')->select();
        foreach ($classlist as $k=>$v){
            $classlist[$k]['childs'] = Db::name('class')->field('id as cid, name')->where(['pid'=>$v['cid']])->select();
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取分类成功",'data' => $classlist]));
    }
    private function fetchVideos($param = null){       
        
        $returnData = $videos = array();
        $currentPage=1;
        $total=0;
        
        $query = new Query();
        
        $query->name('video')->alias('v')
                ->field('v.id as id, v.title, v.url, v.thumbnail, v.preview, v.add_time, v.good, v.gold, v.click, v.tag, v.status, v.hint, v.is_check, v.user_id, m.username, m.nickname, m.headimgurl')
                ->join('member m','v.user_id = m.id','LEFT');
        
        if(isset($param['join'])&&!empty($param['join'])){
            $query->join($param['join']['table'],$param['join']['on'],$param['join']['type']);
        }
        
        if(get_config('resource_examine_on')){
        	if(!isset($param['ismy'])&&empty($param['ismy'])){
        		 $query->where(['is_check'=>1]);
        	}
        }
                
        if(isset($param['where'])&&!empty($param['where'])){
            $query->where($param['where']);
        }
        
        if(isset($param['taglist'])&&!empty($param['taglist'])){
        	foreach($param['taglist'] as $v){
        		$query->whereOr(['v.tag'=>['like','%'.$v['id'].'%']]);
        	}
        }
        
        if(isset($param['order'])&&!empty($param['order'])){
            $query->order($param['order']);
        }
        
        if(isset($param['pager'])&&!empty($param['pager'])){
            $videosList = $query->paginate(['page'=>$param['pager']['page'], 'list_rows'=>$param['pager']['rows']],isset($param['pager']['simple'])?$param['pager']['simple']:false);
            $videos = $videosList->items();
            $currentPage = $videosList->currentPage();
            $total = $videosList->total();
            //$query->page($param['pager']['page'], $param['pager']['rows']);
        }else{
            $videos = $query->select();
            $total = count($videos);
        }
        //dump($query->getLastSql());
        
        $returnData['currentPage'] = $currentPage;
        $returnData['total'] = $total;
        $returnData['videos'] = array();
        foreach($videos as &$v){
            $v['url'] = $this->getFullResourcePath($v['url'],$v['user_id']);//$this->httpType.$_SERVER['HTTP_HOST']."/uploads/".str_replace('\\','/',$v['url']);
            $v['preview'] = $this->getFullResourcePath($v['preview'],$v['user_id']);
            $v['thumbnail'] = $this->getFullResourcePath($v['thumbnail'],$v['user_id']);//$this->httpType.$_SERVER['HTTP_HOST']."/uploads/".str_replace('\\','/',$v['thumbnail']);
            if($v['user_id']===0){
                $v['username'] = $v['nickname'] = $this->default_offical_name;
                $v['headimgurl'] = $this->getDefaultUserAvater(true);//$this->httpType.$_SERVER['HTTP_HOST'].$this->default_app_avatar;
            }else{
                if(!$v['headimgurl']) $v['headimgurl'] = $this->getDefaultUserAvater();
                else $v['headimgurl'] = $this->getFullResourcePath($v['headimgurl'], $v['user_id']);//$this->httpType.$_SERVER['HTTP_HOST'].str_replace('\\','/',$v['headimgurl']);
                if(!$v['username']) $v['username'] = $this->default_user_name;
                if(!$v['nickname']) $v['nickname'] = $v['username'];
            }          
            
             $v['add_time']=date('Y-m-d',$v['add_time']);
			$taglist=explode(',',$v['tag']);
			$v['tags']=Db::name('tag')->field('id,name')->where(['id'=>['IN',$taglist]])->select();
            
            $v['comment'] = Db::name('comment')->where(['resources_type'=>1,'resources_id'=>$v['id']])->count('id');
            
            if($v['gold']){//需要金币观看
                $isbuy = Db::name('video_watch_log')->where(['video_id'=>$v['id'],'user_id'=>$this->member_id])->count('id');//是否已经购买
                
                if(!$isbuy&&$v['user_id']!=$this->member_id){//没有购买并且不是作者，则只能看预览
                    $v['url'] = '';
                }
            }            
            
            array_push($returnData['videos'], $v);
        }
        
        return $returnData;
    }
    
    
}