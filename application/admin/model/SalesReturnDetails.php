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

    public static function findCountsByXsSaleMxId($xsSaleMxId)
    {
        return self::alias('mx')
            ->join('__SALES_RETURN__ th', 'th.id=mx.xs_th_id')
            ->where('mx.xs_sale_mx_id', $xsSaleMxId)
            ->where('th.status', '<>', 2)
            ->max('counts');
    }

    public static function findZhongliangByXsSaleMxId($xsSaleMxId)
    {
        self::alias('mx')
            ->join('__SALES_RETURN__ th', 'th.id=mx.xs_th_id')
            ->where('mx.xs_sale_mx_id', $xsSaleMxId)
            ->where('th.status', '<>', 2)
            ->max('zhongliang');
    }

    public static function getSumJiashuiHejiByPid($pid)
    {
        return self::where('xs_th_id', $pid)->sum('sum_shui_price');
    }

    public static function getSumZhongliangByPid($pid)
    {
        return self::where('xs_th_id', $pid)->sum('zhongliang');
    }
}