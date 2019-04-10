<?php


namespace app\admin\validate;


use think\Validate;

class CapitalFy extends Validate
{
    protected $rule = [
        'customer_id|客户' => 'require',
        'fang_xiang|方向' => 'require',
        'shouzhifenlei_id|收支类别' => 'require',
        'shouzhimingcheng_id|收支名称' => 'require',
        'piaoju_id|票据类型' => 'require',
        'yw_time|业务时间' => 'require',
        'details' => 'require|min:1'
    ];

    protected $message = [
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行'
    ];
}