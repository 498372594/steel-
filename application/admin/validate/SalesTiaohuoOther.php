<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/28
 * Time: 9:55
 */

namespace app\admin\validate;


use think\Validate;

class SalesTiaohuoOther extends Validate
{
    protected $rule = [
        'dfdw' => 'require',
        'sffx' => 'require',
        'szfl' => 'require',
        'szmc' => 'require',
        'pjlx' => 'require',
        'price' => 'require',

    ];

    protected $message = [
        'dfdw.require' => '对方单位不能为空',
        'sffx.require' => '收付方向不能为空',
        'szfl.require' => '收支分类不能为空',
        'szmc.require' => '收支名称不能为空',
        'pjlx.require' => '票据类型不能为空',
        'price.require' => '单价不能为空',

    ];

}