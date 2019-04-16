<?php

namespace app\admin\model;

use app\admin\library\traits\DeletePlugin;

class CapitalFy extends Base
{
    use DeletePlugin;
    protected $autoWriteTimestamp = true;

    public function szmcData()
    {
        return $this->belongsTo('Paymenttype', 'shouzhimingcheng_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['szmc_name' => 'name']);
    }

    public function szflData()
    {
        return $this->belongsTo('Paymentclass', 'shouzhifenlei_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['szfl_name' => 'name']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['dfdw_name' => 'custom']);
    }

    public function details()
    {
        return $this->hasMany('CapitalFyhx', 'cap_fy_id', 'id');
    }
}
