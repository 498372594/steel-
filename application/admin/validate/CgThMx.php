<?php


namespace app\admin\validate;


use think\Validate;

class CgThMx extends Validate
{
    protected $rule = [
        'zhongliang' => 'require',
        'counts' => 'require',
    ];

    protected $message = [
        'zhongliang.require' => '重量不能为空',
        'counts.require' => '数量不能为空'
    ];

}