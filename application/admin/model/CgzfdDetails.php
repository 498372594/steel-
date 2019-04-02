<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/26
 * Time: 16:10
 */

namespace app\admin\model;


use traits\model\SoftDelete;

class CgzfdDetails extends Base
{
    use SoftDelete;

    public function specification()
    {
        return $this->belongsTo('ViewSpecification', 'wuzi_id', 'id')->cache(true, 60)
            ->field('id,specification,mizhong_name,productname');
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
}