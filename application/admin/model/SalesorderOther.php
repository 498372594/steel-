<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 11:21
 */

namespace app\admin\model;

use traits\model\SoftDelete;

class SalesorderOther extends Base
{
    use SoftDelete;

    public function szmcData()
    {
        return $this->belongsTo('Paymenttype', 'szmc', 'id')->cache(true, 60)
            ->field('id,type,class,name')->bind(['sffx' => 'type', 'szfl_name' => 'class', 'szmc_name' => 'name']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'pjlx', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'dfdw', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['dfdw_name' => 'custom']);
    }
}