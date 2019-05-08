<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:14
 */

namespace app\admin\validate;


use think\Validate;

class KcPandian extends Validate
{
    protected $rule = [
        'customer_id' => 'require',
        'piaoju_id' => 'require',
        'details' => 'require|min:1',
        'yw_time' => 'require'
    ];

    protected $message = [

        'details.require' => '明细至少出现一行',
        'piaoju_id.require' => '请选择票据类型',
        'details.min' => '明细至少出现一行',
        'yw_time.require' => '业务时间必须输入'
    ];
}