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
        '9003' => '参数缺少被关注人id',
        '9004' => '不能对自己关注或取消关注',
        '9005' => '关注用户失败',
        '9006' => '已经关注过该用户',
        '9007' => '取消关注用户失败',
        '9008' => '指定用户不存在',
        '9009' => '该账号已存在',
        '9010' => '图片上传失败',
        '9011' => '修改失败'
    ];
    
    /* private $member_id;
    
    private $resource_path;
    
    private $listRows = 6;
    
    private $httpType;
    
    private $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    private $default_user_avatar = '/tpl/default/app/static/images/user.png';
    
    private $authHeaders = ['multipart/form-data']; */
    
    private $share_link_pattern;
    
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
        
        $this->share_link_pattern = $this->httpType.$_SERVER['HTTP_HOST']."/share/";
        
        $noAuthAct = ['addconcern','delconcern','getfriends','getconcerns','getconcerneds','recommendconcerns','mylike','getmemberinfo','sharelink','editmemberinfo'];
        
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
    
     /*
     * 用户的喜欢视频列表
     */
    public function mylike(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $returnData = $videos = array();
        $currentPage=1;
        $total=0;
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        $uid=$request->post('uid')?$request->post('uid'):$this->member_id;
        
        unset($param);
        $param['pager'] = array('page'=>$page, 'rows'=>$rows);
        $query = new Query();
        $query->name('video_good_log')->alias('vc')
                ->field('vc.id as vcid,v.id as id, v.title, v.url, v.thumbnail, v.add_time, v.good, v.gold, v.click, v.tag, v.status, v.hint, v.is_check, v.user_id, m.username, m.headimgurl')
                ->join('member m','vc.user_id = m.id')
        		->join('video v','vc.video_id = v.id');
//      		$this->member_id
        $query->where(['vc.user_id'=>$uid]);
        $query->order('add_time desc');		
       	if(isset($param['pager'])&&!empty($param['pager'])){
            $videosList = $query->paginate(['page'=>$param['pager']['page'], 'list_rows'=>$param['pager']['rows']],isset($param['pager']['simple'])?$param['pager']['simple']:false);
            $videos = $videosList->items();
            $currentPage = $videosList->currentPage();
            $total = $videosList->total();
            $pages=(int)$page;
        }else{
            $videos = $query->select();
            $total = $query->count();
        }
        $returnData['currentPage'] = $currentPage;
        $returnData['total'] = $total;
        $returnData['videos'] = array();
        foreach($videos as &$v){
            $v['url'] = $this->getFullResourcePath($v['url'],$v['user_id']);//$this->httpType.$_SERVER['HTTP_HOST']."/uploads/".str_replace('\\','/',$v['url']);
            $v['thumbnail'] = $this->getFullResourcePath($v['thumbnail'],$v['user_id']);//$this->httpType.$_SERVER['HTTP_HOST']."/uploads/".str_replace('\\','/',$v['thumbnail']);
            if($v['user_id']===0){
                $v['username'] = '69官方';
                $v['headimgurl'] = $this->getDefaultUserAvater(true);//$this->httpType.$_SERVER['HTTP_HOST'].$this->default_app_avatar;
            }else{
                if(!$v['headimgurl']) $v['headimgurl'] = $this->getDefaultUserAvater();
                else $v['headimgurl'] = $this->getFullResourcePath($v['headimgurl'], $v['user_id']);//$this->httpType.$_SERVER['HTTP_HOST'].str_replace('\\','/',$v['headimgurl']);
                if(!$v['username']) $v['username'] = '未知用户';
            }     
            $v['isgood']=DB::name('video_collection')->where(['user_id'=>$uid,'video_id'=>$v['id']])->count();       
            array_push($returnData['videos'], $v);
        }
        die(json_encode(['resultCode' => 0,'message' => '获取喜欢视频列表成功','data' => $returnData]));
    }
    
    /*
     * 获取用户个人信息
     */
    public function getmemberinfo(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid=$request->post('uid')?$request->post('uid'):$this->member_id;
        $member=Db::name('member')->field('*')->where('id',$uid)->select();
        $member[0]['headimgurl']?$this->getFullResourcePath($member[0]['headimgurl'], $member[0]['id']):$this->getDefaultUserAvater();
       	$member[0]['concerned']=$this->checkisfollow($this->member_id,$uid);//判断是否已经关注
        die(json_encode(['resultCode' => 0,'message' => '获取个人信息成功','data' => $member[0]]));
    }
    /**
     * 编辑个人用户信息 
     */
    public function editmemberinfo(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid   = $this->member_id;
        $uname = $request->post('username');
        $nname = $request->post('nickname');
        $introduce = $request->post('introduce')?$request->post('introduce'):'';
        $sex = $request->post('sex')?$request->post('sex'):1;
        $birthday = $request->post('birthday')?strtotime($request->post('birthday')):0;
        $img = $request->file('fileimg');
        $region = $request->post('region')?$request->post('region'):0; 
         //判断账号是否已存在
        if($uname){
        	$isuname=Db::name('member')->where(['username' => $uname,'id'=>['<>',$uid]])->count();
        	if($isuname>0) die(json_encode(['resultCode'=>9009,'error' => $this->err['9009']]));
        }
        
         $imgInfo = array();
        if($uid){
            $movePath = ROOT_PATH . $this->resource_path . $uid;
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
            	die(json_encode(['resultCode' => 9010,'error' => $img->getError()]));
            }
        } 
        unset($update);
       	$update=[
       	    'username' => removeXss($uname),
       	    'nickname' => removeXss($nname),
       	    'introduce'=> removeXss($introduce),
       		'sex'      => $sex,
       		'birthday' => $birthday,
       	];
       	if($img){
       		$update['headimgurl']=$imgInfo['saveName'];
       	}
       	if($imgInfo) $update['headimgurl'];
		 $mresult=Db::name('member')->where('id',$uid)->update($update);
		 if($mresult>0){
		 	die(json_encode(['resultCode' => 0,'message' => '个人信息修改成功','data' => $mresult]));
		 }
		 die(json_encode(['resultCode'=>9011,'error' => $this->err['9011']]));      
    }
    /**
     * 关注用户
     * @param Request $request
     * @return mixed
     */
    public function addconcern(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $cid = $request->param('cid/d','');
        
        if(!$cid){
            die(json_encode(['resultCode' => 9003, 'error' => $this->err['9003']]));
        }
        
        if($cid==$this->member_id){
            die(json_encode(['resultCode' => 9004, 'error' => $this->err['9004']]));
        }
        
        $ucount=Db::name('member')->where('id',$cid)->count();  //检查用户是否存在
        if($ucount<=0){
            die(json_encode(['resultCode' => 9008, 'error' => $this->err['9008']]));
        }
        
        //检查是否已经关注
        unset($data);
        $data['uid'] = $this->member_id;
        $data['cid'] = $cid;   
        
        $concern = Db::name('member_collection')->where($data)->count('id');
        
        //检查是否已经被关注
        unset($where);
        $where['uid'] = $cid;
        $where['cid'] = $this->member_id;
        
        $concerned = Db::name('member_collection')->where($where)->count('id');
        
        if($concerned>0){
            $data['status'] = 1;
        }     
        
        if($concern>0){
            if($concerned>0){
                $ret = Db::transaction(function() use($data, $where){
                    unset($data['status']);
                    Db::name('member_collection')->where($data)->update(['status'=>1]);
                    Db::name('member_collection')->where($where)->update(['status'=>1]);
                });
            }else{
                die(json_encode(['resultCode' => 9006,'error' => $this->err['9006']]));
            }
        }else{
            $ret = Db::transaction(function() use($data, $where){
                $data['collection_time'] = time();
                Db::name('member_collection')->insertGetId($data);
                Db::name('member_collection')->where($where)->update(['status'=>1]);
            });
        }        
        
        if(!$ret){
            die(json_encode(['resultCode' => 0,'message' => "关注用户成功",'data' => $ret]));
        }else{
            die(json_encode(['resultCode' => 9005,'error' => $this->err['9005']]));
        }
    }
    
    /**
     * 取消关注用户
     * @param Request $request
     * @return mixed
     */
    public function delconcern(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $cid = $request->param('cid/d','');
        
        if(!$cid){
            die(json_encode(['resultCode' => 9003, 'error' => $this->err['9003']]));
        }
        
        if($cid==$this->member_id){
            die(json_encode(['resultCode' => 9004, 'error' => $this->err['9004']]));
        }
        
        $ucount=Db::name('member')->where('id',$cid)->count();  //检查用户是否存在
        if($ucount<=0){
            die(json_encode(['resultCode' => 9008, 'error' => $this->err['9008']]));
        }
        
        //检查是否已经关注
        unset($data);
        $data['uid'] = $this->member_id;
        $data['cid'] = $cid;
        
        $concern = Db::name('member_collection')->where($data)->count('id');
        
        //检查是否已经被关注
        unset($where);
        $where['uid'] = $cid;
        $where['cid'] = $this->member_id;
        
        $concerned = Db::name('member_collection')->where($where)->count('id');
        
        if($concern&&$concerned){
            $ret = Db::transaction(function() use($data, $where){
                Db::name('member_collection')->where($data)->delete();
                Db::name('member_collection')->where($where)->update(['status'=>0]);
            });
        }else if(!$concern&&$concerned){
            $ret = Db::transaction(function() use($where){
                Db::name('member_collection')->where($where)->update(['status'=>0]);
            });
        }else if($concern&&!$concerned){
        	$ret = Db::transaction(function() use($data){
                Db::name('member_collection')->where($data)->delete();
            });
        }
        
        if(!$ret){
            die(json_encode(['resultCode' => 0,'message' => "取消关注用户成功",'data' => $ret]));
        }else{
            die(json_encode(['resultCode' => 9007,'error' => $this->err['9007']]));
        }
    }
    
    /**
     * 获取好友列表
     * @param Request $request
     * @return mixed
     */
    public function getfriends(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        unset($where);
        $where['c.uid'] = $this->member_id;
        $where['c.status'] = 1;
        
        unset($join);
        $join['table'] = 'member m';
        $join['on'] = 'c.cid = m.id';
        $join['type'] = 'RIGHT';
        
        $param['join'] = $join;
        $param['where'] = $where;
        $param['order'] = 'm.username asc';
        
        die(json_encode(['resultCode' => 0,'message' => "获取好友列表成功",'data' => $this->fetchMembers($param)]));
    }
    
    /**
     * 获取关注列表
     * @param Request $request
     */
    public function getconcerns(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        unset($where);
        $where['c.uid'] = $this->member_id;
        $where['c.status'] = 0;
        
        unset($join);
        $join['table'] = 'member m';
        $join['on'] = 'c.cid = m.id';
        $join['type'] = 'RIGHT';
        
        $param['join'] = $join;
        $param['where'] = $where;
        $param['order'] = 'm.username asc';
        //获取关注状态
        $concenrns=$this->fetchMembers($param);
        foreach($concenrns['members'] as $k=>$v){
        	$concenrns['members'][$k]['concerned']=$this->checkisfollow($this->member_id,$v['id']);
        }
        die(json_encode(['resultCode' => 0,'message' => "获取关注列表成功",'data' => $concenrns]));
    }
    
    /**
     * 获取粉丝列表
     * @param Request $request
     */
    public function getconcerneds(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        unset($where);
        $where['c.cid'] = $this->member_id;
        $where['c.status'] = 0;
        
        unset($join);
        $join['table'] = 'member m';
        $join['on'] = 'c.uid = m.id';
        $join['type'] = 'RIGHT';
        
        $param['join'] = $join;
        $param['where'] = $where;
        $param['order'] = 'm.username asc';
        //获取关注状态
        $concerneds=$this->fetchMembers($param);
        foreach($concerneds['members'] as $k=>$v){
        	$concerneds['members'][$k]['concerned']=$this->checkisfollow($this->member_id,$v['id']);
        }
        die(json_encode(['resultCode' => 0,'message' => "获取粉丝列表成功",'data' => $concerneds]));
    }
    
    /**
     * 推荐关注列表
     * @param Request $request
     */
    public function recommendconcerns(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        unset($map);
        $map['id'] = $this->member_id;
        $map['pid'] = ['<>', 0];
        
        $users = Db::name('member')->field('pid as uid')->where($map)
                ->union(function($query){
                    $query->name('member')->field('id as uid')->where(['pid'=>$this->member_id]);
                })->select();               
        //Db::name('member')->getLastSql();
        
        $returnData = array();
        if($users){
            $uids = array();
            foreach($users as $v){
                array_push($uids, $v['uid']);
            }
            
            unset($map);
            $map['uid'] = $this->member_id;            
            $cusers = Db::name('member_collection')->field('cid')->where($map)->select();
            $cids = array($this->member_id);
            foreach($cusers as $v){
                array_push($cids, $v['cid']);
            }
            
            unset($where);
            $where['c.uid'] = ['IN', $uids];
            $where['c.cid'] = ['NOT IN', $cids];
            
            unset($join);
            $join['table'] = 'member m';
            $join['on'] = 'c.cid = m.id';
            $join['type'] = 'RIGHT';
            
            $param['join'] = $join;
            $param['where'] = $where;
            $param['pager'] = array('page'=>$page, 'rows'=>$rows);
            $param['order'] = 'm.username asc';            
            
            $returnData = $this->fetchMembers($param);
        }
        foreach($returnData['members'] as $k=>$v){
        	//检查是否已经关注
	        $returnData['members'][$k]['concerned']=$this->checkisfollow($this->member_id,$v['id']);
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取推荐关注成功",'data' => $returnData]));
    }
    
    /*
     * 检查关注状态
     */
    private function checkisfollow($uid=null,$cid=null){
    	if(!$uid||!$cid){
    		return null;
    	}
    	unset($data);
    	$data['uid'] = $uid;
	    $data['cid'] = $cid;
    	$concern = Db::name('member_collection')->where($data)->count('id');
    	unset($where);
        $where['uid'] = $cid;
        $where['cid'] = $uid;
    	$concerned = Db::name('member_collection')->where($where)->count('id');
    	if($concern>0&&$concerned>0){
    		return 1;
    	}else if($concern>0){
    		return 0;
    	}else{
    		return null;
    	}
    }
    /**
     * 代理分享链接
     * @param Request $request
     */
    public function sharelink(Request $request){
        
        $shareLink = $this->share_link_pattern.createUidCode($this->member_id);
        
        //dump($shareLink);
        
        die(json_encode(['resultCode' => 0,'message' => "生成代理分享链接成功",'data' => $shareLink]));
    }
    
    private function fetchMembers($param = null){
        
        $returnData = $members = array();
        $currentPage=1;
        $total=0;
        
        $query = new Query();
        
        $query->name('member_collection')->alias('c')->field('m.id,m.username,m.headimgurl');
        
        if(isset($param['join'])&&!empty($param['join'])){
            $query->join($param['join']['table'],$param['join']['on'],$param['join']['type']);
        }
        
        if(isset($param['where'])&&!empty($param['where'])){
            $query->where($param['where']);
        }
        
        if(isset($param['order'])&&!empty($param['order'])){
            $query->order($param['order']);
        }
        
        if(isset($param['pager'])&&!empty($param['pager'])){
            $membersList = $query->paginate(['page'=>$param['pager']['page'], 'list_rows'=>$param['pager']['rows']],isset($param['pager']['simple'])?$param['pager']['simple']:false);
            $members = $membersList->items();
            $currentPage = $membersList->currentPage();
            $total = $membersList->total();
            //$query->page($param['pager']['page'], $param['pager']['rows']);
        }else{
            $members = $query->select();
            $total = count($members);
        }
        //dump($query->getLastSql());
        
        $returnData['currentPage'] = $currentPage;
        $returnData['total'] = $total;
        $returnData['members'] = array();
        foreach($members as &$v){
            if(!$v['username']) $v['username'] = '未知用户';
            $v['headimgurl'] = $v['headimgurl']?$this->getFullResourcePath($v['headimgurl'], $v['id']):$this->getDefaultUserAvater();
            
            array_push($returnData['members'], $v);
        }
        
        return $returnData;
    }
    
    
    
    
    
    
    
    
    
    
    
    
}