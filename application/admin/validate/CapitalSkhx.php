<?php


namespace app\admin\validate;


use think\Validate;

class CapitalSkhx extends Validate
{
    protected $rule = [
        'skhx_type|单据类型' => 'require',
        'hx_money|核销金额' => 'require',
        'hx_zhongliang|核销重量' => 'require',
        'data_id|单据id' => 'require'
    ];
}