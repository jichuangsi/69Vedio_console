<?php

namespace app\admin\controller;

use think\Db;
use think\Request;

class Test extends Admin
{

    /**
     *  whs 20171113
     *  视频列表
     */

    function index(Request $request){
//  	return $this->error("ffffffff");
        $videodb=$this->myDb->name('video');
        $class=$this->myDb->name('class');
         $select=$request->get('select/d',1);
         $key=$request->get('key/s','');
         $cla=$request->get('class/d',0);
         $pid=$request->param("pid");
         $where=" 1=1 and video.is_check=1 ";
         if(!empty($pid)){
             $where.=" and video.pid={$pid} ";
             $this->assign('pid',$pid);
             $ptl='index';
         }else{
             $where.=" and video.type=0 ";
             $ptl='index';
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
                     $where.=" and video.key_word like  '%{$key}%'";
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
        //echo $where;die;
        //$data_list=$videodb->where($where)->order("id",'desc')->paginate(null,false,['query'=>$request->get()]);
        $data_list=$this->myDb->view('video','id,title,info,key_word,update_time,click,good,thumbnail,user_id,status,is_check,sort,type,gold,recommend')
            ->view('class','name as class','video.class=class.id and class.type=1')
            ->where($where)->order("id",'desc')->cache(120)->paginate(null,false,['query'=>$request->get()]);

        $list=$data_list->toArray();
        $pages = $data_list->render();

        foreach ($list['data'] as $k=>$v){
          //  $list['data'][$k]['class']=$this->GetClassname_ByClass($v['class'],1);
            if( $list['data'][$k]['user_id']==0){
                $list['data'][$k]['user_id']='admin';
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


    /**
     * whs 20171113
     * @ id 类id ,$type类型
     * 通过视频类id获取视频类型
     */
    function GetClassname_ByClass($id,$type){
        $class=$this->myDb->name('class');
        $classname=$class->where(['id'=>$id,'type'=>$type])->field('name')->find();
        if($classname){
            return $classname['name'];
        }else{
            return '未定义分类';
        }
    }
    /**
     * whs 20171113
     * @ id 类id ,$type类型
     * 通过视频类id获取视频类型
     */
    function randclick(Request $request){

        $callback = input('param.callback/s');
        if (!$callback) {
            echo '<br><br>callback为必传参数！';
            exit;
        }
        if($this->request->isPost()){
            $class=$request->post('class/d',0);
            $type=$request->post('type/d',2);
            $num=$request->post('num/d',0);
            $min_num=$request->post('min_num/d',0);
            $max_num=$request->post('max_num/d',100);

            $where=" 1=1";
          switch (intval($class)){
              case 0:
                $where.=" and 1=1";
                break;
              default :
                  $where.=" and class=".intval($class);
          }
            $video=$this->myDb->name('video');
           $videoinfo=$video->where($where)->field("id,click")->select();
           if($type==1){
               if(intval($min_num)==0){$min_num=0;};if(intval($max_num)==0){$max_num=100;};
               foreach ($videoinfo as $k=>$v){
                   $hits=mt_rand($min_num,$max_num);
                   $video->where("id={$v['id']}")->setInc('click',intval($hits));
               }
           }else{
               if(intval($num)==0){$num=0;};
               $video->where($where)->setInc('click',intval($num));
           }
            return $this->success('修改成功');
        }
        $class=$this->myDb->name('class');
        $classlist=$class->where(['type'=>1,'pid'=>0])->select();
        foreach ($classlist as $k=>$v){
            $classlist[$k]['childs']=$class->where(['pid'=>$v['id']])->select();
        }
        $this->assign('classlist',$classlist);
        $this->assign('callback', $callback);
        $this->view->engine->layout(false);
        return $this->fetch();
    }

    /* 视频预览 2018/01/16 $dreamer */
    function play(Request $request){
        $videoId=$request->param('id/d',0);
        if($videoId<=0) exit('<h2>视频ID不正确</h2>');

        $videoInfo=$this->myDb->name('video')->where("id={$videoId}")->find();
        if(!$videoInfo)  exit('<h2>视频信息不存在</h2>');

        $yzmPlayKey=$this->myDb->name('admin_config')->where("name='yzm_play_secretkey'")->find();
        $yzmPlayKey=(isset($yzmPlayKey['value']))?$yzmPlayKey['value']:'';
        $yzmPlayKey=create_yzm_play_sign($yzmPlayKey);
        $videoInfo['url'].="?sign={$yzmPlayKey}";

        $this->assign('videoInfo',$videoInfo);

        $this->view->engine->layout(false);
        return $this->fetch();

    }

    /**
     * 删除视频标签(为了兼容权限控制)
     * $dreamer 1/25
     */
    public function deleteTag(){
        return  $this->khdel();
    }

    /**
     * 删除分类(为了兼容权限控制)
     * $dreamer 1/25
     */
    public function deleteClass(){
        return  $this->khdel('1');die;
    }
    
    /**
     *修改是否推荐状态 
     * @author frs whs tcl dreamer ©2016
     * @return mixed
     */
    public function khrecommend() {
        $val   = input('param.val');
        $ids   = input('param.ids/a') ? input('param.ids/a') : input('param.id/a');
        $table = input('param.table');
        $field = input('param.field', 'recommend');
        if (empty($ids)) {
            return $this->error('参数传递错误[1]！');
        }
        if (empty($table)) {
            return $this->error('参数传递错误[2]！');
        }
        // 以下表操作需排除值为1的数据
        if ($table == 'admin_menu' || $table == 'admin_user' || $table == 'admin_role' || $table == 'admin_module') {
            if (in_array('1', $ids) || ($table == 'admin_menu' && in_array('2', $ids))) {
                return $this->error('系统限制操作');
            }
        }
        // 获取主键
        $pk = $this->myDb->name($table)->getPk();
        $map = [];
        $map[$pk] = ['in', $ids];

        $res = $this->myDb->name($table)->where($map)->setField($field, $val);
        if ($res === false) {
            return $this->error('状态设置失败');
        }
        return $this->success('状态设置成功');
    }
    
}