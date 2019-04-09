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
        $data = json_decode($data,true);
        $res = Db::table('tp5_test')->where(['order_no' => $data['order_no']])->update(['status'=>1]);
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