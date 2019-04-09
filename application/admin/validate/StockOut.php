<?php


namespace app\admin\validate;


use think\Validate;

class StockOut extends Validate
{
    protected $rule = [
        'yw_time' => 'require',
        'details' => 'require|min:1'
    ];

    protected $message = [
        'yw_time.require' => '业务时间必须输入',
        'details.require' => '明细至少出现一行',
        'details.min' => '明细至少出现一行',
    ];

}