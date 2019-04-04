<?php

namespace app\admin\validate;

use think\Validate;

class FeiyongDetails extends Validate
{
    protected $rule = [
        'fang_xiang' => 'require',
        'shouzhileibie_id' => 'require',
        'shouzhimingcheng_id' => 'require',
        'piaoju_id' => 'require',
        'danjia' => 'require',
        'customer_id' => 'require',
    ];

    protected $message = [
        'fang_xiang' => '收付方向不能为空',
        'shouzhileibie_id' => '收支分类不能为空',
        'customer_id.require' => '对方单位不能为空',
        'shouzhimingcheng_id.require' => '收支名称不能为空',
        'piaoju_id.require' => '票据类型不能为空',
        'danjia.require' => '单价不能为空',
    ];
}