<?php

namespace app\admin\controller;

use app\admin\library\traits\Backend;
use think\Db;
use think\Exception;
use think\Session;

class Price extends Right
{
    public function priceset(){
        if(request()->isPost()){
            $data=request()->post();
            $res =model("specification")->allowField(true)->saveAll($data);
            return returnRes($res,'价格编辑失败或数据没有修改');
        }
    }
}