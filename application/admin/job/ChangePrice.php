<?php
/**
 * 延迟修改价格
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-03-28
 * Time: 10:27
 */

namespace app\admin\job;

use think\Log;
use think\queue\Job;
use think\Db;
use think\Controller;

class ChangePrice extends Controller
{
    public function fire(Job $job, $data)
    {
        Log::write('hxc'.$data);
        $res=Db::table("specification")->where("id",">","0")
            ->inc("hsgbj",$data)
            ->inc("hslsj",$data)
            ->inc("hsdzj",$data)
            ->inc("qsgbj",$data)
            ->inc("qslsj",$data)
            ->inc("qsdzj",$data)
            ->update();
        $data=Db::table("specification")->field("id as gg_id,hsgbj,hslsj,hsdzj,qsgbj,qslsj,qsdzj")->select();
        $res=Db::table("price_log")->insertAll($data);
//        $res = Db::table('tp5_test')->where(['order_no' => $data['order_no']])->update(['status'=>1]);
        if($res) {
            $job->delete();
        }
        if ($job->attempts() > 3) {
            $job->delete();
        }
    }

    public function jobDone($data)
    {
        print("<info>Job is Done status!"."</info> \n");

    }
}