<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class SalesReturnDetails extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function pinmingData()
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

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function caizhi()
    {
        return $this->belongsTo('Texture', 'caizhi_id', 'id')->cache(true, 60)
            ->field('id,texturename')->bind(['caizhi_name' => 'texturename']);
    }

    public function chandi()
    {
        return $this->belongsTo('Originarea', 'chandi_id', 'id')->cache(true, 60)
            ->field('id,originarea')->bind(['chandi_name' => 'originarea']);
    }
}