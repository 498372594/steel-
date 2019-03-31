<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:42
 */

namespace app\admin\validate;


use think\Validate;

class SalesTiaohuoDetails extends Validate
{
    protected $rule = [
        'wldw' => 'require',
        'storage_id' => 'require',
        'wuzi_id' => 'require',
        'th_jsfs' => 'require',
        'th_pjlx' => 'require',
        'th_weight' => 'require',
        'th_price' => 'require',
        'xs_jsfs' => 'require',
        'xs_weight' => 'require',
        'xs_price' => 'require',
        'jzs' => 'checkJZS'
    ];

    protected $message = [
        'wldw.require' => '往来单位不能为空',
        'storage_id.require' => '仓库名不能为空',
        'wuzi_id.require' => '物资不能为空',
        'th_jsfs.require' => '调货计算方式不能为空',
        'th_pjlx.require' => '调货税别不能为空',
        'th_weight.require' => '调货重量不能为空',
        'th_price.require' => '调货单价不能为空',
        'xs_jsfs.require' => '销售计算方式不能为空',
        'xs_weight.require' => '销售重量不能为空',
        'xs_price.require' => '销售单价不能为空',
    ];

    protected function checkJZS($value, $rule, $data)
    {
        if (!empty($data['jianshu'])) {
            return empty($value) ? '当录入件数时，件支数为必填项且必须大于0' : true;
        }
        return true;
    }
}