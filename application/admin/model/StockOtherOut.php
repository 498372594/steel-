<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class StockOtherOut extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function custom()
    {
        return $this->belongsTo('Custom', 'customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }

    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jiesuan_name' => 'jiesuanfangshi']);
    }

    public function details()
    {
        return $this->hasMany('StockOtherOutDetails', 'stock_other_out_id', 'id');
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

    public function saleOperator()
    {
        return $this->belongsTo('Admin', 'sale_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['sale_operator_name' => 'name']);
    }
}