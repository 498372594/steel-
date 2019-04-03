<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 8:52
 */

namespace app\admin\model;

use traits\model\SoftDelete;

class Salesorder extends Base
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;

    public function details()
    {
        return $this->hasMany('SalesorderDetails', 'order_id', 'id');
    }

    public function other()
    {
        return $this->hasMany('SalesorderOther', 'order_id', 'id');
    }

    public function jsfsData()
    {
        return $this->belongsTo('Jiesuanfangshi', 'jsfs', 'id')->cache(true, 60)
            ->field('id,jiesuanfangshi')->bind(['jisuan_name' => 'jiesuanfangshi']);
    }

    public function custom()
    {
        return $this->belongsTo('Custom', 'custom_id', 'id')->cache(true, 60)
            ->field('id,custom')->bind(['custom_name' => 'custom']);
    }

    public function pjlxData()
    {
        return $this->belongsTo('Pjlx', 'pjlx', 'id')->cache(true, 60)
            ->field('id,pjlx')->bind(['pjlx_name' => 'pjlx']);
    }
}