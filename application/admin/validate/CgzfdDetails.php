<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:42
 */

namespace app\admin\validate;


use think\Validate;

class CgzfdDetails extends Validate
{
    protected $rule = [
        'storage_id' => 'require',
        'wuzi_id' => 'require',
        'jsfs_id' => 'require',
        'in_weight' => 'require',
        'in_price' => 'require',
        'out_weight' => 'require',
        'out_price' => 'require'
//        'jzs' => 'checkJZS'
    ];

    protected $message = [
        'storage_id.require' => '仓库名不能为空',
        'wuzi_id.require' => '物资不能为空',
        'jsfs_id.require' => '计算方式不能为空',
        'in_weight.require' => '采购重量不能为空',
        'in_price.require' => '采购单价不能为空',
        'out_weight.require' => '销售重量不能为空',
        'out_price.require' => '销售单价不能为空',
    ];

//    protected function checkJZS($value, $rule, $data)
//    {
//        if (!empty($data['num'])) {
//            return empty($value) ? '当录入件数时，件支数为必填项且必须大于0' : true;
//        }
//        return true;
//    }
}