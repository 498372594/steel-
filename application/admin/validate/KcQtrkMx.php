<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27
 * Time: 15:42
 */

namespace app\admin\validate;


use think\Validate;

class KcQtrkMx extends Validate
{
    protected $rule = [
        'store_id' => 'require',
        'pinming_id' => 'require',
        'guige_id' => 'require',
        'jijiafangshi_id' => 'require',
        'zhongliang' => 'require',
        'price' => 'require',
        'jzs' => 'checkJZS'
    ];

    protected $message = [
        'store_id.require' => '仓库名不能为空',
        'pinming_id.require' => '品名不能为空',
        'guige_id.require' => '规格不能为空',
        'jijiafangshi_id.require' => '计算方式不能为空',
        'zhongliang.require' => '重量不能为空',
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