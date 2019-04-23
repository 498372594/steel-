<?php


namespace app\admin\validate;


use think\Validate;

class KcdiaoboMx extends Validate
{
    protected $rule = [
        'zhongliang' => 'require',
        'counts' => 'require',
    ];

    protected $message = [
        'zhongliang.require' => '调拨重量不能为空',
        'counts.require' => '调拨数量不能为空'
    ];

}