<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-04-12
 * Time: 18:46
 */

namespace app\admin\controller;


class Common extends  Right
{
    /**
     * 获取角色名称
     * @return \think\response\Json
     */
    public function roleName()
    {
        $data = getDropdownList('department');
        return returnRes($data,'没有数据，请联系管理员',$data);
    }
}