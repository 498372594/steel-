<?php


namespace app\admin\validate;


use think\Validate;

class InvCgsp extends Validate
{
    protected $rule = [
        'shui_price' => 'number',
        'zhongliang' => 'require|number',
        'price' => 'require|number',
        'shuie' => 'number',
        'sum_shui_price' => 'number'
    ];

    protected $message = [
        'shui_price.number' => '税率必须为数字',
        'zhongliang.require' => '重量不能为空',
        'zhongliang.number' => '重量必须为数字',
        'price.number' => '单价必须为数字',
        'sum_shui_price.number' => '价税合计必须为数字',
        'shuie.number' => '税额必须为数字'
    ];

}