<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalSk extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('CapitalSkhx', 'sk_id', 'id');
    }

    public function mingxi()
    {
        return $this->hasMany('CapitalSkjsfs', 'sk_id', 'id');
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }
}