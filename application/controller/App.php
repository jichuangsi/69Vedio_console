<?php
/**
 * App相关控制器
 * LastDate:    2019/12/10
 */

namespace app\controller;
use think\Cookie;
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
    public function videoshare(Request $request){
    	$uid = $request->param('u');      
    	$vid = $request->param('v')?$request->param('v'):$request->param('amp;v');
    	$r	= $request->param('r')? $request->param('r'):$request->param('amp;r');
        $video=Db::name('video')->field('id,title,url,user_id,thumbnail,gold')->where(['recommend'=>1])->order('add_time desc')->limit(0,9)->select();
        $vinfo=Db::name('video')->field('id,title,url,user_id,thumbnail,gold')->where(['id'=>$vid])->find();
        foreach($video as $k=>$v){
        	$video[$k]['thumbnail']=$this->getFronturl($v['thumbnail'],$v['user_id']);
        }
        if($r==1){
        	$arr=Cookie::get('video');
        	$arr_str=unserialize($arr);
        	if($arr_str){
//      		dump($arr_str);
        		array_push($arr_str,createUidCode($vid));
        	}else{
        		$arr_str=array(createUidCode($vid));
        	}
        	$pid=deUidCode($uid);
        	$agentgold =0;//代理商收入
	        unset($rdata);  //代理商金币记录
	        if($pid>0){
	        	if(!empty(get_config('video_royalty_agent')) && get_config('video_royalty_agent')>0){
	        		$agentgold=$vinfo['gold']*get_config('video_royalty_agent')/100;
	        		$rdata['user_id'] = $pid;
			        $rdata['gold'] = $agentgold; 
			        $rdata['add_time'] = time(); 
			        $rdata['module'] = 'agent'; 
			        $rdata['rid'] = $vid; 
			        $rdata['agent_uid'] = 0;
			        $rdata['explain'] = '分享视频被消费收入';
	        	}
	        }
	        $ret = Db::transaction(function() use($rdata,$agentgold,$pid){            
	            if($agentgold>0){
	            	Db::name('gold_log')->insertGetId($rdata);
	            	Db::name('member')->where(['id'=>$pid])->setInc('money', $agentgold); //代理商收入金币
	            }
	        });
        	Cookie::set('video',serialize($arr_str),3600);
        }
        
        if($vinfo){
        	$vinfo['thumbnail']=$this->getFronturl($vinfo['thumbnail'],$vinfo['user_id']);
        	$vinfo['url']=$this->getFronturl($vinfo['url'],$vinfo['user_id']);
        	if(get_config('gold_exchange_rate')){
        		$vinfo['money']=$vinfo['gold']/get_config('gold_exchange_rate');
        	}else{
        		$vinfo['money']=$vinfo['gold']/10;
        	}
        	if($vinfo['user_id']==0){
        		$vinfo['nickname']='69官方';
        		$vinfo['headimgurl']='';
        	}else{
        		$m=Db::name('member')->field('nickname,headimgurl')->where(['id'=>$v['user_id']])->find();
        		$vinfo['nickname']=$m['nickname'];
        		$vinfo['headimgurl']=$this->getFronturl($m['headimgurl'],$vinfo['user_id']);
        	}
        }
        if($r){
        	$this->assign('r', $r);
        }else{
        	$this->assign('r', 0);
        }
        $this->assign('u', $uid);
        $this->assign('v',$vinfo);
        $this->assign('vlist', $video);
        return view();
    }
    public function zhifusuccess(Request $request){
    	$uid = $request->param('u');      
    	$vid = $request->param('v'); 
    	$this->assign('u', $uid);
        $this->assign('v',$vid);
        
    	return $this->success('成功',url('app/videoshare'));
    }
    public function zhifuerr(Request $request){
    	$uid = $request->param('u');      
    	$vid = $request->param('v'); 
    	$this->assign('u', $uid);
        $this->assign('v',$vid);
        return view('app/videoshare');
    	return $this->success($uid,url('app/videoshare'));
    }
    public function queryvideo(Request $request){
    	$uid = $request->param('u');      
    	$vid = $request->param('v'); 
    	$arr=Cookie::get('video');
    	$arr_str=unserialize($arr);
    	$ss="";
    	if($arr_str){
    		foreach($arr_str as $k=>$v){
    			if(deUidCode($v)==$vid){
    				return true;
    			}
    		}
    	}else{
    		return false;
    	}
    	return false;
    }
    public function setcookie(Request $request){
    	$arr = array(createUidCode(177));
    	$arr_str = serialize($arr); 
    	Cookie::set('video',$arr_str,3600);
    }
    public function getcookie(Request $request){
    	$arr=Cookie::get('video');
    	$arr_str=unserialize($arr);
    	return $arr_str;
    } 
    protected function getFronturl($path='', $uid=''){
		if(!$path) return null;
        
        if(stripos($path,'http') > -1) return $path;
		
		$web_server_url=Db::name('admin_config')->where("name='web_server_url'")->find();
        $web_server_url=$web_server_url?$web_server_url['value']:'';
        
        if($uid){
            $fullPath = $web_server_url."/uploads/$uid/".str_replace('\\','/',$path);
        }else{
                $fullPath = $web_server_url."/uploads/".str_replace('\\','/',$path);
        }  
        return $fullPath;
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
