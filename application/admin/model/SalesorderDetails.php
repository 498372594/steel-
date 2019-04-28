<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class SalesorderDetails extends Base
{
    use SoftDelete;

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'wuzi_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname')
            ->bind(['guige' => 'specification', 'mizhong' => 'mizhong_name', 'pinming' => 'productname']);
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jsfs_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'storage_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function salesorder()
    {
        return $this->belongsTo('Salesorder', 'order_id', 'id')
            ->field('id,ywsj,system_no,custom_id');//->bind(['xs_sale_id' => 'id', 'yw_time' => 'ywsj', 'system_number' => 'system_no','custom_id'=>'custom_id']);
    }

    public function caizhiData()
    {
        return $this->belongsTo('Texture', 'caizhi', 'id')->cache(true, 60)
            ->field('id,texturename')->bind('texturename');
    }

    public function chandiData()
    {
        return $this->belongsTo('Originarea', 'chandi', 'id')->cache(true, 60)
            ->field('id,originarea')->bind(['originarea_name'=>'originarea']);
    }
}