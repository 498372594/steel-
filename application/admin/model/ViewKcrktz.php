<?php

namespace app\admin\model;

use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use traits\model\SoftDelete;

class ViewKcrktz extends Base
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $autoWriteTimestamp = 'datetime';


    public function jsfs()
    {
        return $this->belongsTo('Jsfs', 'jiesuan_id', 'id')->cache(true, 60)
            ->field('id,jsfs')->bind(['jiesuan_name' => 'jsfs']);
    }

    public function storage()
    {
        return $this->belongsTo('Storage', 'store_id', 'id')->cache(true, 60)
            ->field('id,storage')->bind(['storage_name' => 'storage']);
    }


    public function customData()
    {
        return $this->belongsTo('custom', 'cache_customer_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['customer_name' => 'custom']);
    }
    public function rukufangshiData()
    {
        return $this->belongsTo('kc_rk_type', 'ruku_type', 'id')->cache(true, 60)
            ->field('id,name')->bind(['rukufangshi' => 'name']);
    }


}
