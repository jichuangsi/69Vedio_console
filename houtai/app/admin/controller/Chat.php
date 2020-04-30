<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/21
 * Time: 14:19
 */

namespace app\admin\controller;

use think\Db;
use think\Request;

class Chat extends  Admin
{

    /**
     * 聊天信息审核
     */
    public function chatcheck(Request $request){
        $chat=$this->myDb->name('chat');
        $smember=$this->myDb->name('member');
        $tmember=$this->myDb->name('member');
        $where=" 1=1 and c.status = 0";
        $data_list=$chat->alias('c')->field('c.id as cid,c.content as ccon,c.status as cstatus,c.add_time as ctime,s.username as susername,s.nickname as snickname,t.username as tusername,t.nickname as tnickname')
        			->join('member s','c.send_user = s.id','LEFT')
                    ->join("member t", "t.id = c.to_user", "LEFT")
        			->where($where)->order("c.add_time",'asc')->paginate();
        $list=$data_list->toArray();
        $pages = $data_list->render();
        $this->assign('pages', $pages);
        $this->assign('data_list',$list['data']);
        return $this->fetch();
    }

	//聊天信息列表
    public function lists(Request $request){
      	$chat=$this->myDb->name('chat');
      	$select=$request->get('select/d',1);
      	$select2=$request->get('select2/d',1);
        $key=$request->get('key/s','');
      	$where=" 1=1 and c.status != 0 ";
      	$user='s';
      	switch($select2){
      		case 1:
      			$user='s';
      			break;
      		case 2:
      			$user='t';
      			break;
      		default :
      			$user = 's';
      			break;
      	}
      	if($key!='0' && !empty($key) && $key!=''){
            switch ($select){
                case 1:
                    $where.=" and c.content like '%{$key}%'";
                    break;
                case 2:
                    $where.=" and $user.nickname like '%{$key}%'";
                    break;
                case 3:
                    $where.=" and $user.username like '%{$key}%'";
                    break;
                default :
                    $where.=" and 1=1";
                    break;
            }
        }
      	$data_list=$chat->alias('c')->field('c.id as cid,c.content as ccon,c.status as cstatus,c.add_time as ctime,c.last_time as cltime,s.username as susername,s.nickname as snickname,t.username as tusername,t.nickname as tnickname')
        			->join('member s','c.send_user = s.id','LEFT')
                    ->join("member t", "t.id = c.to_user", "LEFT")
        			->where($where)->order("c.add_time",'desc')->paginate();
        $list=$data_list->toArray();
        $pages = $data_list->render();
        
        $this->assign('pages',$pages);
		$this->assign('keys',$key);
        $this->assign('select',$select);
        $this->assign('select2',$select2);
        $this->assign('data_list',$list['data']);
        return $this->fetch();
    }
}