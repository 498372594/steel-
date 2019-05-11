<?php


namespace app\admin\validate;


use think\Validate;

class KcYlSh extends Validate
{
    protected $rule = [
        'zhongliang' => 'require',
        'kehu_name' => 'require',
        'baoliu_time' => 'require',
        'shuliang' => 'require',
    ];

    protected $message = [
        'zhongliang.require' => '预留重量不能为空',
        'kehu_name.require' => '客户名称不能为空',
        'baoliu_time.require' => '保留时间不能为空',
        'shuliang.require' => '调拨数量不能为空'
    ];

}