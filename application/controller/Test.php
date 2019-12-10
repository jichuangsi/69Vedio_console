<?php
/**
 * Api接口层
 * LastDate:    2017/11/27
 */

namespace app\controller;

use think\captcha\Captcha;
use phpmailer\SendEmail;
use think\Controller;
use think\Exception;
use think\Request;
use think\Db;
use systemPay\codePay;

use app\model\Order;


class Test extends Controller
{
    public function __construct(Request $request)
    {
        //$origin=$request->header('origin'); //"http://sp.msvodx.com"
        //$allowDomain=['msvodx.com','meisicms.com'];
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        
        /* $noAuthAct = ['getatlas', 'getcaptcha', 'givegood',
            'delcollection', 'control_imgs', 'collect_atlas',
            'is_login', 'rewardranking', 'getthemeinfos','createqrcode','demo'
        ];
        
        if (!in_array(strtolower($request->action()), $noAuthAct)) {
            if ($request->isPost() && $request->isAjax()) {
                
            } else {
                $returnData = ['statusCode' => '4001', 'error' => '请求方式错误'];
                die(json_encode($returnData));
            }
        } */
    }
    
    function index(){


    }
    
    public function fingerprint(Request $request){
        
        $code = $request->get('code');
        
        $this->assign('code', $code);
        return view();
    }
    
    public function download(Request $request){
        $code = $request->param('c');
        $fingerprint = $request->param('f');
        
        $fingerprintData = [
            'fingerprint'=>$fingerprint,
            'code'=>$code
        ];
        
        
        $isExist = Db::name('fingerprint')->where($fingerprintData)->count();
        $ret = '';
        if(!$isExist){
            $ret = Db::name('fingerprint')->insert($fingerprintData);
        }
        
        if($ret||$isExist){
            $path = ROOT_PATH.'apk/';
            //$file_name = request()->param("filename");
            $file_name = "app-debug.apk";     //下载文件名
            
            //中文需要转码
            $fileAdd = iconv('UTF-8', 'GB2312', $path . $file_name);
            //检查文件是否存在
            if (!file_exists($fileAdd) || !explode(".apks", $fileAdd) || !is_file($fileAdd)) {
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
                return $this->success('应用下载成功',url('test/fingerprint'));
            }
        }else
            return $this->error('应用下载异常！');
    }
    
    public function registering(Request $request){
        $device_uuid = $request->param('u');
        
        $this->assign('uuid', $device_uuid);
        
        return view();
    }
    
    public function binding(Request $request){
        $device_uuid = $request->post('u');
        $fingerprint = $request->post('f');
        
        $update = [
            'device'=>$device_uuid
        ];
        
        $ret = Db::name('fingerprint')->where(['fingerprint'=>$fingerprint])->update($update);
        //die(json_encode(['resultCode' => Db::name('fingerprint')->getLastSql()]));
        if($ret){
            die(json_encode(['resultCode' => 0, 'message' => "设备关联成功"]));
        }else{
            die(json_encode(['resultCode' => -1, 'error' => "设备关联失败"]));
        }
        
    }
    
    public function register(Request $request){
        $screen_width = $request->post('sw');
        $screen_heigth = $request->post('sh');
        $screen_pixelratio = $request->post('sp');
        $gpu_version = $request->post('gv');
        $gpu_renderer = $request->post('gr');        
        $device_uuid = $request->post('du');
        
        
        die(json_encode(['resultCode' => 0, 'message' => "设备关联成功"]));
    }
}


