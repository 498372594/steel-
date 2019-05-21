<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalShouru extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function createOperator()
    {
        return $this->belongsTo('Admin', 'create_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['create_operator_name' => 'name']);
    }

    public function updateOperator()
    {
        return $this->belongsTo('Admin', 'update_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['update_operator_name' => 'name']);
    }

    public function details()
    {
        return $this->hasMany(CapitalShouruMx::class, 'shouru_id', 'id');
    }

    public function departmentData()
    {
        return $this->hasOne(Dropdown::class, 'code', 'group_id')
            ->where('module', 'department')
            ->cache(true, 60)
            ->field('val,code')
            ->bind(['department_name' => 'val']);
    }
}