<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalOtherDetails extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function szmcData()
    {
        return $this->belongsTo('Paymenttype', 'shouzhimingcheng_id', 'id')->cache(true, 60)
            ->field('id,class,name')->bind(['shouzhileibie' => 'class', 'shouzhimingcheng' => 'name']);
    }

    public function szflData()
    {
        return $this->belongsTo('Paymentclass', 'shouzhileibie_id', 'id')
            ->field('id,name')->bind(['shouzhileibie' => 'class']);
    }
}