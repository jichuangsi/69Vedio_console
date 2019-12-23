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
        '9011' => '修改失败',
        '9012' => '参数缺少视频id',
        '9013' => '购买视频不存在',
        '9014' => '视频作者不用购买',
        '9015' => '该视频免费',
        '9016' => '已经购买过该视频',
        '9017' => '余额不够支付该视频所需金币',        
        '9018' => '购买视频失败',
        '9019' => '移除粉丝失败',
        '9020' => '用户为vip试看不限次',
        '9021' => '视频作者不统计试看次数',        
        '9022' => '试看次数已用完',
        '9023' => '会员观看视频失败',
        '9024' => '该视频不为免费',
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
        
        $noAuthAct = ['addconcern','delconcern','getfriends','getconcerns','getconcerneds','recommendconcerns','mylike','getmemberinfo','sharelink','editmemberinfo','buyvideo','tryandsee'];
        
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
            $v['isgood']=DB::name('video_good_log')->where(['user_id'=>$uid,'video_id'=>$v['id']])->count();
            $v['comment'] = Db::name('comment')->where(['resources_type'=>1,'resources_id'=>$v['id']])->count('id');       
            
            $taglist=explode(',',$v['tag']);
			$v['tags']=Db::name('tag')->field('id,name')->where(['id'=>['IN',$taglist]])->select();
            
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
       	$member[0]['year']=$this->datediffage($member[0]['birthday']);//用户岁数
       	$member[0]['fansnum']=Db::name('member_collection')->where(['cid'=>$uid])->count('id');//用户粉丝数
       	$member[0]['follownum']=Db::name('member_collection')->where(['uid'=>$uid])->count('id');//用户关注数
       	$member[0]['fabulous']=Db::name('video')->where('user_id',$uid)->sum('good');        //获取用户被点赞数
       	$member[0]['birthday']=date('Y-m-s',$member[0]['birthday']);//转换生日
       	//获取用户地区名
       	if($member[0]['region']!=0){
       		$distr=Db::name('district')->field('name')->where('id',$member[0]['region'])->select();
       		$member[0]['regionname']=$distr[0]['name'];
       	}else{
       		$member[0]['regionname']='中国';
       	}
        die(json_encode(['resultCode' => 0,'message' => '获取个人信息成功','data' => $member[0]]));
    }
    /*
     * 获取岁数
     */
    function datediffage($before) {
    	$after=time();
		 if ($before>$after) {
		  $b = getdate($after);
		  $a = getdate($before);
		 }
		 else {
		  $b = getdate($before);
		  $a = getdate($after);
		 }
		 $n = array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
		 $y=$m=$d=0;
		 if ($a['mday']>=$b['mday']) { //天相减为正
		  if ($a['mon']>=$b['mon']) {//月相减为正
		   $y=$a['year']-$b['year'];$m=$a['mon']-$b['mon'];
		  }
		  else { //月相减为负，借年
		   $y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12;
		  }
		  $d=$a['mday']-$b['mday'];
		 }
		 else {  //天相减为负，借月
		  if ($a['mon']==1) { //1月，借年
		   $y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12;$d=$a['mday']-$b['mday']+$n[12];
		  }
		  else {
		   if ($a['mon']==3) { //3月，判断闰年取得2月天数
		    $d=$a['mday']-$b['mday']+($a['year']%4==0?29:28);
		   }
		   else {
		    $d=$a['mday']-$b['mday']+$n[$a['mon']-1];
		   }
		   if ($a['mon']>=$b['mon']+1) { //借月后，月相减为正
		    $y=$a['year']-$b['year'];$m=$a['mon']-$b['mon']-1;
		   }
		   else { //借月后，月相减为负，借年
		    $y=$a['year']-$b['year']-1;$m=$a['mon']-$b['mon']+12-1;
		   }
		  }
		 }
		 return $y;
//		 return ($y==0?'':$y.'岁').($m==0?'':$m.'个月').($d==0?'':$d.'天');
	}
   /**
     * 编辑个人用户信息 
     */
    public function editmemberinfo(Request $request){
//  	if (strtoupper($request->method()) == "OPTIONS") {
//          return Response::create()->send();
//      }
        $uid   =  $this->member_id;
        $uname = $request->post('username');
        $nname = $request->post('nickname');
        $introduce = $request->post('introduce')?$request->post('introduce'):'';
        $sex = $request->post('sex')?$request->post('sex'):1;
        $birthday = $request->post('birthday')?strtotime($request->post('birthday')):time();
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
//      $where['c.status'] = 0;
        
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
//      $where['c.status'] = 0;
        
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
    /*
     * 移除粉丝
     */
    public function removeconcerneds(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid = $this->member_id;
        $cid = $request->post('cid')?$request->post('cid'):0;
        
        unset($cdata);
        $cdata['uid'] = $cid;
        $cdata['cid'] = $uid;
        $ret = Db::transaction(function() use($cdata){
                Db::name('member_collection')->where($cdata)->delete();
            });
        if(!$ret){
            die(json_encode(['resultCode' => 0,'message' => "移除粉丝成功",'data' => $ret]));
        }else{
            die(json_encode(['resultCode' => 9019,'error' => $this->err['9019']]));
        }
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
    
    /**
     * 代理分享链接
     * @param Request $request
     */
    public function sharelink(Request $request){
        
        $shareLink = $this->share_link_pattern.createUidCode($this->member_id);
        
        //dump($shareLink);
        
        die(json_encode(['resultCode' => 0,'message' => "生成代理分享链接成功",'data' => $shareLink]));
    }
    
    /**
     * 用户购买视频
     * @param Request $request
     * @return mixed
     */
    public function buyvideo(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $vid = $request->param('vid/d', '');
        
        if(!$vid){
            die(json_encode(['resultCode' => 9012, 'error' => $this->err['9012']]));
        }
        
        $video = Db::name('video')->field('user_id,gold,url')->where(['id'=>$vid])->find();        
        if(!$video){
            die(json_encode(['resultCode' => 9013, 'error' => $this->err['9013']]));
        }
        
        if($video['user_id']==$this->member_id){
            die(json_encode(['resultCode' => 9014, 'error' => $this->err['9014']]));
        }
        
        if($video['gold']===0){
            die(json_encode(['resultCode' => 9015, 'error' => $this->err['9015']]));
        }
        
        $isbuy = Db::name('video_watch_log')->where(['video_id'=>$vid, 'user_id'=>$this->member_id])->count('id');        
        if($isbuy){
            die(json_encode(['resultCode' => 9016, 'error' => $this->err['9016']]));
        }
        
        $canbuy = Db::name('member')->field('id')->where(['id'=>$this->member_id,'money'=>['>=',$video['gold']]])->find();        
        if(!$canbuy){
            die(json_encode(['resultCode' => 9017, 'error' => $this->err['9017']]));
        }
        
        unset($data);
        $data['video_id'] = $vid;
        $data['user_id'] = $this->member_id;
        $data['user_ip'] = $request->ip();
        $data['gold'] = $video['gold'];
        $data['view_time'] = time();
        
        $ret = Db::transaction(function() use($data){            
            Db::name('video_watch_log')->insertGetId($data);
            Db::name('member')->where(['id'=>$data['user_id']])->setDec('money', $data['gold']);
        });
        
        if(!$ret){
            die(json_encode(['resultCode' => 0,'message' => "购买视频成功",'data' => $this->getFullResourcePath($video['url'],$video['user_id'])]));
        }else{
            die(json_encode(['resultCode' => 9018,'error' => $this->err['9018']]));
        }
    }
    
    /**
     * 会员观看视频记录
     * @param Request $request
     * @return mixed
     */
    public function tryandsee(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $vid = $request->param('vid/d', '');
        
        if(!$vid){
            die(json_encode(['resultCode' => 9012, 'error' => $this->err['9012']]));
        }
        
        $user = Db::name('member')->field('gid,try_and_see')->where(['id'=>$this->member_id])->find();
        if($user['try_and_see']===0){
            die(json_encode(['resultCode' => 9022, 'error' => $this->err['9022']]));
        }
        
        $video = Db::name('video')->field('user_id,gold')->where(['id'=>$vid])->find();
        if(!$video){
            die(json_encode(['resultCode' => 9013, 'error' => $this->err['9013']]));
        }
        
        if($video['user_id']==$this->member_id){
            die(json_encode(['resultCode' => 9021, 'error' => $this->err['9021']]));
        }
        
        unset($data);
        $data['video_id'] = $vid;
        $data['user_id'] = $this->member_id;
        $data['user_ip'] = $request->ip();
        $data['try_time'] = time();
        $gid = $user['gid'];
        $isbuy = false;
        if($video['gold']>0){
            $isbuy = Db::name('video_watch_log')->where(['video_id'=>$vid, 'user_id'=>$this->member_id])->count('id');            
        }
        
        $ret = Db::transaction(function() use($data, $gid, $isbuy){
            Db::name('video_try_log')->insertGetId($data);
            Db::name('video')->where(['id'=>$data['video_id']])->setInc('click');        
            if($gid===1&&!$isbuy){
                Db::name('member')->where(['id'=>$data['user_id']])->setDec('try_and_see');                
            }            
        });
            
        if(!$ret){
            die(json_encode(['resultCode' => 0,'message' => "会员观看视频成功",'data' => $ret]));
        }else{
            die(json_encode(['resultCode' => 9023,'error' => $this->err['9023']]));
        }
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