<?php

namespace app\admin\validate;

use think\Validate;

class SalesMoshiDetails extends Validate
{
    protected $rule = [
        'store_id' => 'require',
        'pinming_id' => 'require',
        'guige_id' => 'require',
        'jijiafangshi_id' => 'require',
        'cg_zhongliang' => 'require',
        'cg_price' => 'require',
        'zhongliang' => 'require',
        'price' => 'require',
    ];

    protected $message = [
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
        'zhifa' => ['store_id', 'pinming_id', 'guige_id', 'jijiafangshi_id', 'cg_zhongliang', 'cg_price', 'zhongliang', 'price']
    ];
}