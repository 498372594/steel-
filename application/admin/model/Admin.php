<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-03-30
 * Time: 14:45
 */

namespace app\admin\model;


class Admin extends Base
{
    protected $table = 'admin';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'create_time';

    protected $updateTime = 'update_time';

    protected $hidden = ['password'];

    public function setPasswordAttr($value)
    {
        return md5($value);
    }

    public function getDepartmentIdAttr($value)
    {
        return (string) $value;
    }

    /**
     * @return \think\model\relation\HasOne
     */
    public function company()
    {
        return $this->hasOne('company','id','companyid');
    }

    /**
     * @return \think\model\relation\HasOne
     */
    public function role()
    {
        return $this->hasOne(Dropdown::class,'code','department_id')->where(['module' => 'role'])->field('val,code');
    }
}