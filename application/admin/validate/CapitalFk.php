<?php


namespace app\admin\validate;


use think\Validate;

class CapitalFk extends Validate
{
    protected $rule = [
        'customer_id|客户' => 'require',
        'fk_type|付款类型' => 'require',
        'money|本次付款' => 'require',
        'yw_time|业务时间' => 'require',
        'details' => 'require|min:1',
    ];

    protected $message = [
        'details.require' => '付款明细至少出现一行',
        'details.min' => '付款明细至少出现一行'
    ];
}