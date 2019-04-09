<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-03-28
 * Time: 10:41
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Log;
use think\Queue;

class Test extends Controller
{
    public function index()
    {
        $data = [
            'order_no' =>rand(100000,999999),
        ];
        $this->add($data['order_no']);
        $data = json_encode($data);
        for ($i = 0;$i<10;$i++){
            Queue::later(10,'app\admin\job\ChangePrice',$data,$queue = null);
        }
    }

    public function add($orderNo){
        $data =[
            'order_no'=>$orderNo,
            'msg'=>$orderNo,
            'create_time'=>date('Y-m-d H:i:s'),
        ];
        Db::table('tp5_test')->insert($data);
    }
}