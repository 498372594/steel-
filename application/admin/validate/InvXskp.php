<?php


namespace app\admin\validate;


use think\Validate;

class InvXskp extends Validate
{
    protected $rule = [
        'money|本次开票' => 'require',
        'customer_id|客户' => 'require',
        'piaoju_id|票据类型' => 'require',
        'details' => 'require|min:1',
    ];

    protected $message = [
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
    ];

}