<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:42
 */

namespace app\admin\validate;


use think\Validate;

class SalesorderDetails extends Validate
{
    protected $rule = [
        'storage_id' => 'require',
        'wuzi_id' => 'require',
        'jsfs_id' => 'require',
        'weight' => 'require',
        'price' => 'require',
        'jzs' => 'checkJZS'
    ];

    protected $message = [
        'storage_id.require' => '仓库名不能为空',
        'wuzi_id.require' => '物资不能为空',
        'jsfs_id.require' => '计算方式不能为空',
        'weight.require' => '重量不能为空',
        'price.require' => '单价不能为空',
    ];

    protected function checkJZS($value, $rule, $data)
    {
        if (!empty($data['num'])) {
            return empty($value) ? '当录入件数时，件支数为必填项且必须大于0' : true;
        }
        return true;
    }
}