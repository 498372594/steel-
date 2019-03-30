<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:14
 */

namespace app\admin\validate;


use think\Validate;

class Cgzfd extends Validate
{
    protected $rule = [
        'gys_id' => 'require',
        'gfpj' => 'require',
        'kh_id' => 'require',
        'khpj' => 'require',
        'details' => 'require|min:1',
        'ywsj' => 'require'
    ];

    protected $message = [
        'gys_id.require' => '供应商必须输入',
        'gfpj.require' => '供应商票据类型必须输入',
        'kh_id.require' => '客户必须输入',
        'khpj.require' => '客户票据类型必须输入',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'ywsj.require' => '业务时间必须输入'
    ];
}