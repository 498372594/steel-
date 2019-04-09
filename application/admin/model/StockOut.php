<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class StockOut extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function wait()
    {
        return $this->hasMany('StockOutDetail', 'stock_out_id', 'id');
    }

    public function already()
    {
        return $this->hasMany('StockOutMd', 'stock_out_id', 'id');
    }
}