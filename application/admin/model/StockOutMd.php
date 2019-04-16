<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class StockOutMd extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname')
            ->bind(['guige' => 'specification', 'pinming' => 'productname']);
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs_name' => 'jsfs']);
    }

    public function spot()
    {
        return $this->belongsTo('KcSpot', 'kc_spot_id', 'id')->cache(true, 60)
            ->field('id,resource_number')->bind('resource_number');
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public  function mainData(){
        return $this->belongsTo('StockOut','stock_out_id','id');
    }
}