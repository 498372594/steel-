<?php


namespace app\admin\validate;


use think\Validate;

class CapitalOtherDetails extends Validate
{
    protected $rule = [
        'shouzhileibie_id|收支分类' => 'require',
        'shouzhimingcheng_id|收支名称' => 'require',
        'money|金额' => 'require'
    ];
}