<?php
/**
 * 延迟修改价格
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-03-28
 * Time: 10:27
 */

namespace app\admin\job;

use app\admin\model\PriceLog;
use app\admin\model\Specification;
use think\Controller;
use think\Db;
use think\Log;
use think\queue\Job;

class ChangePrice extends Controller
{
    public function fire(Job $job, $data)
    {
        Db::startTrans();
        try {
            Specification::where("id", ">", "0")
                ->inc("hsgbj", $data)
                ->inc("hslsj", $data)
                ->inc("hsdzj", $data)
                ->inc("qsgbj", $data)
                ->inc("qslsj", $data)
                ->inc("qsdzj", $data)
                ->update();
            $data = Specification::field("id as gg_id,hsgbj,hslsj,hsdzj,qsgbj,qslsj,qsdzj")->select();
            $insert = [];
            $nowDateTime = date('Y-m-d H:i:s');
            foreach ($data as $index => $item) {
                $insert[$index] = $item->toArray();
                $insert[$index]['create_time'] = $nowDateTime;
                $insert[$index]['update_time'] = $nowDateTime;
            }
            (new PriceLog())->insertAll($insert);
            Db::commit();
            $job->delete();
        } catch (\Exception $e) {
            Db::rollback();
            Log::write('queue error:' . $e->getMessage() . ',file:' . $e->getFile() . ',line:' . $e->getLine());
        }
        if ($job->attempts() > 3) {
            $job->delete();
        }
    }

    public function jobDone($data)
    {
        print("<info>Job is Done status!" . "</info> \n");

    }
}