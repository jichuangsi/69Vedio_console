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

class Commentservice extends Baseservice
{
    private $err = [
        '10001' => '请求方式错误',
        '10002' => '请求接口不存在',
        '10003' => '参数缺少资源id',
        '10004' => '指定资源不存在',
        '10005' => '参数缺少评论内容',        
        '10006' => '提交评论失败',
    ];
    
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
        
        $noAuthAct = ['getcomments','submitcomment','mycomments']; 
        
        if (!in_array(strtolower($request->action()), $noAuthAct)) {
            if ($request->isPost() && $request->isAjax()) {
                
            } else {
                $returnData = ['statusCode' => 10001, 'error' => $this->err['10001']];
                die(json_encode($returnData));
            }
        }
    }
    
    public function _empty()
    {
        $returnData = ['statusCode' => 10002, 'error' => $this->err['10002']];
        die(json_encode($returnData));
    }    
    
    /**
     * 获取视频评论列表
     * @param Request $request
     * @return mixed
     */
    public function getcomments(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $vid = $request->param('vid/d', '');//视频id
        $pid = $request->param('pid/d', 0);//父评论id
        $page = $request->param('page/d', 1);
        $rows = $request->param('rows/d', $this->listRows);
        
        //dump($vid);dump($pid);dump($page);dump($rows);
        
        if(!$vid){
            die(json_encode(['resultCode' => 10003, 'error' => $this->err['10003']]));
        }
        
        $vcount=Db::name('video')->where('id',$vid)->count();  //检查资源是否存在
        if($vcount<=0){
            die(json_encode(['resultCode' => 10004, 'error' => $this->err['10004']]));
        }
        
        unset($map);        
        $map['c.pid'] = $pid;
        $map['c.resources_id'] = $vid;
        if(get_config('comment_examine_on')){
            $map['c.status'] = 1;
        }
        $commentslist = Db::name('comment')->alias('c')->field('c.id as cid, c.content, c.add_time, 
                            s.id as sid, s.username as susername, s.nickname as snickname, s.headimgurl as sheadimgurl, 
                            t.id as tid, t.username as tusername, t.nickname as tnickname, t.headimgurl as theadimgurl')
                            ->join('member s','c.send_user = s.id','LEFT')
                            ->join("member t", "t.id = c.to_user", "LEFT")
                            ->where($map)->order("c.add_time DESC")
                            ->paginate(['page'=>$page, 'list_rows'=>$rows])
                            ;
        
       //dump($ret);
       
       $comments = $commentslist->items();
       $currentPage = $commentslist->currentPage();
       $total = $commentslist->total();
       
       $returnData['currentPage'] = $currentPage;
       $returnData['total'] = $total;
       $returnData['comments'] = array();
       
       foreach($comments as &$v){           
           if(!$v['susername']) $v['susername'] = $this->default_user_name;
           if(!$v['tusername']) $v['tusername'] = $this->default_user_name;
           if(!$v['snickname']) $v['snickname'] = $v['susername'];
           if(!$v['tnickname']) $v['tnickname'] = $v['tusername'];
           $v['sheadimgurl'] = $v['sheadimgurl']?$this->getFullResourcePath($v['sheadimgurl'], $v['sid']):$this->getDefaultUserAvater();
           $v['theadimgurl'] = $v['theadimgurl']?$this->getFullResourcePath($v['theadimgurl'], $v['tid']):$this->getDefaultUserAvater();           
           $v['add_time'] = uc_time_ago($v['add_time']);
           
           if($pid===0){//获取第一个子评论
               $count_children = Db::name('comment')->where(['pid'=>$v['cid']])->count('id');
               if($count_children){
                   unset($map);
                   if(get_config('comment_examine_on')){
                       $map['c.status'] = 1;
                   }
                   $map['c.pid'] = $v['cid'];
                   $map['c.resources_id'] = $vid;
                   $comment_first_child = Db::name('comment')->alias('c')->field('c.id as cid, c.content, c.add_time,
                                            s.id as sid, s.username as susername, s.nickname as snickname, s.headimgurl as sheadimgurl,
                                            t.id as tid, t.username as tusername, t.nickname as tnickname, t.headimgurl as theadimgurl')
                                            ->join('member s','c.send_user = s.id','LEFT')
                                            ->join("member t", "t.id = c.to_user", "LEFT")
                                            ->where($map)->order("c.add_time DESC")->limit(1)->select();
                    
                    $comment_first_child[0]['susername'] = $comment_first_child[0]['susername']?$comment_first_child[0]['susername']:$this->default_user_name;
                    $comment_first_child[0]['tusername'] = $comment_first_child[0]['tusername']?$comment_first_child[0]['tusername']:$this->default_user_name;
                    $comment_first_child[0]['snickname'] = $comment_first_child[0]['snickname']?$comment_first_child[0]['snickname']:$comment_first_child[0]['susername'];
                    $comment_first_child[0]['tnickname'] = $comment_first_child[0]['tnickname']?$comment_first_child[0]['tnickname']:$comment_first_child[0]['tusername'];
                    $comment_first_child[0]['sheadimgurl'] = $comment_first_child[0]['sheadimgurl']?$this->getFullResourcePath($comment_first_child[0]['sheadimgurl'], $comment_first_child[0]['sid']):$this->getDefaultUserAvater();
                    $comment_first_child[0]['theadimgurl'] = $comment_first_child[0]['theadimgurl']?$this->getFullResourcePath($comment_first_child[0]['theadimgurl'], $comment_first_child[0]['tid']):$this->getDefaultUserAvater();
                    $comment_first_child[0]['add_time'] = uc_time_ago($comment_first_child[0]['add_time']);
                    $v['children'] = $comment_first_child[0];
                    $v['crest'] = $count_children - 1;
               }
           }
           
           array_push($returnData['comments'], $v);
       }
       
       die(json_encode(['resultCode' => 0,'message' => "获取视频评论列表成功",'data' => $returnData]));
    }
    
    /**
     * 提交评论
     * @param Request $request
     * @return mixed
     */
    public function submitcomment(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $vid = $request->param('vid/d', '');//视频id
        $pid = $request->param('pid/d', 0);//父评论id
        $tid = $request->param('tid/d', 0);//接受人id
        $resources_type = $request->param('rt/d', 1);//资源类型
        $content = $request->param('content/s', '');//评论内容
        $sid = $this->member_id;//发送人id
        
        if(!$vid){
            die(json_encode(['resultCode' => 10003, 'error' => $this->err['10003']]));
        }
        
        if(!$content){
            die(json_encode(['resultCode' => 10005, 'error' => $this->err['10005']]));
        }
        
        $vcount=Db::name('video')->where('id',$vid)->count();  //检查资源是否存在
        if($vcount<=0){
            die(json_encode(['resultCode' => 10004, 'error' => $this->err['10004']]));
        }
        
        if(!$tid){
            $vuser = Db::name('video')->field('user_id')->where('id',$vid)->select();
            if($vuser){
                $tid=$vuser[0]['user_id'];
            }
        }
        
        unset($data);
        $data['send_user'] = $sid;
        $data['to_user'] = $tid;
        $data['content'] = removeXss($content);
        $data['resources_type'] = $resources_type;
        $data['resources_id'] = $vid;
        $data['status'] = (get_config('comment_examine_on')  == 1) ?  0 : 1;
        $data['add_time'] = time();
        $data['pid'] = $pid;
        
        $cid = Db::name('comment')->insertGetId($data);
        
        if($cid){
            die(json_encode(['resultCode' => 0,'message' => "提交评论成功",'data' => ['cid'=>$cid]]));
        }else{
            die(json_encode(['resultCode' => 10006,'error' => $this->err['10006']]));
        }
    }
    
    /**
     * 获取我的评论列表
     * @param Request $request
     * @return mixed
     */
    public function mycomments(Request $request){
        if (strtoupper($request->method()) == "OPTIONS") {
            return Response::create()->send();
        }
        
        $resources_type = $request->param('rt/d', 1);//资源类型
        $page = $request->param('page/d', 1);
        $rows = $request->param('rows/d', $this->listRows);
        
        unset($map);
        $map['v.user_id'] = $this->member_id;
        $map['resources_type'] = $resources_type;
        if(get_config('comment_examine_on')){
            $map['c.status'] = 1;
        }
        
        $comments = Db::name('comment')->alias("c")->field('c.id as cid')->join("video v", "v.id = c.resources_id", "RIGHT")->where($map)
                    ->union(function($query) use($resources_type) {
                        $query->name('comment')->field('id as cid')->where(['to_user'=>$this->member_id,'resources_type'=>$resources_type]);
                        if(get_config('comment_examine_on')){
                            $query->where(['status'=>1]);
                        }
                    })->select();
        /* echo Db::name('comment')->getLastSql();
        dump($comments); */
        
        $returnData = array();
        if($comments){
            $cids = array();
            foreach($comments as $v){
                array_push($cids, $v['cid']);
            }
            
            unset($map);
            $map['c.id'] = ['IN', $cids];
            
            $commentslist = Db::name('comment')->alias('c')->field('c.id as cid, c.content, c.add_time, c.resources_id as vid,
                                s.id as sid, s.username as susername, s.nickname as snickname, s.headimgurl as sheadimgurl,
                                t.id as tid, t.username as tusername, t.nickname as tnickname, t.headimgurl as theadimgurl,
                                v.user_id, v.thumbnail, v.url')
                                ->join("video v",'c.resources_id = v.id','RIGHT')
                                ->join('member s','c.send_user = s.id','LEFT')
                                ->join("member t", "t.id = c.to_user", "LEFT")                                
                                ->where($map)->order("c.add_time DESC")
                                ->paginate(['page'=>$page, 'list_rows'=>$rows])
                                ;
            
            //echo Db::name('comment')->getLastSql();
            
            $comments = $commentslist->items();
            $currentPage = $commentslist->currentPage();
            $total = $commentslist->total();
                                
            $returnData['currentPage'] = $currentPage;
            $returnData['total'] = $total;
            $returnData['comments'] = array();
            
            foreach($comments as &$v){
                if(!$v['susername']) $v['susername'] = $this->default_user_name;
                if(!$v['tusername']) $v['tusername'] = $this->default_user_name;
                if(!$v['snickname']) $v['snickname'] = $v['susername'];
                if(!$v['tnickname']) $v['tnickname'] = $v['tusername'];
                $v['sheadimgurl'] = $v['sheadimgurl']?$this->getFullResourcePath($v['sheadimgurl'], $v['sid']):$this->getDefaultUserAvater();
                $v['theadimgurl'] = $v['theadimgurl']?$this->getFullResourcePath($v['theadimgurl'], $v['tid']):$this->getDefaultUserAvater();
                $v['add_time'] = uc_time_ago($v['add_time']);
                $v['url'] = $this->getFullResourcePath($v['url'],$v['user_id']);
                $v['thumbnail'] = $this->getFullResourcePath($v['thumbnail'],$v['user_id']);
                $v['type'] = $v['user_id']==$this->member_id?1:2;//评论类型；1:评论自己作品，2:评论自己评论
                
                array_push($returnData['comments'], $v);
            }
        }
        
        die(json_encode(['resultCode' => 0,'message' => "获取我的评论列表成功",'data' => $returnData]));
    }
    
}