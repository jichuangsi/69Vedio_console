<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Db\Query;
use think\Exception;

class Resetmemberinfo extends Command
{
    protected function configure()
    {
        $this->setName('reset_member_info')->setDescription('reset the basic info of member daily');
    }
    
    protected function execute(Input $input, Output $output)
    {
        try{            
            $this->resetVipMember();            
            $this->resetCommonMember();   
        }catch (Exception $ex){
            $output->writeln("Resetmemberinfo is fail to excute on " . time());
        }         
        $output->writeln("Resetmemberinfo is succeed to excute on " . time());
    }
    
    private function resetCommonMember(){
    	$userlist=Db::name('member')->field('id')->where(['gid'=>1])->select();
        foreach($userlist as $k=>$v){
        	$pcount=Db::name('member')->where(['pid'=>$v['id']])->count();
        	if($pcount<2){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile')]);
        	}else if($pcount<5){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile2')]);
        	}else if($pcount<10){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile3')]);
        	}else if($pcount<20){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile4')]);
        	}else if($pcount>=20){
        		Db::name("member")->where(['gid'=>1,'id'=>$v['id']])->update(['try_and_see'=>get_config('look_at_num_mobile5')]);
        	}
        }
//      Db::name("member")->where(['gid'=>1])->update(['try_and_see'=>get_config('look_at_num_mobile')]);
    }
    
    private function resetVipMember(){
        Db::name('member')->where(['gid'=>2,'out_time'=>['<=', time()],'is_permanent'=>0])->update(['gid'=>1,'out_time'=>NULL]);
    }
}