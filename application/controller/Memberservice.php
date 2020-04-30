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
        '9025' => '缺少参数',
        '9026' => '修改卡包信息失败',
        '9027' => '以前观看过的视频不添加次数',
        '9028' => '缺少图片',
        '9029' => '上传相册图片失败',
        '9030' => '缺少接收者id',
        '9031' => '接收者id不存在',
        '9032' => '添加聊天信息失败',
    ];
    
    /* private $member_id;
    
    private $resource_path;
    
    private $listRows = 6;
    
    private $httpType;
    
    private $default_app_avatar = '/tpl/default/app/static/images/logo.png';
    
    private $default_user_avatar = '/tpl/default/app/static/images/user.png';
    
    private $authHeaders = ['multipart/form-data']; */
    
    private $share_link_pattern;
    
    private $share_video_link;
    
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
        
        $this->share_video_link = $this->httpType.$_SERVER['HTTP_HOST']."/app/videoshare/";
        
        $noAuthAct = ['addconcern','delconcern','getfriends','getconcerns','getconcerneds','recommendconcerns','mylike','getmemberinfo','sharelink','editmemberinfo','buyvideo','tryandsee','getcard','editcard',
        'uploadimg','getmyimg','usersearch','userchat','getchat','works','getchatlist'];
        
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
    
    public function works(Request $request){
		if (strtoupper($request->method()) == "OPTIONS") {
	        return Response::create()->send();
	    }
       $words=array('我','你','他','是','神','吗','个','啊','个','事','放','改个','我是');
	   $content="违禁词孤苦伶仃改个三姑婆奥德赛看姑婆撒旦未入网叫撒都发松岛枫没多少佛阿斯顿山东if阿斯顿们山东方面的撒对卷发iof将的撒圣诞节佛山大";
	   $banned=$this->generateRegularExpression($words);
	  //检查违禁词
	   $res_banned=$this->check_words($banned,$content);
	   if(count($res_banned)>0){
	   		die(json_encode(['resultCode'=>0,'message' => '有检查敏感词','data' => $res_banned]));
	   }else{
	   		die(json_encode(['resultCode'=>0,'message' => '没有检查敏感词','data' => $res_banned]));
	   }
	    die(json_encode(['resultCode'=>0,'message' => '检查敏感词','data' => $res_banned]));
    }
    /*
     * 获取聊天列表
     */
    public function getchatlist(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid=$this->member_id;
        $query = new Query();
//      $sts=$query->query("select * from ms_chat a where 30>(select count(*) from ms_chat b where b.send_user=".$uid." or b.to_user=".$uid.") order by a.add_time desc");
        $chatlist=Db::name('chat')->where('send_user',$this->member_id)->whereOr('to_user',$this->member_id)->field('*')->group('send_user,to_user')->order('add_time desc')->select();
        $test=array();
        foreach($chatlist as $k=>$v){
        	$user;
        	$isfor=false;
        	if($k>0){
        		foreach($test as $kk=>$vv){
        			if($vv['send_user']==$v['to_user'] && $vv['to_user']==$v['send_user']){
        				if($v['add_time']>$vv['add_time']){
        					die(json_encode(['resultCode'=>0,'message' => '获取聊天列表成功','data' => 123]));
        					$chatlist[$kk]['add_time']=date('Y-m-d H:i:s',$v['add_time']);	
        				}
        				array_splice($chatlist,$k,1);
        				$isfor=true;
        			}
        		}
        	}
        	if($isfor){
        		continue;
        	}
        	$test[$k]['send_user']=$v['send_user'];
        	$test[$k]['to_user']=$v['to_user'];
        	$test[$k]['content']=$v['content'];
        	$test[$k]['add_time']=$v['add_time'];
        	
        	if($v['send_user']==$this->member_id){
        		$user=Db::name('member')->where('id',$v['to_user'])->field('id,nickname,headimgurl')->find();
        	}else{
        		$user=Db::name('member')->where('id',$v['send_user'])->field('id,nickname,headimgurl')->find();
        	}
        	if(empty($user)){
        		array_splice($chatlist,$k,1);
        	}
        	$chatlist[$k]['userid']=$user['id'];
        	$chatlist[$k]['nickname']=$user['nickname'];
        	$chatlist[$k]['headimgurl']=$this->getFullResourcePath($user['headimgurl'], $user['id']);;
        	$chatlist[$k]['add_time']=date('Y-m-d H:i:s',$v['add_time']);
        }
        $returnData['chatlist']=$chatlist;
        die(json_encode(['resultCode'=>0,'message' => '获取聊天列表成功','data' => $returnData]));
    }
    /**
     *获取聊天信息 
     */
    public function getchat(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $to_user=$request->post('tu');//聊天用户id
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        if(!empty($to_user) && $to_user>0){
        	$ismem = Db::name('member')->where(['id'=>$to_user])->count('id');
        	if($ismem==0){
        		die(json_encode(['resultCode'=>9031,'error' => $this->err['9031']]));
        	}
        }else{
        	die(json_encode(['resultCode'=>9030,'error' => $this->err['9030']]));
        }
        $returnData = $chats = array();
        $currentPage=1;
        $total=0;
        $query = new Query();
        $chatList=$query->name('chat')->field('*')->whereOr(['to_user'=>$to_user,'send_user'=>$to_user])->order('add_time desc')->paginate(['page'=>$page, 'list_rows'=>$rows],false);
        $chats=$chatList->items();
//  	dump($query->getLastSql());
    	$returnData['currentPage'] = $chatList->currentPage();
        $returnData['total'] = $chatList->total();
        $returnData['chats'] = array();
        foreach($chats as &$v){
        	$v['add_time']=date('Y-m-d H:i:s',$v['add_time']);
//      	if($v['status']!=1){
//      		continue;
//      	}
        	array_push($returnData['chats'], $v);
        }
        die(json_encode(['resultCode'=>0,'message' => '获取聊天信息成功','data' => $returnData]));
    }
    /*
     * 发送聊天信息
     */
    public function userchat(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $send_user=$request->post('su')?$request->post('su'):$this->member_id;//发送者id
        $to_user=$request->post('tu');//接收者id
        $c=$request->post('c')?$request->post('c'):'';//聊天内容
        if(!empty($to_user) && $to_user>0){
        	$ismem = Db::name('member')->where(['id'=>$to_user])->count('id');
        	if($ismem==0){
        		die(json_encode(['resultCode'=>9031,'error' => $this->err['9031']]));
        	}
        }else{
        	die(json_encode(['resultCode'=>9030,'error' => $this->err['9030']]));
        }
        unset($data);
        $data['send_user']=$send_user;
        $data['to_user']=$to_user;
        $data['add_time']=time();
        $data['content']=$c;
        $ret=Db::name('chat')->insertGetId($data);
        if($ret>0){
        	die(json_encode(['resultCode'=>0,'message' => '发送信息成功','data' => $ret]));
        }else{
        	die(json_encode(['resultCode' => 9032,'error' => $this->err['9032']]));
        }
    }
    /*
     * 搜索名称和账号
     */
    public function usersearch(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $returnData = $users = array();
        $currentPage=1;
        $total=0;
        $u=$request->post('u');
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        $query = new Query();
        $userList=$query->name('member')->field('id,gid,username,tel,nickname,headimgurl,is_permanent,sex')->whereOr(['username'=>['like','%'.$u.'%'],'nickname'=>['like','%'.$u.'%']])->paginate(['page'=>$page, 'list_rows'=>$rows],false);
    	$users=$userList->items();
//  	dump($query->getLastSql());
    	$returnData['currentPage'] = $userList->currentPage();
        $returnData['total'] = $userList->total();
        $returnData['users'] = array();
        foreach($users as $k=>$v){
        	$v['headimgurl'] = $this->getFullResourcePath($v['headimgurl'], $v['id']);
        	$v['concerned']=$this->checkisfollow($this->member_id,$v['id']);
        	array_push($returnData['users'], $v);
        }
       die(json_encode(['resultCode'=>0,'message' => '搜索账号和昵称成功' ,'data' => $returnData])); 
    }
    /*
     * 获取卡包信息(支付宝、银行卡)
     */
    public function getcard(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid=$this->member_id;
        $zinfo=Db::name('draw_money_account')->field('*')->where(['user_id'=>$uid,'type'=>1])->find();//支付宝信息
        $cinfo=Db::name('draw_money_account')->field('*')->where(['user_id'=>$uid,'type'=>2])->find();//银行卡信息
        unset($data);
        $data['zhifubao'] = $zinfo;
        $data['card'] = $cinfo;
        die(json_encode(['resultCode'=>0,'message' => '获取卡包信息成功' ,'data' => $data]));
    }
    
    /*
     * 修改卡包信息
     */
    public function editcard(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $account=$request->post('account');
        $account_name=$request->post('aname');
        $bank=$request->post('bank');
        $type=$request->post('type');
        if(empty($account) || empty($account_name) || empty($type)){
        	die(json_encode(['resultCode'=>9025,'error' => $this->err['9025']]));
        }
        $title;
        switch($type){
        	case 1:
        		$title='支付宝'.$account;
        		break;
        	case 2:
        		$title='银行卡'.$account;
        		break;
        }
        unset($data);
        $data['account']=$account;
        $data['account_name']=$account_name;
        $data['bank']=$bank;
        $data['type']=$type;
        $data['title']=$title;
        $result=Db::name('draw_money_account')->where(['type'=>$type,'user_id'=>$this->member_id])->update($data);
        if($result>0){
        	die(json_encode(['resultCode' => 0,'message' => '修改成功' ,'data' => $result]));
        }else{
        	die(json_encode(['resultCode'=>9026,'error' => $this->err['9026']]));
        }
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
        	$v['sharevideourl']=$this->share_video_link.'u/'.createUidCode($this->member_id).'/v/'.$v['id'];
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
        $member[0]['headimgurl']=$member[0]['headimgurl']?$this->getFullResourcePath($member[0]['headimgurl'], $member[0]['id']):$this->getDefaultUserAvater();
       	$member[0]['concerned']=$this->checkisfollow($this->member_id,$uid);//判断是否已经关注
       	$member[0]['year']=$this->datediffage($member[0]['birthday']);//用户岁数
       	$member[0]['fansnum']=Db::name('member_collection')->where(['cid'=>$uid])->count('id');//用户粉丝数
       	$member[0]['follownum']=Db::name('member_collection')->where(['uid'=>$uid])->count('id');//用户关注数
       	$member[0]['fabulous']=Db::name('video')->where('user_id',$uid)->sum('good');        //获取用户被点赞数
       	$member[0]['birthday']=date('Y-m-d',$member[0]['birthday']);//转换生日
       	//获取用户地区名
       	if($member[0]['region']!=0){
       		$distr=Db::name('district')->field('name')->where('id',$member[0]['region'])->select();
       		$member[0]['regionname']=$distr[0]['name'];
       	}else{
       		$member[0]['regionname']='中国';
       	}
       	$time=time();
       	//判断是不是vip用户(1是；0否)
       	if($member[0]['out_time']>$time || $member[0]['is_permanent']==1){
       		$member[0]['isvip']=1;
       		if($member[0]['is_permanent']==1){
       			$member[0]['vipinfo']='永久会员';
       		}else{
       			$member[0]['vipinfo']=date('Y-m-d',$member[0]['out_time']);
       		}
       	}else{
       		$member[0]['isvip']=0;
       		$member[0]['vipinfo']='';
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
	 *获取个人相册图片 
	 */
	public function getmyimg(Request $request){
		if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $uid=$request->post('uid')?$request->post('uid'):$this->member_id;
        
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->listRows;
        
        $returnData = $imgs= array();
        
        $query = new Query();
        $imgList=$query->name('atlas')->field('*')->where('user_id',$uid)->paginate(['page'=>$page, 'list_rows'=>$rows],false);
		$imgs=$imgList->items();
		$returnData['currentPage'] = $imgList->currentPage();
        $returnData['total'] = $imgList->total();
        $returnData['imgs']=array();
        foreach($imgs as &$v){
        	$v['cover'] = $this->getFullResourcePath($v['cover'],$uid);
        	array_push($returnData['imgs'], $v);
        }
        die(json_encode(['resultCode' => 0,'message' => '获取个人图片成功','data' => $returnData]));
	}
	/**
     * 上传个人相册图片
     */
	public function uploadimg(Request $request){
		$img = $request->file('img');
		$imgInfo = array();
		$uid   =  $this->member_id;
        if($uid){
            $movePath = ROOT_PATH . $this->resource_path . $uid;
        } else{
            $movePath = ROOT_PATH . $this->resource_path;
        }
        if($img){
            unset($info);
            $info = $img->validate(['size'=>1048576*10,'ext'=>'jpg,png,gif,jpeg'])->move($movePath);
            if($info){
                $imgInfo['ext'] = $info->getExtension();
                $imgInfo['saveName'] = $info->getSaveName();
                $imgInfo['fileName'] = $info->getFilename();
            }else{
            	die(json_encode(['resultCode' => 9010,'error' => $img->getError()]));
            }
        }else{
        	die(json_encode(['resultCode' => 9028,'error' => $this->err['9028']]));
        } 
        unset($data);
       	$data=[
       	    'add_time' => time(),
       	    'update_time'=> time(),
       		'user_id'      => $uid,
       	];
       	if($img){
       		$data['cover']=$imgInfo['saveName'];
       	}
       	$aid = Db::name('atlas')->insertGetId($data);
       	if($aid){
       		die(json_encode(['resultCode' => 0,'message' => '上传图片成功','data' => $aid]));
       	}else{
       		die(json_encode(['resultCode' => 9029,'error' => $this->err['9029']]));
       	}
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
        $tel = $request->post('tel')?$request->post('tel'):'';
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
            $info = $img->validate(['size'=>1048576*5,'ext'=>'jpg,png,gif,jpeg'])->move($movePath);
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
       		'tel' => $tel,
       	];
       	if($img){
       		$update['headimgurl']=$imgInfo['saveName'];
       	}
       	if($imgInfo) $update['headimgurl']=$imgInfo['saveName'];
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
        $search=$request->post('s');
        unset($where);
        $where['c.uid'] = $this->member_id;
        $where['c.status'] = 1;
        $where['m.nickname'] = ['like','%'.$search.'%'];
        
        unset($join);
        $join['table'] = 'member m';
        $join['on'] = 'c.cid = m.id';
        $join['type'] = 'RIGHT';
        
        $param['join'] = $join;
        $param['where'] = $where;
        $param['order'] = 'm.username asc';
        
//      die(json_encode(['resultCode' => 0,'message' => "获取好友列表成功",'data' => $this->fetchMembers($param)]));
		die(json_encode(['resultCode' => 0,'message' => "获取好友列表成功",'data' => md5(123456)]));
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
        if($returnData!=null&& !empty($returnData)){
	        foreach($returnData['members'] as $k=>$v){
	        	//检查是否已经关注
		        $returnData['members'][$k]['concerned']=$this->checkisfollow($this->member_id,$v['id']);
	        }
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
        $time =time();
        $gold=$video['gold'];
        //计算分成后可以拿多少金币
        if(!empty(get_config('video_royalty')) && get_config('video_royalty')>0){
        	$gold=$video['gold']*get_config('video_royalty')/100;
        }
        $userinfo = Db::name('member')->field('pid')->where(['id'=>$this->member_id])->find();
        $pid = $userinfo['pid'];//代理商id
        $agentgold =0;//代理商收入
        
        unset($rdata);  //代理商金币记录
        if($pid>0){
        	if(!empty(get_config('video_royalty_agent')) && get_config('video_royalty_agent')>0){
        		$agentgold=$video['gold']*get_config('video_royalty_agent')/100;
        		$rdata['user_id'] = $pid;
		        $rdata['gold'] = $agentgold; 
		        $rdata['add_time'] = $time; 
		        $rdata['module'] = 'agent'; 
		        $rdata['rid'] = $vid; 
		        $rdata['agent_uid'] = $this->member_id;
		        $rdata['explain'] = '代理消费视频提成收入';
        	}
        }else{
        	$rdata=array();
        }
        
        unset($data);//视频购买记录
        $data['video_id'] = $vid;
        $data['user_id'] = $this->member_id;
        $data['user_ip'] = $request->ip();
        $data['gold'] = $video['gold'];
        $data['view_time'] = $time;
        
        unset($ugolddata); //购买者金币记录
        $ugolddata['user_id'] = $this->member_id;
        $ugolddata['gold'] = '-'.$video['gold']; 
        $ugolddata['add_time'] = $time; 
        $ugolddata['module'] = 'video'; 
        $ugolddata['rid'] = $vid; 
        $ugolddata['explain'] = '购买视频内容消费'; 
        
        unset($vgolddata);//视频发布者金币记录
        $vgolddata['user_id'] = $video['user_id'];
        $vgolddata['gold'] = $gold; 
        $vgolddata['add_time'] = $time; 
        $vgolddata['module'] = 'video'; 
        $ugolddata['rid'] = $vid; 
        $vgolddata['explain'] = '视频收入'; 
        
        
        $ret = Db::transaction(function() use($data,$gold,$video,$ugolddata,$vgolddata,$rdata,$agentgold,$pid){            
            Db::name('video_watch_log')->insertGetId($data);//视频购买记录
            Db::name('member')->where(['id'=>$data['user_id']])->setDec('money', $data['gold']);//购买会员减去金币
            Db::name('member')->where(['id'=>$video['user_id']])->setInc('money', $gold);  //发视频会员收入金币
            Db::name('gold_log')->insertGetId($ugolddata);
            Db::name('gold_log')->insertGetId($vgolddata);
            if($agentgold>0){
            	Db::name('gold_log')->insertGetId($rdata);
            	Db::name('member')->where(['id'=>$pid])->setInc('money', $agentgold); //代理商收入金币
            }
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
        
        $video = Db::name('video')->field('user_id,gold')->where(['id'=>$vid])->find();
        if(!$video){
            die(json_encode(['resultCode' => 9013, 'error' => $this->err['9013']]));
        }
        $user = Db::name('member')->field('id,gid,try_and_see,is_permanent')->where(['id'=>$this->member_id])->find();
        
        $iswatch=Db::name('video_try_log')->where(['video_id'=>$vid, 'user_id'=>$this->member_id])->count('id');
        if($user['is_permanent']==1){
		        die(json_encode(['resultCode' => 0,'message' => "永久会员不统计次数",'data' => $user]));
    	}
        if($iswatch>0){
        	die(json_encode(['resultCode' => 0,'message' => "以前观看过的视频不统计次数",'data' => $iswatch]));
        }
        if($user['gid']===1 && $user['try_and_see']===0 && $video['user_id']!==$this->member_id){
            die(json_encode(['resultCode' => 9022, 'error' => $this->err['9022']]));
        }
        
        
        if($video['user_id']==$this->member_id){
            die(json_encode(['resultCode' => 9021, 'error' => $this->err['9021']]));
        }
        
        unset($data);
        $data['video_id'] = $vid;
        $data['user_id'] =$this->member_id;
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
    		return 1;   //相互关注
    	}else if($concern>0){
    		return 0;   //我关注了他
    	}else{
    		return null; //未关注
    	}
    }
    
    private function fetchMembers($param = null){
        
        $returnData = $members = array();
        $currentPage=1;
        $total=0;
        
        $query = new Query();
        
        $query->name('member_collection')->alias('c')->field('m.id,m.username,m.nickname,m.headimgurl,m.sex');
        
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