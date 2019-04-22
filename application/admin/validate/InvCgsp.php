<?php


namespace app\admin\validate;


use think\Validate;

class InvCgsp extends Validate
{
    protected $rule = [
        'gys_id' => 'require',
        'piaoju_id' => 'require',
        'details' => 'require|min:1',
        'yw_time' => 'require',
        'money' => 'require'
    ];

    protected $message = [
        'gys_id.require' => '供应商不能为空',
        'piaoju_id.require' => '票据类型不能为空',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'yw_time.require' => '业务时间必须输入',
        'money.require' => '收票金额不能为空'
    ];

}