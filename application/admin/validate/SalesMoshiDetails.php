<?php

namespace app\admin\validate;

use think\Validate;

class SalesMoshiDetails extends Validate
{
    protected $rule = [
        'cg_customer_id' => 'require',
        'store_id' => 'require',
        'pinming_id' => 'require',
        'guige_id' => 'require',
        'jijiafangshi_id' => 'require',
        'cg_jijiafangshi_id' => 'require',
        'cg_piaoju_id' => 'require',
        'cg_zhongliang' => 'require',
        'cg_price' => 'require',
        'zhongliang' => 'require',
        'price' => 'require',
    ];

    protected $message = [
        'cg_customer_id.require' => '往来单位不能为空',
        'cg_jijiafangshi_id.require' => '计算方式不能为空',
        'cg_piaoju_id' => '票据类型不能为空',
        'store_id.require' => '仓库名不能为空',
        'pinming_id' => '品名不能为空',
        'guige_id.require' => '物资不能为空',
        'jijiafangshi_id.require' => '计算方式不能为空',
        'cg_zhongliang.require' => '采购重量不能为空',
        'cg_price.require' => '采购单价不能为空',
        'zhongliang.require' => '销售重量不能为空',
        'price.require' => '销售单价不能为空',
    ];

    protected $scene = [
        'zhifa' => [
            'store_id',
            'pinming_id',
            'guige_id',
            'jijiafangshi_id',
            'cg_zhongliang',
            'cg_price',
            'zhongliang',
            'price'
        ],
        'tiaohuo' => [
            'cg_customer_id',
            'store_id',
            'pinming_id',
            'guige_id',
            'cg_jijiafangshi_id',
            'cg_piaoju_id',
            'cg_zhongliang',
            'cg_price',
            'jijiafangshi_id',
            'zhongliang',
            'price'
        ]
    ];
}