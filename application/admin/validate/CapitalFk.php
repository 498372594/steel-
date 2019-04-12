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
        'mingxi' => 'require|min:1',
    ];

    protected $message = [
        'mingxi.require' => '付款明细至少出现一行',
        'mingxi.min' => '付款明细至少出现一行'
    ];
}