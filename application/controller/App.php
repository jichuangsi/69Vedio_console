<?php
/**
 * App相关控制器
 * LastDate:    2019/12/10
 */

namespace app\controller;

use think\Controller;
use think\Exception;
use think\Request;
use think\Db;

class App extends Controller{
    
    public function download(Request $request){
        
        $uid = $request->param('u');        
        //$code = $request->param('c');
        //dump($uid);dump($code);exit;
        
        $this->assign('u', $uid);
        //$this->assign('c', $code);
        return view();
    }
    
    public function getApp(Request $request){
        $did = $request->param('d');
        $isios = $request->param('i');
        
        $isExist = Db::name('devices')->where(['id'=>$did])->count();
        $ret = '';
        if($isExist){
            $ret = Db::name('devices')->where(['id'=>$did])->update(['download_time'=>time()]);
        }
        
        if($ret||$isExist){
            $path = ROOT_PATH.'apk/';
            //$file_name = request()->param("filename");
            $file_name = "69Video.apk";     //下载文件名
            if($isios=="ios"){
            	$file_name = "69VideoIOS.ipa";
            }
            //中文需要转码
            $fileAdd = iconv('UTF-8', 'GB2312', $path . $file_name);
            //检查文件是否存在
            if (!file_exists($fileAdd) || !explode(".apk", $fileAdd) || !is_file($fileAdd)  || !explode(".ipa", $fileAdd)) {
                return $this->error('应用下载异常！');
            } else {
                //告诉浏览器这是一个文件流格式的文件(app)
                Header("Content-type: application/vnd.android.package-archive");
                //用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
                header('Content-disposition: attachment; filename=' . iconv('UTF-8', 'GB2312', $file_name)); //文件名
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
                //这里会告诉请求方,文件大小
                header('Content-Length: ' . filesize($fileAdd)); //告诉浏览器，文件大小
                //读取文件内容并直接输出到浏览器
                @readfile($fileAdd);
                return $this->success('应用下载成功',url('app/download'));
            }
        }else
            return $this->error('应用下载异常！');
    }
    
}
