<?php
namespace app\common;

use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;
use FFMpeg\Format\Video\WMV;
use FFMpeg\Format\Video\WebM;
use FFMpeg\FFMpeg;

class FFMpegUtil
{
    //
    static public function gen_video_preview($s='')
    {        
        if(!$s) return null;
        
        $p =  self::gen_new_name($s);
        
        if(!$p) return null;
        
        //$ffmpeg = FFMpeg::create();
		$ffmpeg = FFMpeg::create(array(
		    'ffmpeg.binaries' => ROOT_PATH.'\extend\ffmpeg\bin\ffmpeg.exe',
		    'ffprobe.binaries' => ROOT_PATH.'\extend\ffmpeg\bin\ffprobe.exe',
            'timeout' => 0,
            'ffmpeg.threads' => 12
        ));
		
		$video = $ffmpeg->open($s);
		$video
			->filters()
			->resize(new Dimension(320, 240))
			->synchronize();
		//截图
		/* $video
			->frame(TimeCode::fromSeconds(10))
			->save('http://192.168.31.108:92/Uploads/orange.jpg'); */
		//截视频
		$clip = $video->clip(TimeCode::fromSeconds(0), TimeCode::fromSeconds(intval(get_config('look_at_num'))));
		$clip->save(new X264('aac'), $p);
		/*$video
			->save(new X264(), 'export-x264.mp4')
			->save(new WMV(), 'export-wmv.wmv')
			->save(new WebM(), 'export-webm.webm');*/
		
		if(file_exists($p)){
		    return $p;
		}else{
		    return null;
		}
    }
    
    static private function gen_new_name($s){
        if(!$s) return null;
        
        $info = pathinfo($s);        
        
        return $info["dirname"].DS.$info["filename"].'_preview.'.$info["extension"];
    }
}