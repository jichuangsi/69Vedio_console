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
        Db::name("member")->where(['gid'=>1])->update(['try_and_see'=>get_config('look_at_num_mobile')]);
    }
    
    private function resetVipMember(){
        Db::name('member')->where(['gid'=>2,'out_time'=>['<=', time()]])->update(['gid'=>1,'out_time'=>NULL]);
    }
}