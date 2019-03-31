<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:14
 */

namespace app\admin\validate;


use think\Validate;

class SalesTiaohuo extends Validate
{
    protected $rule = [
        'custom_id' => 'require',
        'pjlx' => 'require',
        'details' => 'require|min:1',
        'ywsj' => 'require'
    ];

    protected $message = [
        'custom_id.require' => '客户必须输入',
        'pjlx.require' => '请选择票据类型',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'ywsj.require' => '业务时间必须输入'
    ];
}