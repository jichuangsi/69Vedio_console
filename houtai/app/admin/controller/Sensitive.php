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

//敏感词
class Sensitive extends  Admin
{

	//敏感词列表
    public function lists(Request $request){
      	$sen=$this->myDb->name('sensitive');
      	
      	$data_list=$sen->alias('c')->field('*')->paginate();
        $list=$data_list->toArray();
        $pages = $data_list->render();
        
        $this->assign('pages',$pages);
        $this->assign('data_list',$list['data']);
        return $this->fetch();
    }
    /*
     * 添加敏感词
     */
    public function add(Request $request){
        if ($this->request->isPost()) {
            $sensitivedb=$this->myDb->name('sensitive');
            //视频资料
            $seninfo=$request->post('sensitive/a');
            //验证视频信息
            $rule =[
                'sensitive|敏感词'=>'require',
            ];
            $message=[
                'sensitive.require'=>"敏感词不能为空",
            ];
            $result=$this->validate($seninfo,$rule,$message);
            if($result !== true) {
                return $this->error($result);
            }
            $insert=$sensitivedb->insert($seninfo);
            if($insert){
                return $this->success('添加成功',url('sensitive/lists'));
            }
            return $this->error('哎呀，出错了！',url());
        }
        return $this->fetch();
    }
}