<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-04-11
 * Time: 11:15
 */

namespace app\admin\model;


use think\Model;

class Role extends Model
{
    // 定义时间戳字段名
    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $autoWriteTimestamp = 'datetime';

    public function getDepartmentIdAttr($value)
    {
         return getDropdownList('department',$value);
    }

    public function getAuthorityAttr($value)
    {
        return explode(',',$value);
    }

}