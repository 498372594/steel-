<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class SalesTiaohuo extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('SalesTiaohuoDetails', 'order_id', 'id');
    }

    public function other()
    {
        return $this->hasMany('SalesTiaohuoOther', 'order_id', 'id');
    }
}