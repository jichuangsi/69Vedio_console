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

class Goldservice extends Baseservice
{
    private $err = [
        '20001' => '请求方式错误',
        '20002' => '请求接口不存在',
        '20003' => '缺少参数',
        '20004' => 'vip套餐不存在',
        '20005' => '会员账户余额不足',
        '20006' => '会员充值失败',
        '20007' => '提现申请失败',
        '20008' => '卡包信息不存在',
    ];
    
    private $Rows = 20;
    public function __construct(Request $request)
    {
        
        parent::__construct($request);
        
        $noAuthAct = ['getgoldlist','getviplist','getgoldrecord','getgolerate','viprecharge','cashwithdrawal','cashlist','rechargelist','getposter','test'];
        
        if (!in_array(strtolower($request->action()), $noAuthAct)) {
            if ($request->isPost() && $request->isAjax()) {
                
            } else {
                $returnData = ['statusCode' => 20001, 'error' => $this->err['20001']];
                die(json_encode($returnData));
            }
        }
    }
    public function _empty()
    {
        $returnData = ['statusCode' => 20002, 'error' => $this->err['20002']];
        die(json_encode($returnData));
    } 
    public function test(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $userlist=Db::name('member')->field('id')->where(['gid'=>1])->select();
        foreach($userlist as $k=>$v){
        	$pcount=Db::name('member')->where(['pid'=>$v['id']])->count();
        	if($pcount<2){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile')]);
        	}else if($pcount<5){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile2')]);
        	}else if($pcount<10){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile3')]);
        	}else if($pcount<20){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile4')]);
        	}else if($pcount>=20){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile5')]);
        	}
        }
        die(json_encode(["resultCode" => 0,'message' => '获取广告列表成功','data' => $userlist]));
    }
    /*
     * 获取广告位的广告信息
     */
    public function getposter(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $aid=$request->post('aid');//广告位id
        $pdata=Db::name('advertisement')->field('*')->where(['position_id'=>$aid,'status'=>1])->select();
        die(json_encode(["resultCode" => 0,'message' => '获取广告列表成功','data' => $pdata]));
    }
    /**
     * 获取金币套餐
     */
    public function getgoldlist(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $goldlist=Db::name('gold_package')->field('*')->select();
//      $goldlist['gold_rate']=get_config('gold_exchange_rate');
        die(json_encode(["resultCode" => 0,'message' => '获取金币套餐列表成功','data' => $goldlist]));
    }
    /*
     * 获取vip套餐
     */
    public function getviplist(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $viplist=Db::name('recharge_package')->field('*')->where('status',1)->order('sort desc')->select();
        die(json_encode(['resultCode' => 0,'message' => '获取vip套餐列表成功','data' => $viplist]));
    }
    /*
     * 获取金币汇率、最低提现、是否提现、提现手续费
     */
    public function getgolerate(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        unset($data);
        $data['goldrate']=get_config('gold_exchange_rate');//汇率
        $data['is_withdrawals']=get_config('is_withdrawals');//是否允许提现（1为支持，0为不支持）
        $data['min_withdrawals']=get_config('min_withdrawals');//提现最低额度
        $data['service_harge']=get_config('service_harge');//提现手续费
        
        die(json_encode(['resultCode' => 0,'message' => '获取金币汇率成功','data' =>$data ]));
    }
    /*
     * 获取用户消费流水
     */
    public function getgoldrecord(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $returnData = $record = array();
        $gstatus=$request->post('gstatus')?$request->post('gstatus'):0; //支出还是收入(1:支出，2:收入，0:全部)
        $gtype =$request->post('gtype')?$request->post('gtype'):'';    //流水类型
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->Rows;
        $query = new Query();
        
     	$where = array();
     	switch($gstatus){
     		case 1:
     			$where['gold'] = ['<',0];
     			break;
     		case 2:
     			$where['gold'] = ['>',0];	
     	}
     	if(!empty($gtype)){
     		$where['module'] = $gtype;
     	}
     	$where['user_id'] = $this->member_id;
     	$recordlist=$query->name('gold_log')->field('*')->where($where)->order('add_time desc')->paginate(['page'=>$page, 'list_rows'=>$rows],false);
     	$record=$recordlist->items();
     	foreach($record as $k=>$val){
     		if(!empty($val['add_time'])){
     			$record[$k]['add_time']=date('Y-m-d H:i:s',$val['add_time']);
     		}
     	}
     	$returnData['recordlist']=$record;
     	$returnData['currentPage']=$recordlist->currentPage();
     	$returnData['total']=$recordlist->total();
     	die(json_encode(['resultCode' => 0, 'message' => '获取消费记录列表成功' ,'data' => $returnData]));   
    }
    /*
     * vip充值
     */
    public function viprecharge(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $rid=$request->post('rid');
        if(!$rid) die(json_encode(['resultCode' => 20003,'error' => $this->err['20003']]));
        
        $rinfo=Db::name('recharge_package')->field('*')->where('id',$rid)->find();//vip套餐信息
        if(!$rinfo) die(json_encode(['resultCode' => 20004,'error' => $this->err['20004']]));
        
        $uinfo=Db::name('member')->field('*')->where('id',$this->member_id)->find();
        if($uinfo['money']<$rinfo['price']){
        	die(json_encode(['resultCode' => 20005,'error' => $this->err['20005']]));
        }
        $time=time();
        $uid=$this->member_id;
        $outtime=$rinfo['days']*24*60*60;//会员套餐期限时间戳
        unset($udata);//会员信息
        $udata['money']=$uinfo['money']-$rinfo['price'];
        $udata['gid']=2;
        if($rinfo['permanent']==1){
        	$udata['is_permanent'] = 1;
        }else{
        	if(!empty($uinfo['out_time']) && $uinfo['out_time']>$time){
        		$udata['out_time']=$uinfo['out_time']+$outtime;
        	}else{
        		$udata['out_time']=$time+$outtime;
        	}
        }
        
        unset($orderdata);//充值会员订单记录
        $orderdata['order_sn']=$time.rand(10000,99999);
        $orderdata['user_id']=$uid;
        $orderdata['price']=$rinfo['price'];
        $orderdata['real_pay_price']=$rinfo['price'];
        $orderdata['buy_type']=2;
        $orderdata['buy_vip_info']='{"id":'.$rinfo['id'].',"name":"'.$rinfo['name'].'","days":'.$rinfo['days'].',"price":"'.$rinfo['price'].'","permanent":'.$rinfo['permanent'].',"info":"'.$rinfo['info'].'"}';
        $orderdata['status']=1;
        if($uinfo['pid']>0){
        	$orderdata['from_agent_id']=$uinfo['pid'];
        }
        $orderdata['add_time']=$time;
        $orderdata['update_time']=$time;
        $orderdata['pay_time']=$time;
        
        unset($ugolddata);//会员金币记录
        $ugolddata['user_id'] = $this->member_id;
        $ugolddata['gold'] = '-'.$rinfo['price']; 
        $ugolddata['add_time'] = $time; 
        $ugolddata['module'] = 'vip'; 
        $ugolddata['explain'] = '充值会员消费';
        
        $pid = $uinfo['pid'];//代理商id
        $agentgold =0;//代理商收入
        unset($rdata);  //代理商金币记录
        if($pid>0){
        	if(!empty(get_config('vip_royalty')) && get_config('vip_royalty')>0){
        		$agentgold=$rinfo['price']*get_config('vip_royalty')/100;
        		$rdata['user_id'] = $pid;
		        $rdata['gold'] = $agentgold; 
		        $rdata['add_time'] = $time; 
		        $rdata['module'] = 'agent'; 
		        $rdata['agent_uid'] = $this->member_id;
		        $rdata['explain'] = '代理充值vip提成收入';
        	}
        }
        
        
        $ret = Db::transaction(function() use($udata,$uid,$ugolddata,$orderdata,$rdata,$agentgold,$pid){            
            Db::name('member')->where('id',$uid)->update($udata);
            $oid=Db::name('order')->insertGetId($orderdata);
            if($oid>0){
            	$ugolddata['rid']=$oid;
            }
            Db::name('gold_log')->insertGetId($ugolddata);
            if($agentgold>0){
            	Db::name('gold_log')->insertGetId($rdata);
            	Db::name('member')->where(['id'=>$pid])->setInc('money', $agentgold); //代理商收入金币
            }
        });
//		$query = new Query();	
//		$ss=$query->name('order')->insertGetId($orderdata);
//		dump($query->getLastSql());
		
        if(!$ret){
        	die(json_encode(['resultCode' => 0,'message' => "vip充值成功",'data' => $ret]));
        }else{
        	die(json_encode(['resultCode' => 20006,'error' => $this->err['20006']]));
        }
    }
    /*
     * 提现记录
     */
    public function cashlist(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $returnData=array();
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->Rows;
        $drinfolist=Db::name('draw_money_log')->field('id,gold,money,status,add_time')->where('user_id',$this->member_id)->order('add_time desc')->paginate(['page'=>$page, 'list_rows'=>$rows],false);;
        $drinfo=$drinfolist->items();
     	foreach($drinfo as $k=>$val){
 			if(!empty($val['add_time'])){
     			$drinfo[$k]['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
     		}
     	}
     	$returnData['drlist']=$drinfo;
     	$returnData['currentPage']=$drinfolist->currentPage();
     	$returnData['total']=$drinfolist->total();
        
        die(json_encode(['resultCode'=>0,'message' => '获取提现记录成功' ,'data' => $returnData]));
    }
    /*
     * 获取充值记录
     */
    public function rechargelist(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $returnData=array();
        $type = $request->post('type');
        $page = $request->post('page')?$request->post('page'):1;
        $rows = $request->post('rows')?$request->post('rows'):$this->Rows;
        unset($where);
        $where['user_id']=$this->member_id;
        if(!empty($type)){
        	$where['buy_type']=$type;
        }
        $orinfolist=Db::name('order')->field('*')->where($where)->order('add_time desc')->paginate(['page'=>$page, 'list_rows'=>$rows],false);;
        $orinfo=$orinfolist->items();
     	foreach($orinfo as $k=>$val){
 			if(!empty($val['add_time'])){
     			$orinfo[$k]['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
     			$orinfo[$k]['buy_vip_info'] = json_decode($orinfo[$k]['buy_vip_info']);
     		}
     	}
     	$returnData['drlist']=$orinfo;
     	$returnData['currentPage']=$orinfolist->currentPage();
     	$returnData['total']=$orinfolist->total();
        
        die(json_encode(['resultCode'=>0,'message' => '获取充值记录成功' ,'data' => $returnData]));
    }
    /*
     * 金币提现申请
     */
    public function cashwithdrawal(Request $request){
    	if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        $gold=$request->post('gold');  //金币
        $money=$request->post('money');  //现金金额
        $cid = $request->post('cid'); //提现账号信息id
        
        if(empty($gold) || empty($money) || empty($cid)){
        	die(json_encode(['resultCode' => 20003,'error' => $this->err['20003']]));
        }
        $drinfo=Db::name('draw_money_account')->field('*')->where('id',$cid)->find();
        if(empty($drinfo) || $drinfo ==null){
        	die(json_encode(['resultCode' => 20008,'error' => $this->err['20008']]));
        }
        $type=$drinfo['type'];    //账号类型(1；支付宝；2：银行卡)
        $name=$drinfo['account_name'];   //名称
        $account=$drinfo['account'];   //账号
        $bank=$drinfo['bank'];
        
        $uid=$this->member_id;
        $uinfo=Db::name('member')->field('*')->where('id',$this->member_id)->find();
        if($uinfo['money']<$money){
        	die(json_encode(['resultCode' => 20005,'error' => $this->err['20005']]));
        }
        $time=time();
       	unset($drdata);
       	$drdata['user_id'] = $this->member_id;
       	$drdata['gold'] = $gold;
       	$drdata['money'] = $money;
       	$drdata['add_time'] = $time;
       	$drdata['info'] = '{"user_id":'.$uid.',"title":null,"type":'.$type.',"account":"'.$account.'","account_name":"'.$name.'","bank":"'.$bank.'"}';
        
        unset($golddata);
        $golddata['user_id'] = $this->member_id;
        $golddata['gold'] = '-'.$gold; 
        $golddata['add_time'] = $time; 
        $golddata['module'] = 'draw_money'; 
        $golddata['explain'] = '申请提现';
        
        $ret = Db::transaction(function() use($drdata,$uid,$gold,$golddata){            
            $did=Db::name('draw_money_log')->insertGetId($drdata);
            if($did>0){
            	$golddata['rid']=$did;
            	Db::name('gold_log')->insertGetId($golddata);
            }
            Db::name('member')->where(['id'=>$uid])->setDec('money', $gold);
        });
        if(!$ret){
        	die(json_encode(['resultCode' => 0 ,'message' => '申请提现成功' , 'data' => $ret]));
        }else{
        	die(json_encode(['resultCode' => 20007,'error' => $this->err['20007']]));
        }
        
    }
}