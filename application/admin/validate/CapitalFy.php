<?php


namespace app\admin\validate;


use think\Validate;

class CapitalFy extends Validate
{
    protected $rule = [
        'customer_id|费用往来单位' => 'require',
        'fang_xiang|方向' => 'require',
        'shouzhifenlei_id|收支类别' => 'require',
        'shouzhimingcheng_id|收支名称' => 'require',
        'piaoju_id|票据类型' => 'require',

    ];

    protected $message = [

    ];
}