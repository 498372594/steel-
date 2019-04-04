<?php


namespace app\admin\model;

use traits\model\SoftDelete;

class KucunCktz extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
    protected $type=[
        'houdu'=>'float',
        'kuandu'=>'float',
        'changdu'=>'float',
        'lingzhi'=>'float',
        'jianshu'=>'float',
        'zhijian'=>'float',
        'counts'=>'float',
        'zhongliang'=>'float',
        'price'=>'float',
        'sumprice'=>'float',
        'shuie'=>'float',
        'shui_price'=>'float',
        'sum_shui_price'=>'float',
    ];

    public function custom()
    {
        return $this->belongsTo('Custom', 'cache_customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'guige_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
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

    public function adder()
    {
        return $this->belongsTo('Admin', 'cache_create_operator', 'id')->cache(true, 60)
            ->field('id,name')->bind(['add_name' => 'name']);
    }
}