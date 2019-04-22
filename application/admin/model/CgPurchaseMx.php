<?php

namespace app\admin\model;

use traits\model\SoftDelete;

class CgPurchaseMx extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification')->bind(['guige' => 'specification']);
    }

    public function jsfs()
    {
        return $this->belongsTo('jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jiesuan_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }

    public function pinmingData()
    {
        return $this->belongsTo('Productname', 'pinming_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['pinming' => 'name']);
    }

    public function wuziData()
    {
        return $this->belongsTo('Productname', 'pinming_id', 'id')->cache(true, 60)
            ->field('id,zjm')->bind(['wuzi' => 'zjm']);
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

    public function jijiafangshiData()
    {
        return $this->belongsTo('jsfs', 'jijiafangshi_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jijiafangshi' => 'jsfs']);
    }

    public static function getSumZhongliangByPid($pid)
    {
        return self::where('purchase_id', $pid)->sum('zhongliang');
    }

}
