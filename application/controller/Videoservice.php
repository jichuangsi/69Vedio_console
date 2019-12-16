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

class Videoservice extends Controller
{
    private $err = [
        '6001' => '请求方式错误',
        '6002' => '请求接口不存在',
        '6003' => '视频上传异常',
        '6004' => '视频上传失败',
        '6005' => '视频校验错误',
        '6006' => '视频保存失败'
    ];
    
    private $member_id;
    
    private $resource_path;
    
    private $listRows = 6;
    
    private $httpType;
    
    private $authHeaders = ['multipart/form-data'];
    
    private $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    private $default_user_avatar = '/tpl/default/app/static/images/logo.png';
    
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
        $this->resource_path = 'public' . DS . 'uploads' . DS;
        $this->httpType = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        
        header('Access-Control-Allow-Origin: *');
        if(!empty($request->header('Content-Type'))&&in_array(strtolower($request->header('Content-Type')), $this->authHeaders)){
            header('Access-Control-Allow-Credentials: true');
        }        
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Max-Age: 1728000');
        
        $noAuthAct = ['upload','myvideos','latestvideos','payvideos','playmostvideos','likemostvideos','commentmostvideos','gettags','getclasses']; 
        
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
        $videoData['title'] = $title;
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
            $info = $img->validate(['size'=>1048576,'ext'=>'jpg,png,gif'])->move($movePath);
            if($info){
                $imgInfo['ext'] = $info->getExtension();
                $imgInfo['saveName'] = $info->getSaveName();
                $imgInfo['fileName'] = $info->getFilename();
            }else{
                $errInfo['imgErr'] = $img->getError();
            }
        }        
        
        if($video){
            unset($info);
            $info = $video->validate(['size'=>31457280,'ext'=>'mp4'])->move($movePath);//需要同时更改php.ini的post_max_size = 30M 
            if($info){
                $videoInfo['ext'] = $info->getExtension();
                $videoInfo['saveName'] = $info->getSaveName();
                $videoInfo['fileName'] = $info->getFilename();
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
        $videoData['thumbnail'] = $imgInfo['saveName'];
        $videoData['add_time'] = time();
        $videoData['update_time'] = time();
        $videoData['user_id'] = 0;//$this->member_id;
        $videoData['is_check'] =  (get_config('resource_examine_on')  == 1) ?  0 : 1;        
        
        $vid = Db::name('video')->insertGetId($videoData);
        
        if($vid){
            die(json_encode(['resultCode' => 0,'message' => "视频上传成功",'data' => ['vid'=>$vid]]));
        }else{
            die(json_encode(['resultCode' => 6006,'error' => $this->err['6006']]));
        }        
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
        
        unset($param);
        $param['where'] = ['user_id'=>$this->member_id];
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $param['order'] = 'add_time desc';
        
        die(json_encode(['resultCode' => 0,'message' => "获取我的上传成功",'data' => $this->fetchVideos($param)]));
        
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
            $param['where'] = ['id'=>['IN', $vids]];
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
                ->field('v.id as id, v.title, v.url, v.thumbnail, v.add_time, v.good, v.gold, v.click, v.tag, v.status, v.hint, v.is_check, v.user_id, m.username, m.headimgurl')
                ->join('member m','v.user_id = m.id','LEFT');
        
        if(isset($param['where'])&&!empty($param['where'])){
            $query->where($param['where']);
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
            $total = $query->count();
        }
        //dump($query->getLastSql());
        
        $returnData['currentPage'] = $currentPage;
        $returnData['total'] = $total;
        $returnData['videos'] = array();
        foreach($videos as &$v){
            $v['url'] = $this->httpType.$_SERVER['HTTP_HOST']."/uploads/".str_replace('\\','/',$v['url']);
            $v['thumbnail'] = $this->httpType.$_SERVER['HTTP_HOST']."/uploads/".str_replace('\\','/',$v['thumbnail']);
            if($v['user_id']===0){
                $v['username'] = '69官方';
                $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_app_avatar;
            }else{
                if(!$v['headimgurl']) $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].$this->default_user_avatar;
                else $v['headimgurl'] = $this->httpType.$_SERVER['HTTP_HOST'].str_replace('\\','/',$v['headimgurl']);
                if(!$v['username']) $v['username'] = '未知用户';
            }            
            
            array_push($returnData['videos'], $v);
        }
        
        return $returnData;
    }
    
    
}