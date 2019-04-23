<?php


namespace app\admin\validate;


use think\Validate;

class InvXskpHx extends Validate
{

    protected $rule = [
        'shui_price' => 'number',
        'zhongliang' => 'require|number',
        'price' => 'require|number',
        'shuie' => 'require|number',
        'sum_price' => 'require|number',
        'sum_shui_price' => 'require|number'
    ];

    protected $message = [
        'shui_price.number' => '税率必须为数字',
        'zhongliang.require' => '重量不能为空',
        'zhongliang.number' => '重量必须为数字',
        'price.number' => '单价必须为数字',
        'price.require' => '单价必须为数字',
        'sum_price.number' => '金额必须为数字',
        'sum_price.require' => '金额必须为数字',
        'sum_shui_price.number' => '价税合计必须为数字',
        'sum_shui_price.require' => '价税合计必须为数字',
        'shuie.require' => '税额必须为数字',
        'shuie.number' => '税额必须为数字',
    ];

}