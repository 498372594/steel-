<?php

namespace app\admin\validate;


use think\Validate;

class SalesMoshi extends Validate
{
    protected $rule = [
        'cg_customer_id' => 'require',
        'cg_piaoju_id' => 'require',
        'customer_id' => 'require',
        'piaoju_id' => 'require',
        'details' => 'require|min:1',
        'yw_time' => 'require'
    ];

    protected $message = [
        'cg_customer_id.require' => '供应商必须输入',
        'cg_piaoju_id.require' => '供应商票据类型必须输入',
        'customer_id.require' => '客户必须输入',
        'piaoju_id.require' => '客户票据类型必须输入',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'yw_time.require' => '业务时间必须输入'
    ];

    protected $scene = [
        'zhifa' => ['cg_customer_id', 'cg_piaoju_id', 'customer_id', 'piaoju_id', 'details', 'yw_time'],
        'tiaohuo' => ['customer_id', 'piaoju_id', 'details', 'yw_time']
    ];
}