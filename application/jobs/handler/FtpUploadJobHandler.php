<?php
namespace app\jobs\handler;

use think\queue\Job;

class FtpUploadJobHandler {    
    
    public function fire(Job $job, $param){
        
        $data = $param['data'];        
        $isJobStillNeedToBeDone = $this->checkDatabaseToSeeIfJobNeedToBeDone($data);
        if(!$isJobStillNeedToBeDone){
            $job->delete();
            return;
        }
        
        $isJobDone = $this->doFtpUploadJob($data);
        
        if ($isJobDone) {            
            $job->delete();
            print("<info>Ftp upload Job(".$data['path'].") has been done and deleted at ".date('Y-m-d H:i:s')."</info>\n");
        }else{
            if ($job->attempts() > 3) {                
                print("<warn>Hello Job has been retried more than 3 times!"."</warn>\n");
                $job->delete();                
                //print("<info>Hello Job will be availabe again after 2s."."</info>\n");
                //$job->release(2); 
            }
        }
    }    
    
    private function checkDatabaseToSeeIfJobNeedToBeDone($data){
        return true;
    }    
    
    private function doFtpUploadJob($data) {        
        
        if(!$data['path']) return false;
        
        $ret = ftp_upload($data['path']);
        
        if($ret&&$ret['code']===0){
            return true;
        }
        
        return false;
    }
}