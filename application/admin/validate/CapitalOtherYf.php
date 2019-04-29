<?php


namespace app\admin\validate;


use think\Validate;

class CapitalOtherYf extends Validate
{
    protected $rule = [
        'customer_id|供应商' => 'require',
        'yw_time|业务时间' => 'require',
        'details' => 'require|min:1'
    ];

    protected $message = [
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行'
    ];
}