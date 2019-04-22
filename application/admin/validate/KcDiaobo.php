<?php


namespace app\admin\validate;


use think\Validate;

class Kcdiaobo extends Validate
{
    protected $rule = [
        'zhongliang' => 'require',
        'details' => 'require|min:1',
        'yw_time' => 'require'
    ];

    protected $message = [
        'zhongliang.require' => '调拨重量不能为空',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'yw_time.require' => '业务时间必须输入'
    ];

}