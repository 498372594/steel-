<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class SalesTiaohuoDetails extends Base
{
    use SoftDelete;

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'wuzi_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
    }

    public function thPjlxData()
    {
        return $this->belongsTo('Pjlx', 'th_pjlx', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['th_pjlx_name' => 'pjlx']);
    }

    public function thJsfsData()
    {
        return $this->belongsTo('Jsfs', 'th_jsfs', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['th_jsfs_name' => 'jsfs']);
    }

    public function xsJsfsData()
    {
        return $this->belongsTo('Jsfs', 'xs_jsfs', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['xs_jsfs_name' => 'jsfs']);
    }

    public function wldwData()
    {
        return $this->belongsTo('Custom', 'wldw', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['wldw_name' => 'custom']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'storage_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }
}