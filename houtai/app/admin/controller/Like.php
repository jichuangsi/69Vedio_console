<?php

namespace app\admin\controller;

use think\Db;
use think\Request;
/**
 * 后台喜欢管理
 * @time 2019-12-17
 */
class Like extends Admin
{


    /**
     *  whs 20171113
     *  视频列表
     */

    function lists(Request $request){
        $videodb=$this->myDb->name('video');
        $class=$this->myDb->name('class');
        $member=$this->myDb->name('member');
         $select=$request->get('select/d',1);
         $key=$request->get('key/s','');
         $cla=$request->get('class/d',0);
         $pid=$request->param("pid");
         $where=" 1=1 and video.is_check=1 ";
         if(!empty($pid)){
             $where.=" and video.pid={$pid} ";
             $this->assign('pid',$pid);
             $ptl='gather_lists';
         }else{
             $where.=" and video.type=0 ";
             $ptl='lists';
         }
         if($key!='0' && !empty($key) && $key!=''){
             switch ($select){
                 case 1:
                     $where.=" and video.id='{$key}'";
                     break;
                 case 2:
                     $where.=" and video.title like '%{$key}%' ";
                     break;
                 case 3:
                     $where.=" and member.nickname like  '%{$key}%'";
                     break;
                 default :
                     $where.=" and 1=1";
                     break;
             }
         }
        if($cla!=0){
             $classid=$class->where(['pid'=>$cla])->field('id')->select();
             $id=null;
             foreach ($classid as $k=>$v){
                 $id.=$v['id'].',';
             }
            $id=$id.$cla;
             $where.=" and (class={$cla} || class in({$id})) ";
        }
        $data_list=$this->myDb->view('video','id,title,info,key_word,update_time,click,good,thumbnail,user_id,status,is_check,sort,type,gold,recommend')
            ->view('class','name as class','video.class=class.id and class.type=1')
            ->view('video_good_log','add_time,id as vcid','video.id=video_good_log.video_id')
            ->view('member','username,nickname','video_good_log.user_id=member.id')
            ->where($where)->order("add_time",'desc')->cache(120)->paginate(null,false,['query'=>$request->get()]);

        $list=$data_list->toArray();
        $pages = $data_list->render();

        foreach ($list['data'] as $k=>$v){
            if($list['data'][$k]['user_id']==0){
                $list['data'][$k]['thumbnail'] = $this->getFronturl($list['data'][$k]['thumbnail']);
                $list['data'][$k]['user_id']='admin';
                
            }else{
            	$username=$member->where(['id'=>$list['data'][$k]['user_id']])->select();
            	$list['data'][$k]['thumbnail'] = $this->getFronturl($list['data'][$k]['thumbnail'],$list['data'][$k]['user_id']);
            	$list['data'][$k]['user_id']=$username[0]['nickname'];
            }
        }
        $classlist=$class->where(['type'=>1,'pid'=>0])->select();
        foreach ($classlist as $k=>$v){
            $classlist[$k]['childs']=$class->where(['pid'=>$v['id']])->select();
        }
        $this->assign('cla',$cla);
        $this->assign('keys',$key);
        $this->assign('select',$select);
        $this->assign('classlist',$classlist);
        $this->assign('pages', $pages);
        $this->assign('data_list',$list['data']);
        return $this->fetch($ptl);
    }
    
}