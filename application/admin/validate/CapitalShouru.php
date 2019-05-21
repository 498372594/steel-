<?php


namespace app\admin\validate;


use think\Validate;

class CapitalShouru extends Validate
{
    protected $rule = [
        'customer_id|往来单位' => 'require',
        'yw_time|业务时间' => 'require',
        'details|明细' => 'require'
    ];
}