<?php


namespace app\admin\validate;


use think\Validate;

class CapitalSk extends Validate
{
    protected $rule = [
        'customer_id|客户' => 'require',
        'sk_type|收款类型' => 'require',
        'money|本次收款' => 'require',
        'yw_time|业务时间' => 'require',
        'mingxi' => 'require|min:1',
    ];

    protected $message = [
        'mingxi.require' => '收款明细至少出现一行',
        'mingxi.min' => '收款明细至少出现一行'
    ];
}