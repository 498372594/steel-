<?php


namespace app\admin\validate;


use think\Validate;

class CapitalFkJsfs extends Validate
{
    protected $rule = [
        'jiesuan_id|结算方式' => 'require',
        'bank_id|账户名称' => 'require',
        'money|付款金额' => 'require'
    ];
}