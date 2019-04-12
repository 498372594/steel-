<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalFk extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('CapitalFkhx', 'fk_id', 'id');
    }

    public function mingxi()
    {
        return $this->hasMany('CapitalFkjsfs', 'fk_id', 'id');
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }
}