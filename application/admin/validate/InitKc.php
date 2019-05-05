<?php


namespace app\admin\validate;


use think\Validate;

class InitKc extends Validate
{
    protected $rule = [
        'store_id|仓库' => 'require',
        'piaoju_id|票据类型' => 'require',
        'yw_time|业务时间' => 'require',
        'details' => 'require|min:1',
    ];

    protected $message = [
        'details.require' => '付款明细至少出现一行',
        'details.min' => '付款明细至少出现一行'
    ];
}