<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:14
 */

namespace app\admin\validate;


use think\Validate;

class KcDiaobo extends Validate
{
    protected $rule = [
//        'customer_id' => 'require',
//        'ruku_fangshi' => 'require',
//        'piaoju_id' => 'require',
        'details' => 'require|min:1',
        'yw_time' => 'require'
    ];

    protected $message = [

        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'yw_time.require' => '业务时间必须输入'
    ];
}