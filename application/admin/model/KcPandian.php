<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 8:52
 */

namespace app\admin\model;

use traits\model\SoftDelete;

class KcPandian extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';

    public function details()
    {
        return $this->hasMany('KcPandianMx', 'pandian_id', 'id');
    }
    public function createoperatordata()
    {
        return $this->belongsTo('admin', 'create_operator_id', 'id')->cache(true, 60)
            ->field('id,name')->bind(['create_operator' => 'name']);
    }
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
    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jiesuan_name' => 'jiesuanfangshi']);
    }

    public function storageData()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }
}