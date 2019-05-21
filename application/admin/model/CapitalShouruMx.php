<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class CapitalShouruMx extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function szmcData()
    {
        return $this->belongsTo('Paymenttype', 'shouzhimingcheng_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['shouzhimingcheng' => 'name']);
    }

    public function szflData()
    {
        return $this->belongsTo('Paymentclass', 'shouzhifenlei_id', 'id')
            ->field('id,name')->bind(['shouzhifenlei' => 'name']);
    }

    public function bankData()
    {
        return $this->belongsTo('Bank', 'bank_id', 'id')->cache(true, 60)
            ->field('id,bank')->bind(['bank_name' => 'bank']);
    }
}