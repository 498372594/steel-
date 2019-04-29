<?php


namespace app\admin\validate;


use think\Validate;

class CapitalOtherYs extends Validate
{
    protected $rule = [
        'customer_id|客户' => 'require',
        'yw_time|业务时间' => 'require',
        'details' => 'require|min:1'
    ];

    protected $message = [
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行'
    ];
}