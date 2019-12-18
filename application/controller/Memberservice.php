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
        
        $noAuthAct = ['addconcern','delconcern','getfriends','getconcerns','getconcerneds','recommendconcerns'];
        
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
            die(json_encode(['resultCode' => 9004, 'error' => $this->err['9004']]));
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
            die(json_encode(['resultCode' => 9004, 'error' => $this->err['9004']]));
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
        
        die(json_encode(['resultCode' => 0,'message' => "获取关注列表成功",'data' => $this->fetchMembers($param)]));
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
        
        die(json_encode(['resultCode' => 0,'message' => "获取粉丝列表成功",'data' => $this->fetchMembers($param)]));
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
        
        die(json_encode(['resultCode' => 0,'message' => "获取推荐关注成功",'data' => $returnData]));
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