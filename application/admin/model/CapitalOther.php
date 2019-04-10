<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalOther extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['customer_name' => 'custom']);
    }

    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jiesuan_name' => 'jiesuanfangshi']);
    }

    public function details()
    {
        return $this->hasMany('CapitalOtherDetails', 'cap_qt_id', 'id');
    }
}