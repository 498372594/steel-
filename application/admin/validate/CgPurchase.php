<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:14
 */

namespace app\admin\validate;


use think\Validate;

class CgPurchase extends Validate
{
    protected $rule = [
        'customer_id' => 'require',
        'ruku_fangshi' => 'require',
        'piaoju_id' => 'require',
        'details' => 'require|min:1',
        'yw_time' => 'require'
    ];

    protected $message = [
        'customer_id.require' => '运营商必须输入',
        'ruku_fangshi.require' => '请选择出库方式',
        'piaoju_id.require' => '票据类型必须输入',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
        'yw_time.require' => '业务时间必须输入'
    ];
}