<?php


namespace app\admin\model;


use traits\model\SoftDelete;

class SalesMoshiMx extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,productname')->bind(['guige' => 'specification', 'pinming' => 'productname']);
    }

    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs_name' => 'jsfs']);
    }

    public function cgJsfsData()
    {
        return $this->belongsTo('Jsfs', 'cg_jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['cg_jsfs_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function cgPjData()
    {
        return $this->belongsTo('Pjlx', 'cg_piaoju_id', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['cg_plx_name' => 'pjlx']);
    }

    public function wldwData()
    {
        return $this->belongsTo('Custom', 'cg_customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['gys_name' => 'custom']);
    }
}