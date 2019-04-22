<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 8:52
 */

namespace app\admin\model;

use traits\model\SoftDelete;

class KcDiaoboMx extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';
    public function saleoperatordata()
    {
        return $this->belongsTo('admin', 'sale_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['sale_operator' => 'name']);
    }
    public function udpateoperatordata()
    {
        return $this->belongsTo('admin', 'update_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['update_operator' => 'name']);
    }
    public function checkoperatordata()
    {
        return $this->belongsTo('admin', 'check_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['check_operator' => 'name']);
    }
    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification')->bind(['guige' => 'specification']);;
    }

    public function storageData()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage' => 'storage']);
    }
    public function newstorageData()
    {
        return $this->belongsTo('Storage', 'new_store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['new_storage' => 'storage']);
    }
    public function pinmingData()
    {
        return $this->belongsTo('Productname', 'pinming_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['pinming' => 'name']);
    }
    public function caizhiData()
    {
        return $this->belongsTo('texture', 'caizhi_id', 'id')->cache(true, 60)
            ->field('id,texturename')->bind(['caizhi' => 'texturename']);
    }
    public function chandiData()
    {
        return $this->belongsTo('Originarea', 'chandi_id', 'id')->cache(true, 60)
            ->field('id,originarea')->bind(['chandi' => 'originarea']);
    }
    public function customData()
    {
        return $this->belongsTo('custom', 'gf_customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['gf_customer' => 'custom']);
    }
    public function jsfsData()
    {
        return $this->belongsTo('jsfs', 'jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jsfs' => 'jsfs']);
    }

}