<?php


namespace app\admin\validate;


use think\Validate;

class StockOtherOut extends Validate
{
    protected $rule = [
        'customer_id' => 'require',
        'piaoju_id' => 'require',
        'details' => 'require|min:1',
        'yw_time' => 'require'
    ];

    protected $message = [
        'customer_id.require' => '客户必须输入',
        'piaoju_id.require' => '票据类型必须输入',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'yw_time.require' => '业务时间必须输入'
    ];

}