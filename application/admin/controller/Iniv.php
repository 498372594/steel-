<?php


namespace app\admin\controller;



use think\{db\exception\DataNotFoundException,
    db\exception\ModelNotFoundException,
    exception\DbException,
    Request,
    response\Json};

class Iniv extends Right
{
    public function add($data)
    {
        /*$data = [
            'hk_type' => '',
            'data_id' => '',
            'fangxiang' => '',
            'customer_id' => '',
            'jiesuan_id' => '',
            'system_number' => '',
            'yw_time' => '',
            'beizhu' => '',
            'money' => '',
            'group_id' => '',
            'sale_operator_id' => '',
            'create_operator_id' => '',
            'zhongliang' => '',
            'cache_pjlx_id' => '',
        ];*/
        model("iniv")->allowField(true)->save($data);
    }
}