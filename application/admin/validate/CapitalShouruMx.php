<?php


namespace app\admin\validate;


use think\Validate;

class CapitalShouruMx extends Validate
{
    protected $rule = [
        'shouzhifenlei_id|收入分类' => 'require',
        'shouzhimingcheng_id|收入名称' => 'require',
        'bank_id|账户名称' => 'require',
        'money|金额' => 'require'
    ];
}